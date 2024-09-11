<?php

namespace PremierMcpe\TradeGui\Exceptions;

use Exception;

class InvalidDirectionException extends Exception
{
    public function __construct(string $message = 'Invalid direction. Must be either `STR_PAD_LEFT` or `STR_PAD_RIGHT`')
    {
        parent::__construct($message);
    }
}
