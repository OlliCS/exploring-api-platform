<?php

namespace App\Validator;

use DateTime;

class TimeSlotValidator{
    private DateTime $startDate;
    private DateTime $endDate;

    public function __construct(DateTime $startDate, DateTime $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function validate() {
        
    }
    
}