# Postman: API key and JWT
1. Create an environment with `base_url` set to `http://api-gateway.test` (use HTTPS for real secrets) and a **local, sensitive** `api_key` variable.
2. Send `POST {{base_url}}/api/v1/auth/token` with Authorization type **Bearer Token**, token `{{api_key}}`. No request body is required.
3. Copy the `access_token` response into a local, sensitive `jwt` variable.
4. Send `GET {{base_url}}/api/v1/weather?latitude=41.8975&longitude=-84.0372` with Authorization type **Bearer Token**, token `{{jwt}}`.
5. Repeat the weather request using `{{api_key}}`; both should succeed.
6. Remove Authorization or alter one character of the JWT; expect HTTP 401.
7. JWTs expire after 15 minutes; request a fresh token using the API key.

Do not commit or export live keys, JWTs or JWT_SECRET. Configure Apache so the Authorization header reaches PHP.
