<?php
declare(strict_types=1);
namespace App;
use PDO;
final class TokenAuth {
    public static function clientId(PDO $db, ?string $header): ?int {
        if ($header === null || !preg_match('/^Bearer ([^\s]+)$/D', $header, $matches)) return null;
        $token = $matches[1];
        if (preg_match('/^pgw_[a-f0-9]{64}$/D', $token)) return ApiKeyAuth::clientId($db, $header);
        $id = Jwt::validate($token);
        if ($id === null) return null;
        $query = $db->prepare('SELECT id FROM api_keys WHERE id = ? AND revoked_at IS NULL');
        $query->execute([$id]);
        return $query->fetch() !== false ? $id : null;
    }
}
