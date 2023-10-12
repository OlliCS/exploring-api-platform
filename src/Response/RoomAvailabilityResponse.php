<?php

namespace App\Response;

use App\Entity\Booking;

class RoomAvailabilityResponse extends ResponseModel
{
    public function __construct( bool $success, string $message)
    {        
        parent::__construct($success, $message);
    }

}