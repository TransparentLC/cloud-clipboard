import crypto from 'node:crypto';
import KoaRouter from '@koa/router';
import koaWebsocket from 'koa-websocket';
import { UAParser } from 'ua-parser-js';

import config from './config.js';
import messageQueue from './message.js';
import {
    wsBoardcast,
    murmurHash,
} from './util.js';

/** @type {Map<String, {type: String, device: String, os: String, browser: String}>} */
const deviceConnected = new Map;
const deviceHashSeed = Math.random() * 0xFFFFFFFF >>> 0;

const router = new KoaRouter({
    prefix: config.server.prefix,
});

router.get('/push', async (/** @type {koaWebsocket.MiddlewareContext<Koa.DefaultState>} */ ctx) => {
    if (config.server.auth) {
        await new Promise(resolve => setTimeout(resolve, crypto.randomInt(50, 200)));
        if (ctx.query.auth !== config.server.auth) {
            await new Promise(resolve => ctx.websocket.send(JSON.stringify({
                event: 'forbidden',
                data: {},
            }), resolve));
            ctx.websocket.close();
            const remoteAddress = ctx.request.header['x-real-ip']
                ?? ctx.request.header['x-forwarded-for']?.split(',').pop()?.trim()
                ?? ctx.req.socket.remoteAddress;

            console.log(new Date().toISOString(), '-', remoteAddress, "auth failed: ", ctx.query.auth);
            return;
        }
    }

    ctx.websocket.room = ctx.query.room || '';

    ctx.app.ws.server.clients.add(ctx.websocket);

    const deviceParsed = UAParser(ctx.get('user-agent'));
    const deviceId = murmurHash(ctx.request.ip + ctx.get('user-agent'), deviceHashSeed);
    const deviceMeta = {
        type: (deviceParsed.device.type || '').trim(),
        device: `${deviceParsed.device.vendor || ''} ${deviceParsed.device.model || ''}`.trim(),
        os: `${deviceParsed.os.name || ''} ${deviceParsed.os.version || ''}`.trim(),
        browser: `${deviceParsed.browser.name || ''} ${deviceParsed.browser.version || ''}`.trim(),
    };
    deviceConnected.forEach((v, k) => ctx.websocket.send(JSON.stringify({
        event: 'connect',
        data: {
            id: k,
            ...v,
        },
    })));
    deviceConnected.set(deviceId, deviceMeta);
    wsBoardcast(ctx.app.ws, JSON.stringify({
        event: 'connect',
        data: {
            id: deviceId,
            ...deviceMeta,
        },
    }));
    ctx.websocket.addEventListener('close', () => {
        wsBoardcast(ctx.app.ws, JSON.stringify({
            event: 'disconnect',
            data: {
                id: deviceId,
            },
        }));
        deviceConnected.delete(deviceId);
        ctx.app.ws.server.clients.delete(ctx.websocket);
    });

    await new Promise(resolve => ctx.websocket.send(JSON.stringify({
        event: 'config',
        data: {
            version: process.env.VERSION,
            text: config.text,
            file: config.file,
        },
    }), resolve));

    ctx.websocket.send(JSON.stringify({
        event: 'receiveMulti',
        data: messageQueue.queue.filter(e => e.data.room === ctx.query.room).map(e => e.data),
    }));
});

export default router;