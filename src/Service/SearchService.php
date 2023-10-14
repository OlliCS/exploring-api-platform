<?php

namespace App\Service;
use DateTime;
use DatePeriod;
use DateInterval;
use App\Entity\Room;
use App\Entity\Booking;
use Doctrine\ORM\EntityManagerInterface;

class SearchService{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }

    public function findAvailableTimeSlotsForDateAndCapacity($date, $people) : array
    {
        $rooms = $this->getRoomsWithEnoughCapacity($people);
        $bookings = $this->getExistingBookings($date, $rooms);
        
        return $this->calculateAvailableTimeSlots($rooms,$bookings,$date);
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
        $freeTimeSlots = [];
        foreach($rooms as $room){
            $freeTimeSlots[$room->getName()] = $this->searchFreeTimeSlotsOfRoom($room, $bookings, $date);
        }

        return $freeTimeSlots;
    }



    private function searchFreeTimeSlotsOfRoom($room, $bookings, $bookingDate)
    {
        $period = $this->createPeriod($bookingDate);
        $freeTimeSlots = [];
        foreach($period as $date){
            $isFree = true;
            foreach($bookings as $booking){
                if($booking->getRoom() == $room){
                    if($date >= $booking->getStartDate() && $date <= $booking->getEndDate()){
                        $isFree = false;
                    }
                }
            }
            if($isFree){
                array_push($freeTimeSlots, $date->format('H:i'));
            }
        }
        return $freeTimeSlots;
    }

    private function createPeriod($bookingDate) :DatePeriod
    {
        $startDate = (new DateTime($bookingDate))->setTime(8, 0, 0);
        $endDate = (new DateTime($bookingDate))->setTime(20, 0, 0);
        $interval = new DateInterval('PT30M');
        return new DatePeriod($startDate, $interval, $endDate);
        
    }
}