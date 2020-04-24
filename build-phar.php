<?php
@unlink('cloud-clipboard.phar');
@unlink('cloud-clipboard-gz.phar');
@unlink('cloud-clipboard-bz2.phar');

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

        $excluded = false;
        foreach ([
            '.git',
            '.gitignore',
            '.codeclimate.yml',
            '.travis.yml',
            'phpunit.xml',
            'doc',
            'docs',
            'test',
            'test_old',
            'tests',
            'example',
            'examples',
            'sample',
            'samples',
            'vendor-bin',
            'swoole-ide-helper',
        ] as $keyword) {
            if (in_array($keyword, explode('/', $file))) {
                $excluded = true;
                break;
            }
        }
        if ($excluded) continue;

        $localname = preg_replace('/^(server\/)/', '', $file);

        $minify = false;
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $minify = true;
            $minified = php_strip_whitespace($file);
            $file .= '.min';
            file_put_contents($file, $minified);
        } else if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
            $minify = true;
            $minified = json_encode(json_decode(file_get_contents($file), true), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $file .= '.min';
            file_put_contents($file, $minified);
        }

        $phar->addFile($file, $localname);
        if ($minify) unlink($file);
        echo $file . "\n";
    }
}

$phar->setStub($phar->createDefaultStub('main.php'));
// $phar->extractTo('extract');

if (extension_loaded('zlib')) {
    copy('cloud-clipboard.phar', 'cloud-clipboard-gz.phar');
    (new Phar('cloud-clipboard-gz.phar'))->compressFiles(Phar::GZ);
} else {
    echo "GZ compression is not enabled.\n";
}

if (extension_loaded('bz2')) {
    copy('cloud-clipboard.phar', 'cloud-clipboard-bz2.phar');
    (new Phar('cloud-clipboard-bz2.phar'))->compressFiles(Phar::BZ2);
} else {
    echo "BZ2 compression is not enabled.\n";
}