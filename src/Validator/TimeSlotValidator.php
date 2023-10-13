<?php

namespace App\Validator;

use DateTime;
use DateTimeZone;
use App\Response\TimeSlotValidatorResponse;

class TimeSlotValidator 
{
    private DateTime $startDate;
    private DateTime $endDate;
    private const  DATEFORMAT = 'Y-m-d H:i:s';
    private const MAX_BOOKING_DURATION = 12; //hours

    public function __construct(DateTime $startDate, DateTime $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function validate() : TimeSlotValidatorResponse
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

    private function validateDatesAreInTheFuture() : TimeSlotValidatorResponse
    {
        $now = new DateTime(null, new DateTimeZone('Europe/Amsterdam'));
        $invalidDates = [];
    
        if($this->startDate < $now) {
            $invalidDates[] = "start date {$this->startDate->format(self::DATEFORMAT)}";
        }
    
        if($this->endDate < $now) {
            $invalidDates[] = "end date {$this->endDate->format(self::DATEFORMAT)}";
        }

        if(!empty($invalidDates)) {
            return new TimeSlotValidatorResponse(false, "Invalid dates: " . implode(', ', $invalidDates));
        }
        return new TimeSlotValidatorResponse(true, "Dates are in the future");
    }

    private function validateStartDateIsBeforeEndDate() : TimeSlotValidatorResponse
    {
        if ($this->startDate > $this->endDate) {
            return new TimeSlotValidatorResponse(false, "The start date {$this->startDate->format(self::DATEFORMAT)} cannot be greater than the end date {$this->endDate->format(self::DATEFORMAT)}");
        }
        return new TimeSlotValidatorResponse(true, "Start date is before end date");
    }

    private function validateDurationIsNotTooLong() : TimeSlotValidatorResponse
    {
        $maxBookingDurationInSeconds = self::MAX_BOOKING_DURATION * 60 * 60; 
        $diffInSeconds = $this->endDate->getTimestamp() - $this->startDate->getTimestamp();
        
        if ($diffInSeconds > $maxBookingDurationInSeconds) {
            return new TimeSlotValidatorResponse(false, "The booking cannot be longer than 12 hours");
        }

        return new TimeSlotValidatorResponse(true, "The timeslot is valid");
    }
}
