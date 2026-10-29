<?php
declare(strict_types=1);
require dirname(__DIR__) . '/config/bootstrap.php';
use App\Jwt;
putenv('JWT_SECRET=test-only-secret-with-at-least-32-characters');
putenv('JWT_ISSUER=php-api-gateway');
putenv('JWT_AUDIENCE=php-api-gateway-clients');
function check(bool $condition, string $message): void {
    if (!$condition) throw new RuntimeException("FAIL: $message");
    echo "PASS: $message\n";
}
$now = time();
$issued = Jwt::issue(42, $now);
$token = $issued['access_token'];
check(Jwt::validate($token, $now) === 42, 'valid JWT');
check(Jwt::validate($token, $now+901) === null, 'expired JWT');
check(Jwt::validate($token . 'broken', $now) === null, 'tampered JWT');
check(Jwt::validate('malformed', $now) === null, 'malformed JWT');
putenv('JWT_AUDIENCE=other');
check(Jwt::validate($token, $now) === null, 'wrong audience');
putenv('JWT_AUDIENCE=php-api-gateway-clients');
putenv('JWT_ISSUER=other');
check(Jwt::validate($token, $now) === null, 'wrong issuer');
echo "JWT tests completed\n";
