<?php

use IPP\Core\Exception\IPPException;

/**
* Class for throwing exceptions
*/
class Interexception extends IPPException{

    public function __construct(string $message, int $return_code)
    {
        parent::__construct($message, $return_code, null, false);
    }
}

