import './agent-polling';

const wsPort = import.meta.env.VITE_WS_PORT ?? 6001;
const wsHost = import.meta.env.VITE_WS_HOST ?? window.location.hostname;

// Get tenant context from meta tag or data attribute
function getTenantContext() {
    // Try to get from meta tag first
    const metaTag = document.querySelector('meta[name="tenant-context"]');
    if (metaTag) {
        return metaTag.getAttribute('content');
    }

    // Fallback: try to get from window object (if set by server)
    if (window.tenantContext) {
        return window.tenantContext;
    }

    return null;
}

let websocketConnected = false;

function connectWs() {
    const tenantContext = getTenantContext();
    
    if (!tenantContext) {
        console.warn('Tenant context not found, WebSocket will not connect');
        return;
    }

    const ws = new WebSocket(`ws://${window.location.hostname}:${wsPort}`);

    ws.addEventListener('open', () => {
        websocketConnected = true;
        // Subscribe to private channel with tenant context
        ws.send(JSON.stringify({
            event: 'pusher:subscribe',
            data: {
                channel: `private-dashboard-updates-${tenantContext}`
            }
        }));
    });

    ws.addEventListener('message', (event) => {
        try {
            const message = JSON.parse(event.data);
            
            // Handle Pusher protocol messages
            if (message.event === 'statistics-updated') {
                window.dispatchEvent(new CustomEvent('statistics-updated', { 
                    detail: message.data 
                }));
            }
            
            // Handle agent status updates
            if (message.event === 'agent-status-updated') {
                window.dispatchEvent(new CustomEvent('agent-status-updated', { 
                    detail: message.data 
                }));
            }
        } catch { /* ignore malformed */ }
    });

    // Reconnect automatically if the connection drops
    ws.addEventListener('close', () => {
        websocketConnected = false;
        setTimeout(connectWs, 3000);
    });

    ws.addEventListener('error', (error) => {
        console.error('WebSocket error:', error);
        websocketConnected = false;
    });
}

connectWs();

// Agent Status Real-Time Polling
window.agentStatusPoller = {
    isRunning: false,
    pollInterval: null,

    start() {
        if (this.isRunning) return;
        this.isRunning = true;
        console.log('[Agent Poller] Starting agent status polling...');

        this.pollInterval = setInterval(() => {
            document.querySelectorAll('[data-agent-id]').forEach((element) => {
                const agentId = element.getAttribute('data-agent-id');
                if (agentId) {
                    fetch(`/api/agents/${agentId}/status`, {
                        headers: { 'Accept': 'application/json' }
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.isOnCall !== undefined) {
                                const borderColor = data.isOnCall ? '#16a34a' : '#c1c1c1';
                                const currentColor = element.style.borderColor;
                                if (currentColor !== borderColor) {
                                    element.style.borderColor = borderColor;
                                    element.style.transition = 'border-color 0.3s ease';
                                    console.log(`[Agent Poller] Updated agent ${agentId}: ${data.isOnCall ? 'on call' : 'free'}`);
                                }
                            }
                        })
                        .catch(err => console.error(`[Agent Poller] Error for agent ${agentId}:`, err));
                }
            });
        }, 1000); // Poll every 1 second for responsive updates
    },

    stop() {
        if (this.pollInterval) {
            clearInterval(this.pollInterval);
            this.isRunning = false;
            console.log('[Agent Poller] Stopped');
        }
    }
};

// Start polling when page loads
document.addEventListener('DOMContentLoaded', () => {
    window.agentStatusPoller.start();
});

// Ensure polling on Livewire updates (for Filament)
document.addEventListener('livewire:navigated', () => {
    window.agentStatusPoller.start();
});

// Fallback polling function (if WebSocket is unavailable)
async function pollDashboardStatistics() {
    if (websocketConnected) {
        return; // Use WebSocket if available
    }

    try {
        const response = await fetch('/api/dashboard/statistics', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        if (response.ok) {
            const data = await response.json();
            window.dispatchEvent(new CustomEvent('statistics-updated', { 
                detail: data 
            }));
        }
    } catch (error) {
        console.error('Failed to fetch dashboard statistics:', error);
    }
}

// Poll every 2 seconds as fallback
// setInterval(pollDashboardStatistics, 2000);
