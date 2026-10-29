<?php
declare(strict_types=1);
$key = getenv('GATEWAY_API_KEY');
$url = getenv('GATEWAY_URL') ?: 'http://127.0.0.1:8000';
if (!$key) throw new RuntimeException('Set GATEWAY_API_KEY');
$ch = curl_init(rtrim($url, '/') . '/api/v1/weather?latitude=41.8975&longitude=-84.0372');
curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20, CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $key]]);
$result = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
if ($result === false) throw new RuntimeException(curl_error($ch));
curl_close($ch);
echo "HTTP $status\n$result\n";
