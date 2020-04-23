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
        echo $file . "\n";
        $phar->addFile($file, $localname);
    }
}

$phar->setStub($phar->createDefaultStub('main.php'));
$phar->compressFiles(Phar::GZ);