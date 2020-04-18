<?php
$zip = new ZipArchive();
$zip->open('cloud-clipboard.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);
$tar = new PharData('cloud-clipboard.tar');

foreach ([
    'server/App',
    'server/vendor',
    'server/static',
    'server/main.php',
    'server/config.json',
] as $path) {
    if (is_file($path)) {
        $files = [$path];
    } else {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS)
        );
    }
    foreach ($files as $file) {
        $localname = preg_replace('/^(server\/)/', '', $file);
        if (is_dir($file)) continue;
        echo $file . "\n";
        $zip->addFile($file, $localname);
        $tar->addFile($file, $localname);
    }
}

$zip->close();
$tar->compress(Phar::GZ);
unlink('cloud-clipboard.tar');