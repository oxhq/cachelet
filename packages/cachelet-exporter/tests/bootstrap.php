<?php

$autoloaders = [
    dirname(__DIR__).'/vendor/autoload.php',
    dirname(__DIR__, 3).'/vendor/autoload.php',
];

foreach ($autoloaders as $autoload) {
    if (file_exists($autoload)) {
        require_once $autoload;
        break;
    }
}

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Oxhq\\Cachelet\\Exporter\\Tests\\' => __DIR__.DIRECTORY_SEPARATOR,
        'Oxhq\\Cachelet\\Exporter\\' => dirname(__DIR__).DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR,
    ];

    foreach ($prefixes as $prefix => $basePath) {
        if (! str_starts_with($class, $prefix)) {
            continue;
        }

        $relative = substr($class, strlen($prefix));
        $file = $basePath.str_replace('\\', DIRECTORY_SEPARATOR, $relative).'.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
});
