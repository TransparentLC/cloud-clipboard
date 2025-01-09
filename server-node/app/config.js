import crypto from 'node:crypto';
import fs from 'node:fs';
import path from 'node:path';

const defaultConfigPath = path.join(process.cwd(), 'config.json');

if (!process.argv[2] && !fs.existsSync(defaultConfigPath)) {
    console.log(`\x1b[93mConfig file "${defaultConfigPath}" does not exist.\x1b[39m`);
    console.log('\x1b[93mA default config file is created and used. Check the descriptions in the repository\'s README.md to modify it.\x1b[39m');
    fs.writeFileSync(defaultConfigPath, JSON.stringify({
        server: {
            host: [],
            port: 9501,
            uds: null,
            prefix: '',
            key: null,
            cert: null,
            history: 10,
            auth: false,
            historyFile: null,
            storageDir: null,
        },
        text: {
            limit: 4096,
        },
        file: {
            expire: 3600,
            chunk: 2097152,
            limit: 268435456,
        },
    }, null, 4));
}

/**
 * @type {{
 *  server: {
 *      host: String | String[],
 *      port: [Number],
 *      uds: [String],
 *      prefix: [String],
 *      key: [String],
 *      cert: [String],
 *      forceWss: [Boolean],
 *      history: Number,
 *      auth: Boolean,
 *      historyFile: [String],
 *      storageDir: [String],
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
const config = JSON.parse(fs.readFileSync(process.argv[2] || defaultConfigPath));

if (!config.server.prefix) {
    config.server.prefix = '';
}
if (config.server.auth === true) {
    config.server.auth = '';
    for (let i = 0; i < 6; i++) {
        config.server.auth += '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz'[crypto.randomInt(62)];
    }
}
if (config.server.auth) {
    config.server.auth = config.server.auth.toString();
}

export default config;
