<?php
declare(strict_types=1);
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) return;
    $path = dirname(__DIR__) . '/app/' . substr($class, strlen($prefix)) . '.php';
    if (is_file($path)) require $path;
});
require __DIR__ . '/Env.php';
\App\Env::load(dirname(__DIR__) . '/.env');
