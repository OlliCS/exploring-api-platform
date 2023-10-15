<?php

namespace App\Entity;

class TimeSlot
{
    private $startDate;
    private $endDate;
    private $room;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }


    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }


}