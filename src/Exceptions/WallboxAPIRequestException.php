<?php

namespace dutchie027\Wallbox\Exceptions;

class WallboxAPIRequestException extends WallboxAPIException
{
    public function __construct($message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
