/**
 * Real-Time Agent Status Polling
 * Single batch request per poll cycle to avoid race conditions
 */

console.log('[Agent Polling] Module loaded');

const agentPoller = {
    interval: null,
    isActive: false,
    polling: false, // prevent overlapping requests

    /**
     * Update a single agent card's border + Alpine isOnCall state
     */
    updateElement(agentId, isOnCall, callType) {
        const elements = document.querySelectorAll(`[data-agent-id="${agentId}"]`);

        elements.forEach(element => {
            const extType = element.getAttribute('data-extension-type');
            const isThisExtOnCall = isOnCall && callType === extType;
            const newColor = isThisExtOnCall ? '#16a34a' : '#c1c1c1';

            if (element.style.borderColor !== newColor) {
                element.style.borderColor = newColor;
                element.style.transition = 'border-color 0.3s ease';
            }

            // Toggle Alpine isOnCall state for phone icon visibility
            if (window.Alpine) {
                const data = window.Alpine.$data(element);
                if (data && data.isOnCall !== isThisExtOnCall) {
                    data.isOnCall = isThisExtOnCall;
                }
            }
        });
    },

    /**
     * Batch-poll all agents in a single request
     */
    pollAllAgents() {
        if (this.polling) return; // skip if previous request still pending

        const agentElements = document.querySelectorAll('[data-agent-id]');
        if (agentElements.length === 0) return;

        const ids = new Set();
        agentElements.forEach(el => {
            const id = el.getAttribute('data-agent-id');
            if (id) ids.add(id);
        });

        if (ids.size === 0) return;

        this.polling = true;

        fetch('/api/agents/status-batch', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ids: [...ids] }),
            cache: 'no-cache'
        })
            .then(res => res.json())
            .then(data => {
                if (data && data.agents) {
                    // Apply all statuses in one synchronous pass — no race conditions
                    for (const [agentId, status] of Object.entries(data.agents)) {
                        this.updateElement(agentId, status.isOnCall, status.callType || 'primary');
                    }
                }
            })
            .catch(() => { /* silently fail */ })
            .finally(() => { this.polling = false; });
    },

    start() {
        if (this.isActive) return;
        this.isActive = true;
        console.log('[Agent Polling] Started');

        this.pollAllAgents();

        this.interval = setInterval(() => {
            this.pollAllAgents();
        }, 1000);
    },

    stop() {
        if (this.interval) {
            clearInterval(this.interval);
            this.interval = null;
            this.isActive = false;
            this.polling = false;
        }
    },

    restart() {
        this.stop();
        setTimeout(() => this.start(), 100);
    }
};

// Auto-start when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => agentPoller.start());
} else {
    agentPoller.start();
}

// Restart on Livewire page navigation
document.addEventListener('livewire:navigated', () => agentPoller.restart());

// Listen for WebSocket events for instant updates
window.addEventListener('agent-status-updated', (event) => {
    const data = event.detail;
    if (data && data.userId) {
        agentPoller.updateElement(data.userId, data.isOnCall, data.type || 'primary');
    }
});

window.agentPoller = agentPoller;
