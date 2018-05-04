<?php

namespace App\Exceptions;

class UserLimitReachedException extends \Exception
{
    /**
     * @param string    $message
     */
    public function __construct($message = null)
    {
        parent::__construct($message);
    }
}