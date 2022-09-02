<?php

declare(strict_types=1);

namespace dutchie027\Wallbox;

class Push
{

    private $poApp;
    private $poUser;
    /**
     * Default Constructor - Initialize Values
     */
    public function __construct()
    {
        $this->poApp = new \Serhiy\Pushover\Application($_ENV['PUSHOVER_APP']);
        $this->poUser = new \Serhiy\Pushover\Recipient($_ENV['PUSHOVER_USER']);
    }

    public function sendPush($title, $body)
    {
        $message = new \Serhiy\Pushover\Api\Message\Message($body, $title);
        $notification = new \Serhiy\Pushover\Api\Message\Notification($this->poApp, $this->poUser, $message);
        $notification->push();
    }
}
