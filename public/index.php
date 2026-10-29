<?php
declare(strict_types=1);
require dirname(__DIR__) . '/config/bootstrap.php';
use App\Database;
use App\ApiKeyAuth;
use App\TokenAuth;
use App\Jwt;
use App\WeatherService;
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function respond(int $status, array $body): never {
    http_response_code($status);
    echo json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    exit;
}
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$routes = ['/api/v1/weather'=>'GET', '/api/v1/auth/token'=>'POST'];
if (!is_string($path) || !isset($routes[$path])) respond(404, ['error'=>'Not found']);
if (($_SERVER['REQUEST_METHOD'] ?? '') !== $routes[$path]) {
    header('Allow: '.$routes[$path]);
    respond(405, ['error'=>'Method not allowed']);
}
try {
    $db = Database::connect();
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    if ($path === '/api/v1/auth/token') {
        $clientId = ApiKeyAuth::clientId($db, $header);
        if ($clientId === null) respond(401, ['error'=>'Valid API key required']);
        respond(200, Jwt::issue($clientId));
    }
    if (TokenAuth::clientId($db, $header) === null) respond(401, ['error'=>'Invalid or missing credentials']);
    $lat = filter_input(INPUT_GET, 'latitude', FILTER_VALIDATE_FLOAT);
    $lon = filter_input(INPUT_GET, 'longitude', FILTER_VALIDATE_FLOAT);
    if ($lat === null || $lat === false || $lon === null || $lon === false || $lat < -90 || $lat > 90 || $lon < -180 || $lon > 180) {
        respond(400, ['error'=>'Valid latitude (-90..90) and longitude (-180..180) are required']);
    }
    respond(200, WeatherService::current((float)$lat, (float)$lon));
} catch (Throwable $e) {
    error_log((string)$e);
    respond(502, ['error'=>'Service temporarily unavailable']);
}
