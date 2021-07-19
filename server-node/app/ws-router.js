import KoaRouter from '@koa/router';
import koaWebsocket from 'koa-websocket';

import config from './config.js';
import messageQueue from './message.js';

const router = new KoaRouter;

router.get('/push', async (/** @type {koaWebsocket.MiddlewareContext<Koa.DefaultState>} */ ctx) => {
    if (config.server.auth && ctx.query.auth !== config.server.auth) {
        await new Promise(resolve => ctx.websocket.send(JSON.stringify({
            event: 'forbidden',
            data: {},
        }), resolve));
        ctx.websocket.close();
        return;
    }

    await new Promise(resolve => ctx.websocket.send(JSON.stringify({
        event: 'config',
        data: {
            version: 'node-1.0.0',
            text: config.text,
            file: config.file,
        },
    }), resolve));

    messageQueue.queue.reduce(
        (acc, cur) => acc.then(() => new Promise(resolve => ctx.websocket.send(JSON.stringify(cur), resolve))),
        Promise.resolve()
    );
});

router.get('(.*)', async (/** @type {koaWebsocket.MiddlewareContext<Koa.DefaultState>} */ ctx) => {
});

export default router;