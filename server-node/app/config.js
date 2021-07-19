import fs from 'fs';

/**
 * @type {{
 *  server: {
 *      host: String,
 *      port: Number,
 *      wss: Boolean,
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
const config = JSON.parse(await fs.promises.readFile('config.json'));

if (config.server.auth === true) {
    config.server.auth = Math.floor(Math.random() * 1000000).toString().padStart(6, 0);
}
if (config.server.auth) {
    config.server.auth = config.server.auth.toString()
    console.log(`Authorization code: ${config.server.auth}`);
} else {
    console.log('Authorization code is disabled.');
}

export default config;