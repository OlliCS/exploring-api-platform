<?php

namespace App\Validator;

use DateTime;
use App\Response\RoomAvailabilityResponse;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class RoomAvailabilityValidator {
    private DateTime $startDate;
    private DateTime $endDate;
    private $bookings;

    public function __construct(DateTime $startDate, DateTime $endDate, $bookings)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->bookings = $bookings;
    }

    public function validate() : RoomAvailabilityResponse
    {
        $validation = $this->validateStartHourInExistingBooking();
        if(!$validation->isSuccess()) {
            return $validation;
        }

        $validation = $this->validateEndHourInExistingBooking();
        if(!$validation->isSuccess()) {
            return $validation;
        }

        $validation = $this->validateBookingIsNotOverlapping();
        if(!$validation->isSuccess()) {
            return $validation;
        }

        return new RoomAvailabilityResponse(true, "The room is available");

    }

    private function validateStartHourInExistingBooking() : RoomAvailabilityResponse
    {
        foreach($this->bookings as $booking) {
            if($this->startDate >= $booking->getStartDate() && $this->startDate <= $booking->getEndDate()) {
                return new RoomAvailabilityResponse(false, "The start date {$this->startDate->format('Y-m-d H:i:s')} is already booked");
            }
        }
        return new RoomAvailabilityResponse(true, "The start date {$this->startDate->format('Y-m-d H:i:s')} is available");
    }

    private function validateEndHourInExistingBooking() : RoomAvailabilityResponse
    {
        foreach($this->bookings as $booking) {
            if($this->endDate >= $booking->getStartDate() && $this->endDate <= $booking->getEndDate()) {
                return new RoomAvailabilityResponse(false, "The end date {$this->endDate->format('Y-m-d H:i:s')} is already booked");
            }
        }
        return new RoomAvailabilityResponse(true, "The end date {$this->endDate->format('Y-m-d H:i:s')} is available");
    }

    private function validateBookingIsNotOverlapping() : RoomAvailabilityResponse
    {
        foreach($this->bookings as $booking) {
            if($this->startDate <= $booking->getStartDate() && $this->endDate >= $booking->getEndDate()) {
                return new RoomAvailabilityResponse(false, "The booking is overlapping with an existing booking");
            }
        }
        return new RoomAvailabilityResponse(true, "The booking is not overlapping with an existing booking");
    }



}




