<?php

namespace App\Response;

use App\Entity\Booking;

class BookingResponse extends ResponseModel
{
    private ?Booking $booking;
    public function __construct(?Booking $booking, bool $success, string $message)
    {
        parent::__construct($success, $message);
        $this->booking = $booking;

    }

    public function getBooking(): ?Booking
    {
        return $this->booking;
    }


}
