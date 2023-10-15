<?php

namespace App\Entity;

class TimeSlot
{
    private $startDate;
    private $endDate;
    private $room;

    public function __construct($startDate, $endDate, $room)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->room = $room;
    }

    public function getStartDate()
    {
        return $this->startDate;
    }

    public function getEndDate()
    {
        return $this->endDate;
    }
    public function getRoom()
    {
        return $this->room;
    }
}