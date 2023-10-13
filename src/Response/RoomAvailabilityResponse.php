<?php

namespace App\Response;

use App\Entity\Booking;

class RoomAvailabilityResponse extends BaseResponse
{
    public function __construct( bool $success, string $message)
    {        
        parent::__construct($success, $message);
    }

}