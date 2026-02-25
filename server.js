import WebSocket, { WebSocketServer } from 'ws';
import { createServer } from 'http';

const PORT = parseInt(process.env.WS_PORT ?? '6001');
const SECRET = process.env.WS_SECRET ?? 'pbx-dashboard-secret';

const server = createServer((req, res) => {
    if (req.method === 'POST' && req.url === '/broadcast') {
        let body = '';
        req.on('data', (chunk) => (body += chunk));
        req.on('end', () => {
            let payload;
            try {
                payload = JSON.parse(body);
            } catch {
                res.writeHead(400);
                res.end('Bad Request');
                return;
            }

            if (payload.secret !== SECRET) {
                res.writeHead(403);
                res.end('Forbidden');
                return;
            }

            const message = JSON.stringify({
                event: payload.event ?? 'update',
                data: payload.data ?? {},
            });

            let delivered = 0;
            wss.clients.forEach((client) => {
                if (client.readyState === WebSocket.OPEN) {
                    client.send(message);
                    delivered++;
                }
            });

            console.log(`[broadcast] event=${payload.event} delivered to ${delivered} client(s)`);
            res.writeHead(200, { 'Content-Type': 'application/json' });
            res.end(JSON.stringify({ ok: true, delivered }));
        });
        return;
    }

    res.writeHead(404);
    res.end();
});

const wss = new WebSocketServer({ server });

wss.on('connection', (ws, req) => {
    const ip = req.socket.remoteAddress;
    console.log(`[ws] client connected from ${ip} — total: ${wss.clients.size}`);
    ws.on('close', () => console.log(`[ws] client disconnected — total: ${wss.clients.size}`));
    ws.on('error', console.error);
});

server.listen(PORT, '0.0.0.0', () => {
    console.log(`[ws] server listening on port ${PORT}`);
});
