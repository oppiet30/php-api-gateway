<?php
declare(strict_types=1);
namespace App;
final class WeatherService {
    public static function current(float $latitude, float $longitude): array {
        $url = 'https://api.open-meteo.com/v1/forecast?' . http_build_query([
            'latitude' => $latitude, 'longitude' => $longitude,
            'current' => 'temperature_2m,relative_humidity_2m,wind_speed_10m',
            'temperature_unit' => 'fahrenheit', 'wind_speed_unit' => 'mph',
            'timezone' => 'auto',
        ]);
        $curl = curl_init($url);
        if ($curl === false) throw new \RuntimeException('Unable to initialize cURL');
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 12, CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $body = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        if ($body === false || $status !== 200) throw new \RuntimeException('Weather provider unavailable: ' . ($error ?: "HTTP $status"));
        $data = json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        if (!isset($data['current']) || !is_array($data['current'])) throw new \RuntimeException('Unexpected weather response');
        return [
            'latitude' => $data['latitude'] ?? $latitude,
            'longitude' => $data['longitude'] ?? $longitude,
            'timezone' => $data['timezone'] ?? null,
            'current' => $data['current'],
            'units' => $data['current_units'] ?? [],
            'provider' => 'Open-Meteo',
        ];
    }
}
