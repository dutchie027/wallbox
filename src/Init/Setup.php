<?php

declare(strict_types=1);

namespace dutchie027\Wallbox\Init;

use Composer\Script\Event;

class Setup
{
    private const API_KVPS = [
        'API_USERNAME',
        'API_PASSWORD',
    ];

    private const LOG_KVPS = [
        'LOG_PREFIX',
        'LOG_LEVEL',
        'LOG_DIR',
    ];

    private const PUSH_KVPS = [
        'PUSHOVER_APP',
        'PUSHOVER_USER',
    ];

    private const SERVICE_KVPS = [
        'TIMEOUT_SECONDS',
    ];

    private const KVP_SECTIONS = [
        'api',
        'service',
        'push',
        'log',
    ];

    private static $iniFile;

    public static function generateBlankIni(Event $event): void
    {
        $config = $event->getComposer()->getConfig()->get('vendor-dir');
        $envFile = dirname($config) . DIRECTORY_SEPARATOR . 'wallbox.ini';
        $myfile = fopen($envFile, 'w') or die('Unable to open file!');

        foreach (self::KVP_SECTIONS as $key) {
            $header = '[' . $key . ']' . PHP_EOL;
            fwrite($myfile, $header);

            foreach (constant('self::' . strtoupper($key) . '_KVPS') as $kvp) {
                $line = $kvp . '=' . PHP_EOL;
                fwrite($myfile, $line);
            }
            fwrite($myfile, PHP_EOL);
        }
        fclose($myfile);
        self::$iniFile = $envFile;
    }

    /**
     * Method to return the Monolog instance
     */
    public static function getFileLocation(): string
    {
        return self::$iniFile;
    }
}
