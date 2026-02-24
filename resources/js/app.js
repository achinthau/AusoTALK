const wsPort = import.meta.env.VITE_WS_PORT ?? 6001;
const wsHost = import.meta.env.VITE_WS_HOST ?? window.location.hostname;

function connectWs() {
    // const ws = new WebSocket(`ws://${wsHost}:${wsPort}`);
        const ws = new WebSocket(`ws://${window.location.hostname}:${wsPort}`);

    ws.addEventListener('message', (event) => {
        try {
            const { event: name } = JSON.parse(event.data);
            if (name === 'statistics-updated') {
                window.dispatchEvent(new CustomEvent('statistics-updated'));
            }
        } catch { /* ignore malformed */ }
    });

    // Reconnect automatically if the connection drops
    ws.addEventListener('close', () => setTimeout(connectWs, 3000));
    ws.addEventListener('error', console.error);
}

connectWs();
