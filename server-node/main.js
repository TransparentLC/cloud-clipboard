import Koa from 'koa';
import koaWebsocket from 'koa-websocket';

import config from './app/config.js';
import httpRouter from './app/http-router.js';
import wsRouter from './app/ws-router.js';

const app = koaWebsocket(new Koa);
app.use(httpRouter.routes());
app.use(httpRouter.allowedMethods());
app.ws.use(wsRouter.routes());
app.ws.use(wsRouter.allowedMethods());

app.listen(config.server.port);
console.log(`Server listening on port ${config.server.port} ...`);