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
