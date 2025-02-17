import crypto from 'node:crypto';
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';

import config from './config.js';

const storageFolder = config.server.storageDir || path.join(os.tmpdir(), '.cloud-clipboard-storage');
if (!fs.existsSync(storageFolder)) {
    fs.mkdirSync(storageFolder);
}

class UploadedFile {
    /**
     * @param {String} name
     */
    constructor(name) {
        this.name = name;
        this.uuid = crypto.randomBytes(16).toString('hex');
        this.path = path.join(storageFolder, this.uuid);
        this.size = 0;
        /** @type {Number} */
        this.uploadTime = undefined;
        /** @type {Number} */
        this.expireTime = undefined;
        this.writePromise = Promise.resolve();
    }

    /**
     * @param {Uint8Array} data
     */
    write(data) {
        if (data.length > config.file.chunk) {
            throw new Error(`分片长度不能超过 ${config.file.chunk} 字节`);
        }
        if (this.size + data.length > config.file.limit) {
            throw new Error('文件大小已超过限制');
        }
        this.writePromise = this.writePromise
            .then(() => fs.promises.appendFile(this.path, data))
            .then(() => this.size += data.length);
        return this.writePromise;
    }

    finish() {
        this.writePromise = this.writePromise.then(() => {
            this.uploadTime = Math.round(Date.now() / 1000);
            this.expireTime = this.uploadTime + config.file.expire;
        });
        return this.writePromise;
    }

    remove() {
        this.writePromise = this.writePromise.then(() => fs.promises.rm(this.path)).catch(() => {});
        return this.writePromise;
    }
}

/** @type {Map<String, UploadedFile>} */
const uploadFileMap = new Map;

setInterval(() => {
    /** @type {String[]} */
    const toRemove = [];
    const currentTime = Math.round(Date.now() / 1000);
    uploadFileMap.forEach((v, k) => {
        if (v.expireTime < currentTime) toRemove.push(k);
    });
    toRemove.forEach(e => {
        try {
            uploadFileMap.get(e).remove();
            uploadFileMap.delete(e);
        } catch (err) {

        }
    });
}, 1800000);

export {
    UploadedFile,
    uploadFileMap,
    storageFolder,
};
