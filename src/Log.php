<?php

declare(strict_types=1);

namespace dutchie027\Wallbox;

use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

final class Log
{

    private string $s_log_dir;

    private int $s_log_level;

    private string $s_log_prefix;

    public $log;

    /**
     * Default Constructor - Initialize Values
     */
    public function __construct()
    {
        $this->s_log_dir = sys_get_temp_dir();
        $this->s_log_prefix = 'wallbox';
        $this->s_log_level = 100;
        // create a log channel
        $this->log = new Logger($this->s_log_prefix);
        $this->log->pushHandler(new StreamHandler($this->s_log_dir . '/' . $this->s_log_prefix . '.log', Level::Warning));
    }

    public function returnLogger()
    {
        return $this->log;
    }
}
