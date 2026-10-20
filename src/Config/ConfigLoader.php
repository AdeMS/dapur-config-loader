<?php

declare(strict_types=1);

namespace AdeMS\Dapur\Config;

use RuntimeException;

final class ConfigLoader
{
    public function __construct(
        private array $config,
        private string $configDirectory,
    ) {
    }

    public function load(?string $host = null): array
    {
        $host ??= $_SERVER['HTTP_HOST'] ?? '';
        $host = strtolower($host);
        $host = preg_replace('/:\d+$/', '', $host);
        $host = trim($host, '.');

        if (!preg_match('/^[a-z0-9.-]+$/', $host)) {
            $host = '';
        }

        $hostConfig = $this->configDirectory . DIRECTORY_SEPARATOR . $host . '.php';
        $defaultConfig = $this->configDirectory . DIRECTORY_SEPARATOR . 'default.php';
        $configFile = $host !== '' && is_file($hostConfig)
            ? $hostConfig
            : $defaultConfig;

        if (!is_file($configFile)) {
            return $this->config;
        }

        $additionalConfig = require $configFile;

        if (!is_array($additionalConfig)) {
            throw new RuntimeException(
                sprintf('File konfigurasi harus mengembalikan array: %s', $configFile)
            );
        }

        return array_replace_recursive($this->config, $additionalConfig);
    }
}
