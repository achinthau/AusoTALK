/**
 * Real-Time Agent Status & Call Statistics Polling
 * Automatically polls the API every 1 second and updates agent borders and call counts
 */

console.log('[Agent Polling] Module loaded');

const agentPoller = {
    interval: null,
    isActive: false,

    /**
     * Update agent element border color
     */
    updateElement(agentId, isOnCall) {
        const selector = `[data-agent-id="${agentId}"]`;
        const elements = document.querySelectorAll(selector);

        elements.forEach(element => {
            const newColor = isOnCall ? '#16a34a' : '#c1c1c1';
            const oldColor = element.style.borderColor;

            if (oldColor !== newColor) {
                element.style.borderColor = newColor;
                element.style.transition = 'border-color 0.3s ease';
                console.log(`[Agent Polling] Agent ${agentId}: ${isOnCall ? 'ON CALL ✓' : 'FREE'}`);
            }
        });
    },

    /**
     * Poll a single agent status
     */
    pollAgent(agentId) {
        fetch(`/api/agents/${agentId}/status`, {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            cache: 'no-cache'
        })
            .then(res => res.json())
            .then(data => {
                if (data && data.isOnCall !== undefined) {
                    this.updateElement(agentId, data.isOnCall);
                }
            })
            .catch(err => {
                // Silently fail for polling errors
            });
    },

    /**
     * Poll all agents on the page
     */
    pollAllAgents() {
        const agentElements = document.querySelectorAll('[data-agent-id]');

        if (agentElements.length === 0) return;

        agentElements.forEach(element => {
            const agentId = element.getAttribute('data-agent-id');
            if (agentId) {
                this.pollAgent(agentId);
            }
        });
    },

    /**
     * Poll call statistics for the dashboard
     */
    pollStatistics() {
        fetch('/api/call-statistics', {
            method: 'GET',
            headers: { 'Accept': 'application/json' },
            cache: 'no-cache'
        })
            .then(res => res.json())
            .then(data => {
                if (data) {
                    // Dispatch statistics-updated event for the widget to listen to
                    window.dispatchEvent(new CustomEvent('statistics-updated', {
                        detail: data
                    }));
                    console.log('[Agent Polling] Statistics updated:', data);
                }
            })
            .catch(err => {
                // Silently fail for polling errors
            });
    },

    /**
     * Start continuous polling
     */
    start() {
        if (this.isActive) return;

        this.isActive = true;
        console.log('[Agent Polling] Started - polling every 1 second');

        // Poll immediately
        this.pollAllAgents();
        this.pollStatistics();

        // Then poll every 1 second
        this.interval = setInterval(() => {
            this.pollAllAgents();
            this.pollStatistics();
        }, 1000);
    },

    /**
     * Stop polling
     */
    stop() {
        if (this.interval) {
            clearInterval(this.interval);
            this.isActive = false;
            console.log('[Agent Polling] Stopped');
        }
    },

    /**
     * Restart polling
     */
    restart() {
        this.stop();
        setTimeout(() => this.start(), 100);
    }
};

// Auto-start when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        agentPoller.start();
    });
} else {
    agentPoller.start();
}

// Restart polling when Livewire updates (for Filament navigation)
document.addEventListener('livewire:updated', () => {
    agentPoller.restart();
});

// Listen for WebSocket events too
window.addEventListener('agent-status-updated', (event) => {
    const data = event.detail;
    if (data && data.userId) {
        agentPoller.updateElement(data.userId, data.isOnCall);
    }
});

// Export for debugging
window.agentPoller = agentPoller;
