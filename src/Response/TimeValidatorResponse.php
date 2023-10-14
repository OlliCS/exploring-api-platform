<?php

namespace App\Response;


class TimeValidatorResponse extends BaseResponse
{
    public function __construct( bool $success, string $message)
    {        
        parent::__construct($success, $message);
    }

}