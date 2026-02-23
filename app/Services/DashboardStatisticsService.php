<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class DashboardStatisticsService
{
    private function getTenant(): ?string
    {
        $user = Auth::user();
        if (! $user || ! $user->company) {
            return null;
        }

        return $user->company->context;
    }

    public function getCallStatistics(): array
    {
        $tenant = $this->getTenant();

        $sql = "SELECT
            IFNULL(SUM(IF(a.direction = 'in' AND a.status=1, 1, 0)), 0) AS total_inbound_call_count,
            IFNULL(SUM(IF(a.direction = 'out' AND a.status=1, 1, 0)), 0) AS total_outbound_call_count,
            IFNULL(SUM(IF(a.direction = 'ext', 1, 0)), 0) AS total_internal_call_count
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
        $keys = Redis::connection()->client()->select(1);
        $keys = Redis::connection()->client()->keys('agent_on_call-*');

        return count($keys);
    }

    public function getQueueWiseStatistics(): array
    {
        $tenant = $this->getTenant();
        if (! $tenant) {
            return [];
        }

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
        if (! $tenant) {
            return [];
        }

        return DB::connection('mysql-voice')
            ->table('callcount')
            ->where('tenant', $tenant)
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
        $keys = Redis::connection()->client()->select(1);
        $keys = Redis::connection()->client()->keys("agent_on_call-*-{$queueName}-*");

        return count($keys);
    }
}
