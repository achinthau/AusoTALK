function connectWs() {
    const protocol = window.location.protocol === 'https:' ? 'wss' : 'ws';
    const port = window.location.port;
    // On standard ports (80/443) use Nginx /ws proxy; on dev (e.g. :8000) connect directly
    const url = (port === '' || port === '80' || port === '443')
        ? `${protocol}://${window.location.host}/ws`
        : `${protocol}://${window.location.hostname}:6001`;
    const ws = new WebSocket(url);

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
