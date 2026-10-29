<?php
declare(strict_types=1);
namespace App;
use PDO;
final class ApiKeyAuth {
    public static function authorized(PDO $db, ?string $header): bool {
        if ($header === null || !preg_match('/^Bearer (pgw_[a-f0-9]{64})$/i', $header, $matches)) return false;
        $hash = hash('sha256', $matches[1]);
        $query = $db->prepare('SELECT id FROM api_keys WHERE key_hash = ? AND revoked_at IS NULL LIMIT 1');
        $query->execute([$hash]);
        return $query->fetch() !== false;
    }
}
