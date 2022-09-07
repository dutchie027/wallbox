<?php

declare(strict_types=1);

namespace dutchie027\Wallbox;

class Config
{
    private const ALLOWED_LEVELS = [100, 200, 250, 300, 400, 500, 550, 600];

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

        self::$s_log_dir = (isset($_ENV['LOG_DIR']) && strlen($_ENV['LOG_DIR']) > 5) ? $_ENV['LOG_DIR'] : sys_get_temp_dir();
        self::$s_log_prefix = (isset($_ENV['LOG_PREFIX']) && strlen($_ENV['LOG_PREFIX']) > 5) ? $_ENV['LOG_PREFIX'] : 'wallbox';
        self::$s_log_level = (isset($_ENV['LOG_LEVEL']) && in_array($_ENV['LOG_LEVEL'], self::ALLOWED_LEVELS, true)) ? $_ENV['LOG_LEVEL'] : 100;
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
