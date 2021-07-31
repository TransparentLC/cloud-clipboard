const fs = require('fs-extra');
const glob = require('glob');
const zlib = require('zlib');

(async () => {

/** @type {String[]} */
const files = await new Promise((resolve, reject) => glob(
    'dist/**/*.+(js|css|html|svg)',
    (error, files) => error ? reject(error) : resolve(files)
));

await Promise.all(files.map(e => new Promise((resolve, reject) => {
    const readStream = fs.createReadStream(e);
    const writeStream = fs.createWriteStream(e + '.gz');
    readStream
        .pipe(zlib.createGzip({ level: 9 }))
        .pipe(writeStream)
        .on('finish', error => error ? reject(error) : resolve());
})));

fs.rmSync('../server/static', { recursive: true });
fs.rmSync('../server-node/static', { recursive: true });
fs.copySync('dist', '../server/static');
fs.copySync('dist', '../server-node/static');

})()
