import Koa from 'koa';
import koaWebsocket from 'koa-websocket';

/**
 * @param {Koa.ParameterizedContext<Koa.DefaultState, Koa.DefaultContext, any>} ctx
 * @param {Number} [status]
 * @param {Object|Array} [result]
 * @param {String} [msg]
 */
export const writeJSON = (ctx, status = 200, result = {}, msg = '') => {
    ctx.status = status;
    ctx.body = {
        code: status,
        msg,
        result,
    };
};

/**
 * @param {koaWebsocket.Server<any, any>} wsServer
 * @param {*} data
 */
export const wsBoardcast = (wsServer, data) => wsServer.server.clients.forEach(client => client.send(data));