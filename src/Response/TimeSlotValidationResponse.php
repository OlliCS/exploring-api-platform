<?php

namespace App\Response;


class TimeSlotValidationResponse extends ResponseModel
{
    public function __construct( bool $success, string $message)
    {        
        parent::__construct($success, $message);
    }

}