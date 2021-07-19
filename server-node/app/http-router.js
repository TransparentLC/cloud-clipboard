import KoaRouter from '@koa/router';
import koaBody from 'koa-body';
import koaWebsocket from 'koa-websocket';

import config from './config.js';
import messageQueue from './message.js';
import {
    writeJSON,
    wsBoardcast,
} from './util.js';

const router = new KoaRouter;

router.get('/server', async ctx => {
    ctx.body = {
        'server': `ws${config.server.wss ? 's' : ''}://${config.server.host}:${config.server.port}/push`,
        'auth': !!config.server.auth,
    };
});

router.post(
    '/text',
    koaBody({
        enableTypes: ['text'],
    }),
    async ctx => {
        /** @type {String} */
        let body = ctx.request.body;
        if (body.length > config.text.limit) {
            writeJSON(ctx, 400, {}, `文本长度不能超过 ${config.text.limit} 字`);
            return;
        }
        body = body
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
        const message = {
            event: 'receive',
            data: {
                id: messageQueue.counter,
                type: 'text',
                content: body,
            },
        };
        messageQueue.enqueue(message);
        /** @type {koaWebsocket.App<Koa.DefaultState, Koa.DefaultContext>} */
        const app = ctx.app;
        wsBoardcast(app.ws, JSON.stringify(message));
        writeJSON(ctx);
    }
);

router.delete('/revoke/:id(\\d+)', async ctx => {
    const id = parseInt(ctx.params.id);
    if (!messageQueue.queue.some(e => e.data.id === id)) {
        return writeJSON(ctx, 400, {}, '不存在的消息 ID');
    }
    messageQueue.queue.splice(messageQueue.queue.find(e => e.data.id === id), 1);
    /** @type {koaWebsocket.App<Koa.DefaultState, Koa.DefaultContext>} */
    const app = ctx.app;
    wsBoardcast(app.ws, JSON.stringify({
        event: 'revoke',
        data: {
            id,
        },
    }));
    writeJSON(ctx);
});

export default router;