<?php
declare(strict_types=1);
require dirname(__DIR__) . '/config/bootstrap.php';
if (PHP_SAPI !== 'cli') exit(1);
$name = trim($argv[1] ?? '');
if ($name === '' || strlen($name) > 100) {
    fwrite(STDERR, "Usage: php bin/create-api-key.php CLIENT_NAME (1-100 chars)\n");
    exit(1);
}
$key = 'pgw_' . bin2hex(random_bytes(32));
$db = App\Database::connect();
$statement = $db->prepare('INSERT INTO api_keys (name, key_hash) VALUES (?, ?)');
$statement->execute([$name, hash('sha256', $key)]);
echo "Save this API key now; it cannot be retrieved later:\n$key\n";
