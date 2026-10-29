<?php
declare(strict_types=1);
namespace App;

/**
 * Minimal .env loader for KEY=value settings.
 * Supports blank lines, full-line comments, and single/double quoted values.
 * Does not overwrite variables supplied by the process environment.
 */
final class Env {
    public static function load(string $file): void {
        if (!is_file($file)) return;
        if (!is_readable($file)) throw new \RuntimeException('Environment configuration file is not readable');
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        if ($lines === false) throw new \RuntimeException('Unable to read environment configuration');
        foreach ($lines as $number => $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (!preg_match('/^([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)$/D', $line, $matches)) {
                throw new \RuntimeException('Invalid .env syntax on line ' . ($number + 1));
            }
            $key = $matches[1];
            $value = trim($matches[2]);
            if (strlen($value) >= 2 && ($value[0] === '"' || $value[0] === "'")) {
                if (substr($value, -1) !== $value[0]) {
                    throw new \RuntimeException('Unterminated .env quote on line ' . ($number + 1));
                }
                $value = substr($value, 1, -1);
            } elseif (str_contains($value, '#')) {
                // Only a space-prefixed # starts an inline comment.
                $value = rtrim(preg_replace('/\s+#.*$/', '', $value));
            }
            if (getenv($key) !== false) continue;
            if (!putenv($key . '=' . $value)) throw new \RuntimeException('Failed to configure environment');
        }
    }
}
