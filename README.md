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
3. Set `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` in the process environment. For Apache, use a protected service environment configuration rather than committing secrets.
4. Generate a client key: `php bin/create-api-key.php demo-client`.
5. Development only: `php -S 127.0.0.1:8000 -t public public/index.php`.
6. Test with:
   ```sh
   export GATEWAY_API_KEY='your-generated-key'
   curl -i -H "Authorization: Bearer $GATEWAY_API_KEY" 'http://127.0.0.1:8000/api/v1/weather?latitude=41.8975&longitude=-84.0372'
   ```
7. Run `php clients/example.php` or `python3 clients/example.py` with `GATEWAY_API_KEY` set.

## Production notes
Use HTTPS, restrict API key exposure, protect application files outside the document root, add rate limiting and logging, implement expiration and scopes, and consider caching provider responses. Open-Meteo's free noncommercial service is subject to its terms and limits. This starter does not implement rate limiting, key expiration, or request logging.
