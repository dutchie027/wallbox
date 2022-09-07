<?php

declare(strict_types=1);

namespace dutchie027\Wallbox\Init;

use Composer\Script\Event;

class Setup
{
    private const ENV_VALS = [
        'API_USERNAME',
        'API_PASSWORD',
        'TIMEOUT_SECONDS',
        'PUSHOVER_APP',
        'PUSHOVER_USER',
        'LOG_PREFIX',
        'LOG_LEVEL',
        'LOG_DIR',
    ];

    public static function generateBlankEnv(Event $event): void
    {
        $config = $event->getComposer()->getConfig()->get('vendor-dir');
        $envFile = dirname($config) . '/.env.sample';
        $myfile = fopen($envFile, 'w') or die('Unable to open file!');

        foreach (self::ENV_VALS as $key) {
            $line = $key . '=""' . PHP_EOL;
            fwrite($myfile, $line);
        }
        fclose($myfile);
    }
}
