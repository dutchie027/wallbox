<?php

declare(strict_types=1);

namespace dutchie027\Wallbox;

class Config
{
    private static string $s_log_dir;

    private static int $s_log_level;

    private static string $s_log_prefix;

    private string $token;

    /**
     * Default Constructor - Initialize Values
     */
    public function __construct()
    {
        $tokenString = $_ENV['API_USERNAME'] . ':' . $_ENV['API_PASSWORD'];
        $this->token = base64_encode($tokenString);
        self::$s_log_dir = sys_get_temp_dir();
        self::$s_log_prefix = 'wallbox';
        self::$s_log_level = 100;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Returns Log Directory
     */
    public static function getLogDir(): string
    {
        return self::$s_log_dir;
    }

    /**
     * Returns Logging Level
     */
    public static function getLogLevel(): int
    {
        return self::$s_log_level;
    }

    /**
     * Returns Log Prefix
     */
    public static function getLogPrefix(): string
    {
        return self::$s_log_prefix;
    }
}
