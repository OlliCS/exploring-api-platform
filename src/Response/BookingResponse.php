<?php

namespace App\Response;

use App\Entity\Booking;

class BookingResponse
{
    private ?Booking $booking;
    private bool $success;
    private string $message;
    private function __construct(?Booking $booking, bool $success, string $message)
    {
        $this->booking = $booking;
        $this->success = $success;
        $this->message = $message;
    }

    public function getBooking(): ?Booking
    {
        return $this->booking;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
