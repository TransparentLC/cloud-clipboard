import crypto from 'node:crypto';
import fs from 'node:fs';
import path from 'node:path';

/**
 * @type {{
 *  server: {
 *      port: Number,
 *      history: Number,
 *      auth: Boolean
 *  },
 *  text: {
 *      limit: Number,
 *  },
 *  file: {
 *      expire: Number,
 *      chunk: Number,
 *      limit: Number,
 *  },
 * }}
 */
const config = JSON.parse(await fs.promises.readFile(path.join(process.cwd(), 'config.json')));

if (config.server.auth === true) {
    config.server.auth = '';
    for (let i = 0; i < 6; i++)
    config.server.auth += '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'[crypto.randomInt(62)];
}
if (config.server.auth) {
    config.server.auth = config.server.auth.toString();
}

export default config;