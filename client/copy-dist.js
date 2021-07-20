const fs = require('fs-extra');

fs.rmSync('../server/static', { recursive: true });
fs.rmSync('../server-node/static', { recursive: true });
fs.copySync('dist', '../server/static');
fs.copySync('dist', '../server-node/static');