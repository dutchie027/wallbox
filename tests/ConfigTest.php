<?php

declare(strict_types=1);

namespace dutchie027\Test\Wallbox;

use dutchie027\Wallbox\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var string
     */
    private $tmp_ini;

    protected function setUp(): void
    {
        $this->tmp_ini = tempnam(sys_get_temp_dir(), 'phpunit') ?: sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'php-unit';
        $handle = fopen($this->tmp_ini, 'w');
        $tempDir = sys_get_temp_dir();

        if ($handle) {
            fwrite($handle, '[api]' . PHP_EOL);
            fwrite($handle, 'API_USERNAME="wallbox"' . PHP_EOL);
            fwrite($handle, 'API_PASSWORD="password"' . PHP_EOL . PHP_EOL);
            fwrite($handle, '[service]' . PHP_EOL);
            fwrite($handle, 'TIMEOUT_SECONDS=30' . PHP_EOL . PHP_EOL);
            fwrite($handle, '[push]' . PHP_EOL);
            fwrite($handle, 'PUSHOVER_APP="appkey"' . PHP_EOL);
            fwrite($handle, 'PUSHOVER_USER="userkey"' . PHP_EOL . PHP_EOL);
            fwrite($handle, '[log]' . PHP_EOL);
            fwrite($handle, 'LOG_PREFIX="wallbox"' . PHP_EOL);
            fwrite($handle, 'LOG_LEVEL=100' . PHP_EOL);
            fwrite($handle, 'LOG_DIR="' . $tempDir . '"' . PHP_EOL);
            fclose($handle);
        }

        $this->config = new Config($this->tmp_ini);
    }

    public function testgetToken(): void
    {
        self::assertEquals('d2FsbGJveDpwYXNzd29yZA==', $this->config->getToken());
    }

    public function testgetPushApp(): void
    {
        self::assertEquals('appkey', $this->config->getPushApp());
    }

    public function testgetPushUser(): void
    {
        self::assertEquals('userkey', $this->config->getPushUser());
    }

    public function testgetLogDir(): void
    {
        self::assertEquals(sys_get_temp_dir(), Config::getLogDir());
    }

    public function testgetLogLevel(): void
    {
        self::assertEquals(100, Config::getLogLevel());
    }

    public function testgetLogPrefix(): void
    {
        self::assertEquals('wallbox', Config::getLogPrefix());
    }

    public function testgetServiceTimeout(): void
    {
        self::assertEquals(60, $this->config->getServiceTimeout());
    }
}
