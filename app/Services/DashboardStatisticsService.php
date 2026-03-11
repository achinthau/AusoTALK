<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class DashboardStatisticsService
{
    private const CACHE_TTL = 2; // 2 seconds

    private function getTenant(): ?string
    {
        $user = Auth::user();
        if (! $user || ! $user->company) {
            return null;
        }

        if ($user->hasRole('super_admin')) {
            return null;
        }

        return $user->company->context;
    }

    private function getCacheKey(string $metric, ?string $tenant = null): string
    {
        $tenant ??= $this->getTenant();

        return "statistics:{$metric}:{$tenant}";
    }

    public function getCallStatistics(): array
    {
        $tenant = $this->getTenant();

        $sql = "SELECT
            IFNULL(SUM(IF(a.direction = 'in' AND a.status=1, 1, 0)), 0) AS total_inbound_call_count,
            IFNULL(SUM(IF(a.direction = 'out' AND a.status=1, 1, 0)), 0) AS total_outbound_call_count,
            IFNULL(SUM(IF(a.direction = 'ext'AND a.status=1, 1, 0)), 0) AS total_internal_call_count
        FROM callcount a
        WHERE a.date > CURDATE()";

        $bindings = [];
        if ($tenant) {
            $sql .= ' AND a.tenant = ?';
            $bindings[] = $tenant;
        }

        $callData = DB::connection('mysql-voice')->select($sql, $bindings)[0];

        return [
            'inbound' => (int) $callData->total_inbound_call_count,
            'outbound' => (int) $callData->total_outbound_call_count,
            'total' => (int) $callData->total_inbound_call_count + $callData->total_outbound_call_count,
            'internal' => (int) $callData->total_internal_call_count,
        ];
    }

    public function getQueueStatistics(): array
    {
        $queueData = DB::connection('mysql-voice')
            ->select('SELECT 
            SUM(t1.connected) as total_queue_count,
            SUM(t1.answered) as total_answered_count,
            SUM(t1.disconnected) as total_disconnection_count,
            SUM(t1.abandoned) as abandoned_queue_count,
            SUM(t1.queue_wating_count) as queue_wating_count

            FROM (
            SELECT t.*,
            IF(t.answered=0 AND t.disconnected=1 AND t.connected=1,1,0) as abandoned,
            IFNULL(IF(t.uniqueid NOT IN (SELECT DISTINCT  uniqueid  FROM queuecount aa WHERE aa.date > CURDATE() and aa.status IN (2,0)),1,0),0) as queue_wating_count

            FROM 
            (
                    SELECT 
                    a.uniqueid ,
                        SUM(IF(a.status=1,1,0)) as connected,
                        SUM(IF(a.status=2,1,0)) as answered,
                        SUM(IF(a.status=0,1,0)) as disconnected
                    FROM queuecount a 
                    WHERE  a.date > CURDATE()
                    GROUP BY a.uniqueid 
                 ) t
            ) t1;')[0];

        $abandoned = (int) $queueData->abandoned_queue_count;

        return [
            'queued' => (int) $queueData->total_queue_count,
            'answered' => (int) $queueData->total_answered_count,
            'abandoned' => $abandoned < 0 ? 0 : $abandoned,
            'waiting' => (int) $queueData->queue_wating_count,
        ];
    }

    public function getOngoingCallCount(): int
    {
        $tenant = $this->getTenant();

        Redis::connection()->client()->select(1);
        $pattern = $tenant ? "agent_on_call-{$tenant}-*" : 'agent_on_call-*';
        $keys = Redis::connection()->client()->keys($pattern);

        return count($keys);
    }

    public function getQueueWiseStatistics(): array
    {
        return DB::connection('mysql-voice')
            ->select('SELECT 
                t1.queuename,
                SUM(t1.connected) as total_queue_count,
                SUM(t1.answered) as total_answered_count,
                SUM(t1.disconnected) as total_disconnection_count,
                SUM(t1.abandoned) as abandoned_queue_count,
                SUM(t1.queue_wating_count) as queue_wating_count

                FROM (
                SELECT t.*,
                IF(t.answered=0 AND t.disconnected=1 AND t.connected=1,1,0) as abandoned,
                IFNULL(IF(t.uniqueid NOT IN (SELECT DISTINCT  uniqueid  FROM queuecount aa WHERE aa.date > CURDATE() and aa.status IN (2,0)),1,0),0) as queue_wating_count

                FROM 
                (
                        SELECT 
                        a.uniqueid ,a.queuename,
                            SUM(IF(a.status=1,1,0)) as connected,
                            SUM(IF(a.status=2,1,0)) as answered,
                            SUM(IF(a.status=0,1,0)) as disconnected
                        FROM queuecount a 
                        WHERE  a.date > CURDATE()
                        GROUP BY a.uniqueid ,a.queuename
                    ) t
                ) t1 GROUP BY t1.queuename;');
    }

    public function getDialerQueueWiseStatistics(): array
    {
        $tenant = $this->getTenant();

        $query = DB::connection('mysql-voice')
            ->table('callcount');

        if ($tenant) {
            $query->where('tenant', $tenant);
        }

        return $query
            ->whereNotNull('app')
            ->whereDate('date', '>', DB::raw('CURDATE()'))
            ->select(
                'app as queuename',
                DB::raw('COUNT(*) as total_queue_count'),
                DB::raw('
            SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as answered_count,
            SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as busy_count,
            SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) as no_answer_count,
            SUM(CASE WHEN status = 5 THEN 1 ELSE 0 END) as unreachable_count,
            SUM(CASE WHEN status = 6 THEN 1 ELSE 0 END) as cancel_count
        ')
            )
            ->groupBy('app')
            ->get()
            ->toArray();
    }

    public function getQueueOngoingCallCount(string $queueName): int
    {
        $tenant = $this->getTenant();

        Redis::connection()->client()->select(1);
        $pattern = $tenant
            ? "agent_on_call-{$tenant}-{$queueName}-*"
            : "agent_on_call-*-{$queueName}-*";
        $keys = Redis::connection()->client()->keys($pattern);

        return count($keys);
    }

    // Cached methods for background polling

    public function getCachedCallStatistics(?string $tenant = null): array
    {
        $tenant ??= $this->getTenant();
        $cacheKey = $this->getCacheKey('calls', $tenant);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenant) {
            $sql = "SELECT
                IFNULL(SUM(IF(a.direction = 'in' AND a.status=1, 1, 0)), 0) AS total_inbound_call_count,
                IFNULL(SUM(IF(a.direction = 'out' AND a.status=1, 1, 0)), 0) AS total_outbound_call_count,
                IFNULL(SUM(IF(a.direction = 'ext'AND a.status=1, 1, 0)), 0) AS total_internal_call_count
            FROM callcount a
            WHERE a.date > CURDATE()";

            $bindings = [];
            if ($tenant) {
                $sql .= ' AND a.tenant = ?';
                $bindings[] = $tenant;
            }

            $callData = DB::connection('mysql-voice')->select($sql, $bindings)[0];

            return [
                'inbound' => (int) $callData->total_inbound_call_count,
                'outbound' => (int) $callData->total_outbound_call_count,
                'total' => (int) $callData->total_inbound_call_count + $callData->total_outbound_call_count,
                'internal' => (int) $callData->total_internal_call_count,
            ];
        });
    }

    public function getCachedQueueStatistics(?string $tenant = null): array
    {
        $tenant ??= $this->getTenant();
        $cacheKey = $this->getCacheKey('queue', $tenant);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            $queueData = DB::connection('mysql-voice')
                ->select('SELECT 
                SUM(t1.connected) as total_queue_count,
                SUM(t1.answered) as total_answered_count,
                SUM(t1.disconnected) as total_disconnection_count,
                SUM(t1.abandoned) as abandoned_queue_count,
                SUM(t1.queue_wating_count) as queue_wating_count

                FROM (
                SELECT t.*,
                IF(t.answered=0 AND t.disconnected=1 AND t.connected=1,1,0) as abandoned,
                IFNULL(IF(t.uniqueid NOT IN (SELECT DISTINCT  uniqueid  FROM queuecount aa WHERE aa.date > CURDATE() and aa.status IN (2,0)),1,0),0) as queue_wating_count

                FROM 
                (
                        SELECT 
                        a.uniqueid ,
                            SUM(IF(a.status=1,1,0)) as connected,
                            SUM(IF(a.status=2,1,0)) as answered,
                            SUM(IF(a.status=0,1,0)) as disconnected
                        FROM queuecount a 
                        WHERE  a.date > CURDATE()
                        GROUP BY a.uniqueid 
                     ) t
                ) t1;')[0];

            $abandoned = (int) $queueData->abandoned_queue_count;

            return [
                'queued' => (int) $queueData->total_queue_count,
                'answered' => (int) $queueData->total_answered_count,
                'abandoned' => $abandoned < 0 ? 0 : $abandoned,
                'waiting' => (int) $queueData->queue_wating_count,
            ];
        });
    }

    public function getCachedOngoingCallCount(?string $tenant = null): int
    {
        $tenant ??= $this->getTenant();
        $cacheKey = $this->getCacheKey('ongoing', $tenant);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenant) {
            Redis::connection()->client()->select(1);
            $pattern = $tenant ? "agent_on_call-{$tenant}-*" : 'agent_on_call-*';
            $keys = Redis::connection()->client()->keys($pattern);

            return count($keys);
        });
    }

    public function getCachedQueueWiseStatistics(?string $tenant = null): array
    {
        $tenant ??= $this->getTenant();
        $cacheKey = $this->getCacheKey('queue_wise', $tenant);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () {
            return DB::connection('mysql-voice')
                ->select('SELECT 
                    t1.queuename,
                    SUM(t1.connected) as total_queue_count,
                    SUM(t1.answered) as total_answered_count,
                    SUM(t1.disconnected) as total_disconnection_count,
                    SUM(t1.abandoned) as abandoned_queue_count,
                    SUM(t1.queue_wating_count) as queue_wating_count

                    FROM (
                    SELECT t.*,
                    IF(t.answered=0 AND t.disconnected=1 AND t.connected=1,1,0) as abandoned,
                    IFNULL(IF(t.uniqueid NOT IN (SELECT DISTINCT  uniqueid  FROM queuecount aa WHERE aa.date > CURDATE() and aa.status IN (2,0)),1,0),0) as queue_wating_count

                    FROM 
                    (
                            SELECT 
                            a.uniqueid ,a.queuename,
                                SUM(IF(a.status=1,1,0)) as connected,
                                SUM(IF(a.status=2,1,0)) as answered,
                                SUM(IF(a.status=0,1,0)) as disconnected
                            FROM queuecount a 
                            WHERE  a.date > CURDATE()
                            GROUP BY a.uniqueid ,a.queuename
                        ) t
                    ) t1 GROUP BY t1.queuename;');
        });
    }

    public function getCachedDialerQueueWiseStatistics(?string $tenant = null): array
    {
        $tenant ??= $this->getTenant();
        $cacheKey = $this->getCacheKey('dialer_queue_wise', $tenant);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tenant) {
            $query = DB::connection('mysql-voice')
                ->table('callcount');

            if ($tenant) {
                $query->where('tenant', $tenant);
            }

            return $query
                ->whereNotNull('app')
                ->whereDate('date', '>', DB::raw('CURDATE()'))
                ->select(
                    'app as queuename',
                    DB::raw('COUNT(*) as total_queue_count'),
                    DB::raw('
                SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as answered_count,
                SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as busy_count,
                SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) as no_answer_count,
                SUM(CASE WHEN status = 5 THEN 1 ELSE 0 END) as unreachable_count,
                SUM(CASE WHEN status = 6 THEN 1 ELSE 0 END) as cancel_count
            ')
                )
                ->groupBy('app')
                ->get()
                ->toArray();
        });
    }
}
