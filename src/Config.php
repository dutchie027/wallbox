<?php

declare(strict_types=1);

namespace dutchie027\Wallbox;

class Config
{
    /**
     * 'DEBUG'|'INFO'|'NOTICE'|'WARNING'|'ERROR'|'CRITICAL'|'ALERT'|'EMERGENCY'
     */
    private const ALLOWED_LEVELS = [100, 200, 250, 300, 400, 500, 550, 600];

    private static string $s_log_dir;

    private static int $s_log_level;

    private static string $s_log_prefix;

    private static string $s_push_user;

    private static string $s_push_app;

    private string $token;

    /**
     * Default Constructor - Initialize Values
     */
    public function __construct(string $loc = 'wallbox.ini')
    {
        $stack = debug_backtrace();
        $firstFrame = $stack[count($stack) - 1];
        $scriptDir = dirname($firstFrame['file']);
        $file = is_file($loc) ? $loc : $scriptDir . "/" . $loc;
        $this->ini_data = $this->returnIniArray($file);
        $tokenString = $this->returnContents('api/API_USERNAME', 'user') . ':' . $this->returnContents('api/API_PASSWORD', 'password');
        $this->token = base64_encode($tokenString);
        self::$s_log_dir = $this->returnContents('log/LOG_DIR', sys_get_temp_dir());
        self::$s_log_prefix = $this->returnContents('log/LOG_PREFIX', 'wallbox');
        self::$s_log_level = $this->returnLogLevel('log/LOG_LEVEL', 100);
        self::$s_push_user = $this->returnContents('push/PUSHOVER_USER', '');
        self::$s_push_app = $this->returnContents('push/PUSHOVER_APP', '');
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

    /**
     * Returns Log Prefix
     */
    public static function getPushUser(): string
    {
        return self::$s_push_user;
    }

    /**
     * Returns Log Prefix
     */
    public static function getPushApp(): string
    {
        return self::$s_push_app;
    }

    /**
     * Checks existence of ini file and then returns KVP Array
     *
     * @param string $loc
     *
     * @return array<string,array<string>>
     */
    private function returnIniArray($loc): array
    {
        $return = [];

        if (file_exists($loc)) {
            $return = parse_ini_file($loc, true) ?: [];
        }

        return $return;
    }

    /**
     * Used to set values from .ini array or default value
     */
    private function returnContents(string $var, string $dv): string
    {
        [$root, $key] = explode('/', $var);
        return (isset($this->ini_data[$root][$key])) ? $this->ini_data[$root][$key] : $dv;
    }

    /**
     * Used to set values from .ini array or default value
     */
    private function returnLogLevel(string $var, int $dv): int
    {
        [$root, $key] = explode('/', $var);
        return ((isset($this->ini_data[$root][$key])) && (in_array((int) $this->ini_data[$root][$key], self::ALLOWED_LEVELS, true))) ? (int) $this->ini_data[$root][$key] : $dv;
    }
}
