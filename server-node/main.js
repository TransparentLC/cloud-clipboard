import fs from 'node:fs';
import http from 'node:http';
import http2 from 'node:http2';
import os from 'node:os';
import path from 'node:path';
import url from 'node:url';
import Koa from 'koa';
import koaCompress from 'koa-compress';
import koaStatic from 'koa-static';
import koaWebsocket from 'koa-websocket';

import config from './app/config.js';
import httpRouter from './app/http-router.js';
import wsRouter from './app/ws-router.js';

const storagePath = path.join(os.tmpdir(), '.cloud-clipboard-storage');
if (!fs.existsSync(storagePath)) {
    fs.mkdirSync(storagePath);
}

process.env.VERSION = `node-${JSON.parse(fs.readFileSync(path.join(path.dirname(url.fileURLToPath(import.meta.url)), 'package.json'))).version}`;

const app = koaWebsocket(new Koa);
app.use(koaCompress());
app.use(koaStatic(path.join(path.dirname(url.fileURLToPath(import.meta.url)), 'static')));
app.use(httpRouter.routes());
app.use(httpRouter.allowedMethods());
app.ws.use(wsRouter.routes());
app.ws.use(wsRouter.allowedMethods());

// WebSocket over HTTP2 · Issue #1458 · websockets/ws
// https://github.com/websockets/ws/issues/1458
const createServer = () => (config.server.cert && config.server.key)
    ? http2.createSecureServer(
        {
            cert: fs.readFileSync(config.server.cert),
            key: fs.readFileSync(config.server.key),
            allowHTTP1: true,
        },
        app.callback(),
    )
    : http.createServer(app.callback());

// How to create https server and support websocket or wss use koa2? · Issue #29 · kudos/koa-websocket
// https://github.com/kudos/koa-websocket/issues/29#issuecomment-341782858
if (Array.isArray(config.server.host) && config.server.host.length) {
    config.server.host.forEach(e => {
        const server = createServer();
        server.listen(config.server.port, e);
        app.ws.listen({server});
    });
} else {
    const server = createServer();
    server.listen(config.server.port);
    app.ws.listen({server});
}

console.log([
    '',
    `Cloud Clipboard ${process.env.VERSION}`,
    'https://github.com/TransparentLC/cloud-clipboard',
    '',
    'Authorization code' + (config.server.auth ? `: ${config.server.auth}` : ' is disabled.'),
    `Server listening on port ${config.server.port} ...`,
    'Available at:',
    ...(Array.isArray(config.server.host) && config.server.host.length
        ? (
            config.server.host.map(e => {
                // How to check if a string is a valid IPv6 address in JavaScript? | MELVIN GEORGE
                // https://melvingeorge.me/blog/check-if-string-is-valid-ipv6-address-javascript
                const isIPv6 = e.match(/(([\da-f]{1,4}:){7,7}[\da-f]{1,4}|([\da-f]{1,4}:){1,7}:|([\da-f]{1,4}:){1,6}:[\da-f]{1,4}|([\da-f]{1,4}:){1,5}(:[\da-f]{1,4}){1,2}|([\da-f]{1,4}:){1,4}(:[\da-f]{1,4}){1,3}|([\da-f]{1,4}:){1,3}(:[\da-f]{1,4}){1,4}|([\da-f]{1,4}:){1,2}(:[\da-f]{1,4}){1,5}|[\da-f]{1,4}:((:[\da-f]{1,4}){1,6})|:((:[\da-f]{1,4}){1,7}|:)|fe80:(:[\da-f]{0,4}){0,4}%[\da-zA-Z]{1,}|::(ffff(:0{1,4}){0,1}:){0,1}((25[0-5]|(2[0-4]|1{0,1}[\d]){0,1}[\d])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[\d]){0,1}[\d])|([\da-f]{1,4}:){1,4}:((25[0-5]|(2[0-4]|1{0,1}[\d]){0,1}[\d])\.){3,3}(25[0-5]|(2[0-4]|1{0,1}[\d]){0,1}[\d]))/gi);
                return `    http${config.server.key && config.server.cert ? 's' : ''}://${isIPv6 ? `[${e}]` : e}:${config.server.port}`;
            })
        )
        : (
            Object.entries(os.networkInterfaces()).reduce((acc, [k, v]) => {
                acc.push(`    ${k}:`);
                v.forEach(e => {
                    const a = e.family === 'IPv6' ? `[${e.address}]` : e.address;
                    acc.push(`        http${config.server.key && config.server.cert ? 's' : ''}://${a}:${config.server.port}`);
                });
                return acc;
            }, [])
        )
    ),
].join('\n'));