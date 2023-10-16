<?php

namespace App\Service;
use DateTime;
use DatePeriod;
use DateInterval;
use App\Entity\Room;
use App\Entity\Booking;
use App\Entity\TimeSlot;
use Doctrine\ORM\EntityManagerInterface;

class SearchService
{
    const DURATION = 'PT30M'; // 30 minutes
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    public function findAvailableTimeSlotsForDateAndCapacity($date, $people) : array
    {
        $rooms = $this->getRoomsWithEnoughCapacity($people);
        $bookings = $this->getExistingBookings($date, $rooms);
        
        $timeslots = $this->calculateAvailableTimeSlots($rooms,$bookings,$date);
        return $timeslots;

       
    }

    private function getRoomsWithEnoughCapacity($people)
    {
        $roomRepository = $this->entityManager->getRepository(Room::class);
        $queryBuilder = $roomRepository->createQueryBuilder('r')
            ->andWhere('r.capacity >= :people')
            ->setParameter('people', $people);

        $rooms = $queryBuilder->getQuery()->getResult();
        return $rooms;
    }

    private function getExistingBookings($date, $rooms): array
    {
        $startDate = (new DateTime($date))->setTime(0, 0, 0);
        $endDate = (new DateTime($date))->setTime(23, 59, 59);

        $bookingRepository = $this->entityManager->getRepository(Booking::class);
        $queryBuilder = $bookingRepository->createQueryBuilder('b')
            ->where('b.startDate BETWEEN :startDate AND :endDate')
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate);

        if (!empty($rooms)) {
            $queryBuilder->andWhere('b.room IN (:rooms)')->setParameter('rooms', $rooms);
        }

        $bookings = $queryBuilder->getQuery()->getResult();

        return $bookings;
    }

    private function calculateAvailableTimeSlots($rooms,$bookings,$date) : array
    {
        $roomsWithFreeTimeSlots = [];
        $freeTimeSlots = [];
        foreach($rooms as $room){
            $freeTimeSlots['room'] = $room;
            $freeTimeSlots['slots'] = $this->searchFreeTimeSlotsOfRoom($room, $bookings, $date);

            $roomsWithFreeTimeSlots[] = $freeTimeSlots;

        }

        return $roomsWithFreeTimeSlots;
    }



    private function searchFreeTimeSlotsOfRoom($room, $bookings, $bookingDate)
    {
        $period = $this->createPeriod($bookingDate);
        $freeTimeSlots = [];
        foreach($period as $date){
            $isFree = true;
            $endDate = (clone $date)->add(new DateInterval(self::DURATION));
            foreach($bookings as $booking){
                if($booking->getRoom() == $room){
                    if($date >= $booking->getStartDate() && $date < $booking->getEndDate() || ($endDate > $booking->getStartDate() && $endDate <= $booking->getEndDate())){
                        $isFree = false;
                        break;
                    }
                }

            }
            if($isFree){
                $timeslot = new TimeSlot($date, (clone $date)->add(new DateInterval(self::DURATION)));
                array_push($freeTimeSlots, $timeslot);
            }
        }
        return $freeTimeSlots;
    }

    private function createPeriod($bookingDate) :DatePeriod
    {
        $startDate = (new DateTime($bookingDate))->setTime(8, 0, 0);
        $endDate = (new DateTime($bookingDate))->setTime(20, 0, 0);
        $interval = new DateInterval(self::DURATION);
        return new DatePeriod($startDate, $interval, $endDate);
        
    }
}