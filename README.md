# PHP API Gateway

A learning project: consume Open-Meteo and expose a protected REST API using PHP 8+, MariaDB, PDO and cURL.

## Requirements
PHP 8.1+, pdo_mysql, curl, MariaDB, Apache mod_rewrite (or PHP built-in dev server).

## Setup

1. Import the schema: `mariadb -u root -p < database/schema.sql`.
2. In MariaDB create a restricted database user (substitute a strong password):
   ```sql
   CREATE USER 'php_api_gateway'@'localhost' IDENTIFIED BY 'CHANGE_ME';
   GRANT SELECT, INSERT, UPDATE ON php_api_gateway.* TO 'php_api_gateway'@'localhost';
   ```
   `DB_HOST=localhost` uses a Unix socket, while `127.0.0.1` uses TCP. Configure the account host to match your deployment.
3. Copy `.env.example` to `.env` in the project root and edit `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, and `DB_PASSWORD`. The application loads it automatically from `config/bootstrap.php`. Existing process environment variables take precedence. `.env` is already excluded by `.gitignore`. Also set `JWT_SECRET` to a strong random value (at least 32 bytes). Restrict `.env` file access to the deployment account and the PHP-FPM worker, and never commit it.
   Generate the JWT signing secret locally with `php -r 'echo bin2hex(random_bytes(32)), PHP_EOL;'`. Keep it stable across restarts and consistent across workers. Optionally configure `JWT_ISSUER` and `JWT_AUDIENCE`.
4. Generate a client key: `php bin/create-api-key.php demo-client`.
5. Development only: `php -S 127.0.0.1:8000 -t public public/index.php`.
6. Test with:
   ```sh
   export GATEWAY_API_KEY='your-generated-key'
   curl -i -H "Authorization: Bearer $GATEWAY_API_KEY" 'http://127.0.0.1:8000/api/v1/weather?latitude=41.8975&longitude=-84.0372'
   curl -i -X POST -H "Authorization: Bearer $GATEWAY_API_KEY" 'http://127.0.0.1:8000/api/v1/auth/token'
   ```
7. Run `php clients/example.php` or `python3 clients/example.py` with `GATEWAY_API_KEY` set.

## Authentication
- `GET /api/v1/weather`: accepts an active `pgw_` API key **or** a valid JWT in the Bearer header.
- `POST /api/v1/auth/token`: accepts **only** an active `pgw_` API key; returns a 15-minute HS256 JWT.
- JWTs contain issuer, audience, subject (client key ID), issue time, not-before and expiry. A revoked API key invalidates JWTs issued for that key.
- JWTs are not encrypted. Protect tokens and the signing secret.
- See [Postman tutorial](docs/postman.md).
- Run JWT unit tests: `php tests/jwt_test.php`.

## Production notes
Use HTTPS, restrict API key exposure, protect secrets and application files outside the document root, configure Apache to forward the Authorization header, add rate limiting, logging and key rotation, implement expiration and scopes, and consider caching provider responses. Open-Meteo's free noncommercial service is subject to its terms and limits. This starter does not implement rate limiting, key expiration, or request logging.

## Configuration tests
Run `php tests/env_test.php` to test the lightweight `.env` loader. It supports plain `KEY=value`, quoted values, comments, and existing environment overrides; it does not implement variable interpolation or advanced dotenv syntax.
