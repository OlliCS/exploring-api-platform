<?php

namespace App\Service;
use DateTime;
use App\Entity\Room;
use App\Entity\Booking;
use Doctrine\ORM\EntityManagerInterface;

class BookingService 
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createBooking(Room $room, DateTime $startDate, DateTime $endDate): Booking
    {
        



        $booking = new Booking();
        $booking->setRoom($room);
        $booking->setStartDate($startDate);
        $booking->setEndDate($endDate);

        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        return $booking;
    }

    public function isRoomAvailable(Room $room, DateTime $startDate, DateTime $endDate): bool
    {
        $bookings = $this->entityManager->getRepository(Booking::class)->findBy(['room' => $room]);

        foreach ($bookings as $booking) {
            if ($startDate >= $booking->getStartDate() && $startDate <= $booking->getEndDate()) {
                return false;
            }
            if ($endDate >= $booking->getStartDate() && $endDate <= $booking->getEndDate()) {
                return false;
            }
        }

        return true;
    }

    public function isTimeSlotValid($startDate, $endDate): bool
    {
        return $endDate > $startDate;
    }
}


