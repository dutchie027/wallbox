<?php

declare(strict_types=1);

namespace dutchie027\Wallbox;

class Push
{
    /**
     * Pushover Application Class
     *
     * @var \Serhiy\Pushover\Application
     */
    private $poApp;

    /**
     * Pushover Recipient Class
     *
     * @var \Serhiy\Pushover\Recipient
     */
    private $poUser;

    /**
     * Default Constructor - Initialize Values
     */
    public function __construct()
    {
        $this->poApp = new \Serhiy\Pushover\Application(Config::getPushApp());
        $this->poUser = new \Serhiy\Pushover\Recipient(Config::getPushUser());
    }

    public function sendPush(string $title, string $body): void
    {
        $message = new \Serhiy\Pushover\Api\Message\Message($body, $title);
        $notification = new \Serhiy\Pushover\Api\Message\Notification($this->poApp, $this->poUser, $message);
        $notification->push();
    }
}
