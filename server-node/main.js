import fs from 'node:fs';
import http from 'node:http';
import http2 from 'node:http2';
import os from 'node:os';
import path from 'node:path';
import url from 'node:url';
import Koa from 'koa';
import koaCompress from 'koa-compress';
import koaMount from 'koa-mount';
import koaStatic from 'koa-static';
import koaWebsocket from 'koa-websocket';

import config from './app/config.js';
import httpRouter from './app/http-router.js';
import wsRouter from './app/ws-router.js';

process.env.VERSION = `node-${JSON.parse(fs.readFileSync(path.join(path.dirname(url.fileURLToPath(import.meta.url)), 'package.json'))).version}`;

const app = koaWebsocket(new Koa);
app.use(async (ctx, next) => {
    const startTime = performance.now();

    await next();

    const statusCode = ctx.status;
    const statusString = process.env.NO_COLOR
        ? statusCode.toString()
        : `\x1b[${[39, 94, 92, 96, 93, 91, 95][(statusCode / 100) | 0]}m${statusCode}\x1b[0m`;

    const remoteAddress = ctx.request.header['x-real-ip']
        ?? ctx.request.header['x-forwarded-for']?.split(',').pop()?.trim()
        ?? ctx.req.socket.remoteAddress;

    console.log(new Date().toISOString(), '-', remoteAddress, ctx.request.method, ctx.request.path, statusString, `${(performance.now() - startTime).toFixed(2)}ms`);
})
app.use(koaCompress());
app.use(koaMount(config.server.prefix + '/', koaStatic(path.join(path.dirname(url.fileURLToPath(import.meta.url)), 'static'), {
    maxage: 30 * 24 * 60 * 60 * 1000,
})));
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
if (config.server.uds) {
    const s = config.server.uds.split(':');
    const udsPath = s[0];
    const udsPerm = s[1] || '666';
    if (fs.existsSync(udsPath)) {
        fs.unlinkSync(udsPath);
    }
    const server = createServer();
    server.listen(udsPath, () => fs.chmodSync(udsPath, udsPerm));
    app.ws.listen({server});
}
if (Array.isArray(config.server.host) && config.server.host.length) {
    config.server.host.forEach(e => {
        const server = createServer();
        server.listen(config.server.port, e);
        app.ws.listen({server});
    });
} else if (config.server.port) {
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
    ...(config.server.uds ? [`Server listening on UNIX domain socket ${config.server.uds} ...`] : []),
    ...(config.server.port
        ? [
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
        ]
        : []
    ),
    '',
].join('\n'));
