<?php

namespace App\Validator;

use DateTime;
use DateTimeZone;
use App\Response\TimeValidatorResponse;

class TimeValidator 
{
    private const DATEFORMAT = 'Y-m-d H:i:s';
    private const WORKING_HOURS_START = 8; //hours
    private const WORKING_HOURS_END = 20; //hours
    private const MAX_BOOKING_DURATION_HOURS = 12; //hours
    private const MIN_BOOKING_DURATION_MINUTES = 15; //minutes


    private DateTime $startDate;
    private DateTime $endDate;

    public function __construct(DateTime $startDate, DateTime $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function validate() : TimeValidatorResponse
    {
        $validations = [
            'validateDatesAreInTheFuture',
            'validateStartDateIsBeforeEndDate',
            'validateDurationIsNotShort',
            'validateDateIsNotInTheWeekend',
            'validateTimeIsBetweenWorkingHours',
            'validateDurationIsNotTooLong',
            
        ];

        foreach ($validations as $validation) {
            $validationResponse = $this->$validation();
            if (!$validationResponse->isSuccess()) {
                return $validationResponse;
            }
        }

        return new TimeValidatorResponse(true, "The time is valid");
    }

    private function validateDatesAreInTheFuture() : TimeValidatorResponse
    {
        $now = new DateTime(null, new DateTimeZone('Europe/Amsterdam'));
        $invalidDates = [];
    
        if($this->startDate < $now) {
            $invalidDates[] = "start date {$this->startDate->format(self::DATEFORMAT)} is in the past";
        }
    
        if($this->endDate < $now) {
            $invalidDates[] = "end date {$this->endDate->format(self::DATEFORMAT)} is in the past";
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
        $maxBookingDurationInSeconds = self::MAX_BOOKING_DURATION_HOURS * 60 * 60; 
        $diffInSeconds = $this->endDate->getTimestamp() - $this->startDate->getTimestamp();
        
        if ($diffInSeconds > $maxBookingDurationInSeconds) {
            return new TimeValidatorResponse(false, "The booking cannot be longer than" . self::MAX_BOOKING_DURATION_HOURS . " hours");
        }

        return new TimeValidatorResponse(true, "The duration is not too long");
    }

    private function validateDurationIsNotShort() : TimeValidatorResponse
    {
        $minBookingDurationInSeconds = self::MIN_BOOKING_DURATION_MINUTES * 60; 
        $diffInSeconds = $this->endDate->getTimestamp() - $this->startDate->getTimestamp();
        
        if ($diffInSeconds < $minBookingDurationInSeconds) {
            return new TimeValidatorResponse(false, "The booking cannot be shorter than ". self::MIN_BOOKING_DURATION_MINUTES ." minutes");
        }

        return new TimeValidatorResponse(true, "The duration is not too short");
    }

    private function validateDateIsNotInTheWeekend() : TimeValidatorResponse
    {
        $invalidDates = [];
        if ($this->startDate->format('N') > 5) {
            $invalidDates[] = "start date {$this->startDate->format(self::DATEFORMAT)} is in the weekend";
        }

        if ($this->endDate->format('N') > 5) {
            $invalidDates[] = "end date {$this->endDate->format(self::DATEFORMAT)} is in the weekend";
        }

        if(!empty($invalidDates)) {
            return new TimeValidatorResponse(false, "Invalid dates: " . implode(', ', $invalidDates));
        }
        return new TimeValidatorResponse(true, "Dates are not in the weekend");
    }

    private function validateTimeIsBetweenWorkingHours() : TimeValidatorResponse{
        $invalidDates = [];
        $startHour = $this->startDate->format('H');
        $endHour = $this->endDate->format('H');
        $workingHoursMessage = " not between working hours (" . self::WORKING_HOURS_START . "-" . self::WORKING_HOURS_END . ") ";

        if ($startHour < self::WORKING_HOURS_START || $startHour > self::WORKING_HOURS_END) {
            $invalidDates[] = "start date {$this->startDate->format(self::DATEFORMAT)}";
        }

        if ($endHour < self::WORKING_HOURS_START || $endHour > self::WORKING_HOURS_END) {
            $invalidDates[] = "end date {$this->endDate->format(self::DATEFORMAT)}";
        }

        if(!empty($invalidDates)) {
            return new TimeValidatorResponse(false, "Invalid dates: " . $workingHoursMessage . "" . implode(', ', $invalidDates));
        }
        return new TimeValidatorResponse(true, "Dates are between working hours");
    }
}
