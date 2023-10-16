<?php

namespace App\Validator;

use DateTime;
use App\Response\RoomAvailabilityResponse;


class RoomAvailabilityValidator {
    private DateTime $startDate;
    private DateTime $endDate;
    private $existingBookings;
    private const  DATEFORMAT = 'Y-m-d H:i';

    public function __construct(DateTime $startDate, DateTime $endDate, $existingBookings)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->bookings = $existingBookings;
    }

    public function validate() : RoomAvailabilityResponse
    {
        $conflictingHoursValidation = $this->validateHourConflictsWithExistingBookings();
        if(!$conflictingHoursValidation->isSuccess()) {
            return $conflictingHoursValidation;
        }

        $overlapValidation = $this->validateBookingIsNotOverlapping();
        if(!$overlapValidation->isSuccess()) {
            return $overlapValidation;
        }

        return new RoomAvailabilityResponse(true, "The room is available");
    }

    private function validateHourConflictsWithExistingBookings() : RoomAvailabilityResponse
    {
        foreach($this->existingBookings as $booking) {
            if($this->startDate >= $booking->getStartDate() && $this->startDate < $booking->getEndDate()) {
                return new RoomAvailabilityResponse(
                    false,
                    "The start date {$this->startDate->format(self::DATEFORMAT)} is in the range of another booking, {$this->createMessageOfConflictingBooking($booking)}");
            }
            if($this->endDate > $booking->getStartDate() && $this->endDate <= $booking->getEndDate()) {
                return new RoomAvailabilityResponse(
                    false,
                    "The end date {$this->endDate->format(self::DATEFORMAT)} is in the range of another booking, {$this->createMessageOfConflictingBooking($booking)}");
            }
        }
        return new RoomAvailabilityResponse(true, "The start and end date are not in the range of another booking");
    }

    private function validateBookingIsNotOverlapping() : RoomAvailabilityResponse
    {
        foreach($this->existingBookings as $booking) {
            if($this->startDate <= $booking->getStartDate() && $this->endDate >= $booking->getEndDate()) {
                return new RoomAvailabilityResponse(false, "The booking is overlapping with an existing booking, {$this->createMessageOfConflictingBooking($booking)}");
            }
        }
        return new RoomAvailabilityResponse(true, "The booking is not overlapping with an existing booking");
    }

    private function createMessageOfConflictingBooking($booking) : string
    {
        return "the booking starts at {$booking->getStartDate()->format(self::DATEFORMAT)} and ends at {$booking->getEndDate()->format(self::DATEFORMAT)}";
    }
}




