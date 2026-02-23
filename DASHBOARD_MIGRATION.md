# Dashboard Migration: Livewire to Filament with WebSocket Support

## Overview

The admin dashboard has been migrated from a Livewire component with `wire:poll` to a modern Filament dashboard with WebSocket real-time updates.

## Architecture

### Components

1. **Dashboard Page** (`app/Filament/Pages/Dashboard.php`)
   - Main dashboard page for Filament admin panel
   - Displays all dashboard widgets
   - Automatically discovered by Filament

2. **Widgets**
   - `CallStatisticsWidget` - Displays call statistics (inbound, outbound, total)
   - `QueueWiseStatisticsWidget` - Shows statistics by queue
   - `DialerQueueWiseStatisticsWidget` - Shows dialer campaign statistics

3. **Services**
   - `DashboardStatisticsService` - Fetches statistics from MySQL old database
   - `DashboardDataUpdateEvent` - Broadcasts statistics via WebSocket

4. **Console Command**
   - `BroadcastDashboardStatistics` - Periodic service that broadcasts updates

5. **Client-side Script**
   - `resources/js/dashboard-websocket.js` - Handles WebSocket connections and updates

## Setup & Configuration

### 1. Start the Broadcasting Service

Run the dashboard statistics broadcaster (replaces wire:poll):

```bash
php artisan dashboard:broadcast-statistics --interval=3
```

The `--interval` option controls update frequency in seconds (default: 3).

### 2. Configure Laravel Echo (Optional)

For better WebSocket integration, install Laravel Echo and configure it:

```bash
npm install laravel-echo pusher-js
```

Update your `resources/js/app.js`:

```javascript
import Echo from 'laravel-echo';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});
```

### 3. Include the WebSocket Script

Add to your main layout or Filament panel head:

```blade
<script src="{{ asset('js/dashboard-websocket.js') }}"></script>
```

Or in the Filament panel provider:

```php
->extraJsFiles([
    '/js/dashboard-websocket.js',
])
```

## Features

### Real-Time Updates
- Dashboard statistics update in real-time without page refresh
- WebSocket connection with automatic reconnection
- Graceful fallback to native WebSocket if Laravel Echo unavailable

### Statistics Monitored
- **Calls**: Total inbound and outbound calls
- **Queue**: Queued, answered, abandoned, and waiting calls
- **Ongoing**: Current active calls
- **Queue-wise**: Statistics broken down by queue name
- **Dialer**: Campaign statistics with detailed breakdown

### Connection Management
- Automatic reconnection with exponential backoff
- Maximum 10 reconnection attempts
- Manual status check via `window.dashboardListener.getStatus()`

## Migration from Livewire Dashboard

### What Changed
- `wire:poll.3000ms` → WebSocket real-time updates
- Livewire listeners → WebSocket event broadcasts
- Livewire views → Filament widgets & Blade templates
- Livewire component → Filament Dashboard Page

### Breaking Changes
- No longer uses Livewire for dashboard
- Requires WebSocket broadcaster (Pusher, Redis, etc.)
- Database connection name changes: `mysql-old` still used for legacy data

## Performance & Scalability

### Broadcasting Options

**For Development (Pusher - Free Tier)**:
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=your_app_id
PUSHER_APP_KEY=your_app_key
PUSHER_APP_SECRET=your_app_secret
PUSHER_APP_CLUSTER=mt1
```

**For Production (Redis)**:
```env
BROADCAST_DRIVER=redis
REDIS_BROADCAST_CONNECTION=default
```

**For Testing (Log)**:
```env
BROADCAST_DRIVER=log
```

## Troubleshooting

### Dashboard Not Updating

1. Check if broadcaster service is running:
   ```bash
   ps aux | grep 'dashboard:broadcast-statistics'
   ```

2. Check WebSocket connection in browser console:
   ```javascript
   window.dashboardListener.getStatus()
   ```

3. Ensure broadcast driver is configured in `.env`

### WebSocket Connection Failed

1. Verify your broadcaster is running (Pusher, Redis, etc.)
2. Check CORS configuration if using different domains
3. Review browser console for specific error messages
4. Check Laravel logs for broadcast errors: `tail -f storage/logs/laravel.log`

### High Memory Usage

The broadcaster runs continuously. To optimize:

1. Increase interval: `--interval=5` or `--interval=10`
2. Use a process manager: supervisor, systemd, or Docker
3. Monitor queue for any stuck jobs: `php artisan queue:failed`

## Docker Integration

Run broadcaster in Docker with supervisor:

```dockerfile
# Dockerfile
RUN apt-get install -y supervisor
COPY supervisor.conf /etc/supervisor/conf.d/
CMD supervisord
```

```bash
# supervisor.conf
[program:dashboard-broadcaster]
process_name=%(program_name)s_%(process_num)02d
command=php /app/artisan dashboard:broadcast-statistics --interval=3
numprocs=1
directory=/app
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/dashboard-broadcaster.log
```

## Testing

Visit the admin dashboard at `/admin` to see the real-time statistics. The widgets should update automatically based on the configured interval.

## API Reference

### DashboardStatisticsService

```php
$service = app(DashboardStatisticsService::class);

// Get call statistics
$calls = $service->getCallStatistics(); // ['inbound', 'outbound', 'total']

// Get queue statistics
$queue = $service->getQueueStatistics(); // ['queued', 'answered', 'abandoned', 'waiting']

// Get ongoing calls
$ongoing = $service->getOngoingCallCount(); // int

// Get queue-wise data
$queueData = $service->getQueueWiseStatistics(); // array

// Get dialer data
$dialerData = $service->getDialerQueueWiseStatistics(); // array
```

### WebSocket Events (Client-side)

```javascript
// Listen for all dashboard updates
window.addEventListener('dashboard-updated', (event) => {
    console.log('Dashboard updated:', event.detail);
});

// Listen for queue data updates
window.addEventListener('queue-data-updated', (event) => {
    console.log('Queue data:', event.detail);
});

// Listen for dialer data updates
window.addEventListener('dialer-data-updated', (event) => {
    console.log('Dialer data:', event.detail);
});

// Check connection status
const status = window.dashboardListener.getStatus();
console.log(status); // { connected: boolean, reconnectAttempts: number }
```

## Future Enhancements

- [ ] Add chart visualizations for historical data
- [ ] Implement filtering by date range
- [ ] Add export functionality
- [ ] Custom webhook notifications for anomalies
- [ ] Rate limiting on statistics queries
- [ ] Caching layer for expensive database queries
