import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import Koa from 'koa';
import koaCompress from 'koa-compress';
import koaStatic from 'koa-static';
import koaWebsocket from 'koa-websocket';

import config from './app/config.js';
import httpRouter from './app/http-router.js';
import wsRouter from './app/ws-router.js';

try {
    await fs.promises.rm(path.join(os.tmpdir(), '.cloud-clipboard-storage'), { recursive: true });
} catch {}
await fs.promises.mkdir(path.join(os.tmpdir(), '.cloud-clipboard-storage'));

process.env.VERSION = `node-${JSON.parse(await fs.promises.readFile(path.join(path.resolve(), 'package.json'))).version}`;

const app = koaWebsocket(new Koa);
app.use(koaCompress());
app.use(koaStatic(path.join(path.resolve(), 'static')));
app.use(httpRouter.routes());
app.use(httpRouter.allowedMethods());
app.ws.use(wsRouter.routes());
app.ws.use(wsRouter.allowedMethods());

app.listen(config.server.port);
console.log([
    '',
    `Cloud Clipboard ${process.env.VERSION}`,
    'https://github.com/TransparentLC/cloud-clipboard',
    '',
    'Authorization code' + (config.server.auth ? `: ${config.server.auth}` : ' is disabled.'),
    `Server listening on port ${config.server.port} ...`,
    'Available at:',
    ...Object.entries(os.networkInterfaces()).reduce((acc, [k, v]) => {
        acc.push(`    ${k}:`);
        v.forEach(e => {
            const a = e.family === 'IPv6' ? `[${e.address}]` : e.address;
            acc.push(`        http://${a}:${config.server.port}`);
        });
        return acc;
    }, []),
].join('\n'));