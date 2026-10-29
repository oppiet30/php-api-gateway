# PHP API Gateway

A learning project: consume Open-Meteo and expose a protected REST API using PHP 8+, MariaDB, PDO and cURL.

## Requirements
PHP 8.1+, pdo_mysql, curl, MariaDB, Apache mod_rewrite (or PHP built-in dev server).

## Setup

1. Import the schema: `mariadb -u root -p < database/schema.sql`.
2. Create a restricted database user (replace the password):
   ```sql
   CREATE USER 'php_api_gateway'@'localhost' IDENTIFIED BY 'CHANGE_ME';
   GRANT SELECT, INSERT, UPDATE ON php_api_gateway.* TO 'php_api_gateway'@'localhost';
   ```
   Note that `127.0.0.1` and `localhost` may match different MariaDB account host entries.
3. Set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` in the process environment.
4. Set a strong random `JWT_SECRET` (at least 32 bytes) in the **web server environment**, never in Git. For example, generate a value locally with `php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'`. Keep the secret stable across restarts and consistent across workers. Optionally configure `JWT_ISSUER` and `JWT_AUDIENCE`.
5. Generate an API key: `php bin/create-api-key.php demo-client`.
6. Development only: `php -S 127.0.0.1:8000 -t public public/index.php`.
7. Test:
   ```sh
   export GATEWAY_API_KEY='your-generated-key'
   curl -i -H "Authorization: Bearer $GATEWAY_API_KEY" 'http://127.0.0.1:8000/api/v1/weather?latitude=41.8975&longitude=-84.0372'
   curl -i -X POST -H "Authorization: Bearer $GATEWAY_API_KEY" 'http://127.0.0.1:8000/api/v1/auth/token'
   ```

## Authentication
- `GET /api/v1/weather`: accepts an active `pgw_` API key **or** a valid JWT in the Bearer header.
- `POST /api/v1/auth/token`: accepts **only** an active `pgw_` API key; returns a 15-minute HS256 JWT.
- JWTs contain issuer, audience, subject (client key ID), issue time, not-before and expiry. A revoked API key invalidates JWTs issued for that key.
- JWTs are not encrypted. Protect tokens and the signing secret.
- See [Postman tutorial](docs/postman.md).
- Run JWT unit tests: `php tests/jwt_test.php`.

## Production notes
Use HTTPS and limit network exposure. Protect secrets and files outside the document root, configure Apache to forward the Authorization header, add rate limiting, request logging and key rotation, and consider caching provider responses. The API key has no built-in expiration in this starter. Open-Meteo's free noncommercial service has usage terms and limits.
