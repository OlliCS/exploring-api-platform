<?php

namespace App\Validator;

use DateTime;
use DateTimeZone;
use App\Response\TimeValidatorResponse;

class TimeValidator 
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

    public function validate() : TimeValidatorResponse
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

    private function validateDatesAreInTheFuture() : TimeValidatorResponse
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
            return new TimeValidatorResponse(false, "Invalid dates: " . implode(', ', $invalidDates));
        }
        return new TimeValidatorResponse(true, "Dates are in the future");
    }

    private function validateStartDateIsBeforeEndDate() : TimeValidatorResponse
    {
        if ($this->startDate > $this->endDate) {
            return new TimeValidatorResponse(false, "The start date {$this->startDate->format(self::DATEFORMAT)} cannot be greater than the end date {$this->endDate->format(self::DATEFORMAT)}");
        }
        return new TimeValidatorResponse(true, "Start date is before end date");
    }

    private function validateDurationIsNotTooLong() : TimeValidatorResponse
    {
        $maxBookingDurationInSeconds = self::MAX_BOOKING_DURATION * 60 * 60; 
        $diffInSeconds = $this->endDate->getTimestamp() - $this->startDate->getTimestamp();
        
        if ($diffInSeconds > $maxBookingDurationInSeconds) {
            return new TimeValidatorResponse(false, "The booking cannot be longer than 12 hours");
        }

        return new TimeValidatorResponse(true, "The time is valid");
    }
}
