<?php

namespace App\Utils;

use http\Exception\InvalidArgumentException;

class Config
{
    private static array $data;

    public static function init(): void
    {
        self::parseEnv(__DIR__ . '/../../.env');
    }

    public static function env(string $key, mixed $default = null)
    {
        return self::$data[$key] ?? $default;
    }

    private static function parseEnv($path): void
    {
        if (!file_exists($path)) {
            throw new InvalidArgumentException(sprintf('%s does not exist', $path));
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (str_starts_with(trim($line), '#')) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            $value = trim($value, "\"'\t\n\r\0\x0B");

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                self::$data[$name] = $value;
            }
        }
    }
}