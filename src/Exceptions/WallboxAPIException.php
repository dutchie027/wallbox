<?php

namespace dutchie027\Wallbox\Exceptions;

class WallboxAPIException extends \Exception
{
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
