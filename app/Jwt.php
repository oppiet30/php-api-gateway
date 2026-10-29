<?php
declare(strict_types=1);
namespace App;
final class Jwt {
    private static function secret(): string {
        $secret = getenv('JWT_SECRET');
        if (!is_string($secret) || strlen($secret) < 32) throw new \RuntimeException('JWT_SECRET must be at least 32 bytes');
        return $secret;
    }
    private static function b64(string $data): string {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    private static function decode(string $value): string|false {
        if (!preg_match('/^[A-Za-z0-9_-]+$/D', $value)) return false;
        return base64_decode(strtr($value, '-_', '+/'), true);
    }
    public static function issue(int $clientId, ?int $now = null): array {
        $now ??= time();
        $issuer = getenv('JWT_ISSUER') ?: 'php-api-gateway';
        $audience = getenv('JWT_AUDIENCE') ?: 'php-api-gateway-clients';
        $header = self::b64(json_encode(['alg'=>'HS256','typ'=>'JWT'], JSON_THROW_ON_ERROR));
        $claims = ['iss'=>$issuer,'aud'=>$audience,'sub'=>(string)$clientId,'iat'=>$now,'nbf'=>$now,'exp'=>$now+900];
        $payload = self::b64(json_encode($claims, JSON_THROW_ON_ERROR));
        $input = $header . '.' . $payload;
        $signature = self::b64(hash_hmac('sha256', $input, self::secret(), true));
        return ['access_token'=>$input.'.'.$signature,'token_type'=>'Bearer','expires_in'=>900];
    }
    public static function validate(string $token, ?int $now = null): ?int {
        $now ??= time();
        if (strlen($token) > 4096 || substr_count($token, '.') !== 2) return null;
        [$h,$p,$s] = explode('.', $token);
        $headerJson = self::decode($h);
        $payloadJson = self::decode($p);
        if ($headerJson === false || $payloadJson === false || self::decode($s) === false) return null;
        try {
            $header = json_decode($headerJson, true, 16, JSON_THROW_ON_ERROR);
            $claims = json_decode($payloadJson, true, 16, JSON_THROW_ON_ERROR);
        } catch (\JsonException) { return null; }
        if (!is_array($header) || ($header['alg'] ?? null) !== 'HS256' || ($header['typ'] ?? null) !== 'JWT' || !is_array($claims)) return null;
        $expected = self::b64(hash_hmac('sha256', $h.'.'.$p, self::secret(), true));
        if (!hash_equals($expected, $s)) return null;
        if (($claims['iss'] ?? null) !== (getenv('JWT_ISSUER') ?: 'php-api-gateway')
            || ($claims['aud'] ?? null) !== (getenv('JWT_AUDIENCE') ?: 'php-api-gateway-clients')) return null;
        foreach (['iat','nbf','exp'] as $field) if (!isset($claims[$field]) || !is_int($claims[$field])) return null;
        if ($claims['iat'] > $now + 30 || $claims['nbf'] > $now + 30 || $claims['exp'] <= $now || $claims['exp'] > $claims['iat'] + 900) return null;
        $sub = $claims['sub'] ?? null;
        if (!is_string($sub) || !preg_match('/^[1-9][0-9]*$/D', $sub) || filter_var($sub, FILTER_VALIDATE_INT) === false) return null;
        return (int)$sub;
    }
}
