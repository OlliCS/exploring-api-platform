<?php

namespace App\Response;


class TimeSlotValidatorResponse extends BaseResponse
{
    public function __construct( bool $success, string $message)
    {        
        parent::__construct($success, $message);
    }

}