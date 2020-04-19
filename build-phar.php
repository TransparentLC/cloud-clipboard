<?php
@unlink('cloud-clipboard.phar');
$phar = new Phar('cloud-clipboard.phar');

foreach ([
    'server/App',
    'server/vendor',
    'server/static',
    'server/main.php',
] as $path) {
    if (is_file($path)) {
        $files = [$path];
    } else {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );
    }
    foreach ($files as $file) {
        $file = str_replace('\\', '/', $file);
        if (is_dir($file)) continue;
        if (strpos($file, '/.git/') !== false) continue;
        if (strpos($file, '/vendor/eaglewu/swoole-ide-helper/') !== false) continue;

        $localname = preg_replace('/^(server\/)/', '', $file);
        echo $file . "\n";
        $phar->addFile($file, $localname);
    }
}

$phar->setStub($phar->createDefaultStub('main.php'));
$phar->compressFiles(Phar::GZ);