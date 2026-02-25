/**
 * Dashboard WebSocket Real-Time Updates
 * Listens to WebSocket broadcasts and updates the dashboard in real-time
 */

class DashboardWebSocketListener {
    constructor() {
        this.listeners = new Map();
        this.isConnected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 10;
        this.reconnectDelay = 3000;
        this.init();
    }

    /**
     * Initialize the WebSocket connection
     */
    init() {
        // Use Laravel Echo if available, otherwise use native WebSocket
        if (typeof window.Echo !== 'undefined') {
            this.setupEcho();
        } else {
            this.setupNativeWebSocket();
        }
    }

    /**
     * Setup using Laravel Echo
     */
    setupEcho() {
        window.Echo.channel('dashboard-updates')
            .listen('.statistics-updated', (data) => {
                this.handleStatisticsUpdate(data);
            })
            .listen('.statistics-error', (error) => {
                console.error('Dashboard error:', error);
            });

        this.isConnected = true;
        this.reconnectAttempts = 0;
    }

    /**
     * Setup native WebSocket fallback
     */
    setupNativeWebSocket() {
        const protocol = window.location.protocol === 'https:' ? 'wss:' : 'ws:';
        const wsUrl = `${protocol}//${window.location.host}/ws`;

        try {
            const ws = new WebSocket(wsUrl);

            ws.onopen = () => {
                console.log('WebSocket connected');
                this.isConnected = true;
                this.reconnectAttempts = 0;
                ws.send(JSON.stringify({
                    type: 'subscribe',
                    channel: 'dashboard-updates'
                }));
            };

            ws.onmessage = (event) => {
                try {
                    const message = JSON.parse(event.data);
                    if (message.event === '.statistics-updated') {
                        this.handleStatisticsUpdate(message.data);
                    }
                } catch (e) {
                    console.error('Error parsing WebSocket message:', e);
                }
            };

            ws.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.handleConnectionError();
            };

            ws.onclose = () => {
                console.warn('WebSocket disconnected');
                this.isConnected = false;
                this.handleConnectionError();
            };

            window.dashboardWebSocket = ws;
        } catch (e) {
            console.error('Failed to establish WebSocket:', e);
            this.handleConnectionError();
        }
    }

    /**
     * Handle statistics update from WebSocket
     */
    handleStatisticsUpdate(data) {
        const statistics = data.statistics || data;

        // Update each widget with new data
        if (statistics.calls) {
            this.updateCallStatistics(statistics.calls);
        }
        if (statistics.queue) {
            this.updateQueueStatistics(statistics.queue);
        }
        if (statistics.ongoing !== undefined) {
            this.updateOngoingCount(statistics.ongoing);
        }
        if (statistics.queueWise) {
            this.updateQueueWiseData(statistics.queueWise);
        }
        if (statistics.dialerQueueWise) {
            this.updateDialerQueueWiseData(statistics.dialerQueueWise);
        }

        // Emit custom event for listeners
        window.dispatchEvent(new CustomEvent('dashboard-updated', { detail: statistics }));
    }

    /**
     * Update call statistics display
     */
    updateCallStatistics(callStats) {
        // Update DOM elements with call statistics
        const elements = document.querySelectorAll('[data-stat="calls"]');
        elements.forEach(el => {
            el.textContent = callStats.total || 0;
        });

        // Update subtext
        const subtextElements = document.querySelectorAll('[data-stat="calls-subtext"]');
        subtextElements.forEach(el => {
            el.textContent = `(In ${callStats.inbound || 0} | Out ${callStats.outbound || 0})`;
        });
    }

    /**
     * Update queue statistics display
     */
    updateQueueStatistics(queueStats) {
        const updates = {
            'queued': queueStats.queued,
            'answered': queueStats.answered,
            'abandoned': queueStats.abandoned,
            'waiting': queueStats.waiting
        };

        Object.entries(updates).forEach(([stat, value]) => {
            const elements = document.querySelectorAll(`[data-stat="${stat}"]`);
            elements.forEach(el => {
                el.textContent = value || 0;
            });
        });
    }

    /**
     * Update ongoing call count
     */
    updateOngoingCount(count) {
        const elements = document.querySelectorAll('[data-stat="ongoing"]');
        elements.forEach(el => {
            el.textContent = count || 0;
        });
    }

    /**
     * Update queue-wise data
     */
    updateQueueWiseData(queueWiseData) {
        // Dispatch event for components to update their data
        window.dispatchEvent(new CustomEvent('queue-data-updated', { detail: queueWiseData }));
    }

    /**
     * Update dialer queue-wise data
     */
    updateDialerQueueWiseData(dialerData) {
        // Dispatch event for components to update their data
        window.dispatchEvent(new CustomEvent('dialer-data-updated', { detail: dialerData }));
    }

    /**
     * Handle connection errors and attempt reconnection
     */
    handleConnectionError() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            const delay = this.reconnectDelay * this.reconnectAttempts;
            console.log(`Attempting to reconnect in ${delay}ms... (${this.reconnectAttempts}/${this.maxReconnectAttempts})`);

            setTimeout(() => {
                this.init();
            }, delay);
        } else {
            console.error('Failed to establish WebSocket connection after multiple attempts');
        }
    }

    /**
     * Subscribe to a specific listener
     */
    subscribe(event, callback) {
        if (!this.listeners.has(event)) {
            this.listeners.set(event, []);
        }
        this.listeners.get(event).push(callback);
    }

    /**
     * Get current connection status
     */
    getStatus() {
        return {
            connected: this.isConnected,
            reconnectAttempts: this.reconnectAttempts
        };
    }
}

// Initialize the dashboard listener when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    if (!window.dashboardListener) {
        window.dashboardListener = new DashboardWebSocketListener();
    }
});
