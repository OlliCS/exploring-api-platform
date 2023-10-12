<?php

namespace App\Validator;

use DateTime;
use App\Response\TimeSlotValidationResponse;

class TimeSlotValidator 
{
    private DateTime $startDate;
    private DateTime $endDate;

    public function __construct(DateTime $startDate, DateTime $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function validate() : TimeSlotValidationResponse
    {
        $validation = $this->validateDatesAreInTheFuture();
        if (!$validation->isSuccess()) {
            return $validation;
        }

        $validation = $this->validateStartDateIsBeforeEndDate();
        if (!$validation->isSuccess()) {
            return $validation;
        }

        return $this->validateDurationIsNotTooLong();
    }

    private function validateDatesAreInTheFuture() : TimeSlotValidationResponse
    {
        $now = new DateTime();
        $invalidDates = [];
    
        if($this->startDate < $now) {
            $invalidDates[] = "start date {$this->startDate->format('Y-m-d H:i:s')}";
        }
    
        if($this->endDate < $now) {
            $invalidDates[] = "end date {$this->endDate->format('Y-m-d H:i:s')}";
        }

        if(!empty($invalidDates)) {
            return new TimeSlotValidationResponse(false, "Invalid dates: " . implode(', ', $invalidDates));
        }
        return new TimeSlotValidationResponse(true, "Dates are in the future");
    }

    private function validateStartDateIsBeforeEndDate() : TimeSlotValidationResponse
    {
        if ($this->startDate > $this->endDate) {
            return new TimeSlotValidationResponse(false, "The start date {$this->startDate->format('Y-m-d H:i:s')} cannot be greater than the end date {$this->endDate->format('Y-m-d H:i:s')}");
        }
        return new TimeSlotValidationResponse(true, "Start date is before end date");
    }

    private function validateDurationIsNotTooLong() : TimeSlotValidationResponse
    {
        $maxBookingDurationInSeconds = 12 * 60 * 60; // 12 hours
        $diffInSeconds = $this->endDate->getTimestamp() - $this->startDate->getTimestamp();
        
        if ($diffInSeconds > $maxBookingDurationInSeconds) {
            return new TimeSlotValidationResponse(false, "The booking cannot be longer than 12 hours");
        }

        return new TimeSlotValidationResponse(true, "The timeslot is valid");
    }
}
