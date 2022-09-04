<?php

declare(strict_types=1);

namespace dutchie027\Wallbox;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Config
{
    private string $token;

    /**
     * Default Constructor - Initialize Values
     */
    public function __construct()
    {
        $tokenString = $_ENV['API_USERNAME'] . ':' . $_ENV['API_PASSWORD'];
        $this->token = base64_encode($tokenString);
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
