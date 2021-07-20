import Koa from 'koa';
import fs from 'fs';
import koaCompress from 'koa-compress';
import koaStatic from 'koa-static';
import koaWebsocket from 'koa-websocket';
import os from 'os';
import path from 'path';

import config from './app/config.js';
import httpRouter from './app/http-router.js';
import wsRouter from './app/ws-router.js';

try {
    await fs.promises.rm(path.join(os.tmpdir(), '.cloud-clipboard-storage'), { recursive: true });
} catch {}
await fs.promises.mkdir(path.join(os.tmpdir(), '.cloud-clipboard-storage'));

process.env.VERSION = `node-${JSON.parse(await fs.promises.readFile('./package.json')).version}`;

const app = koaWebsocket(new Koa);
app.use(koaCompress());
app.use(koaStatic('static'));
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
    `Server listening on port ${config.server.port} ...`
].join('\n'));