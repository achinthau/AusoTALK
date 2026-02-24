function connectWs() {
    const protocol = window.location.protocol === 'https:' ? 'wss' : 'ws';
    const ws = new WebSocket(`${protocol}://${window.location.host}/ws`);

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
