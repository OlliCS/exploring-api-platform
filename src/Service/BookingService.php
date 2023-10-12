<?php

namespace App\Service;
use DateTime;
use Exception;
use App\Entity\Room;
use App\Entity\Booking;
use App\Response\BookingResponse;
use App\Validator\TimeSlotValidator;
use Doctrine\ORM\EntityManagerInterface;
use App\Response\RoomAvailabilityResponse;
use App\Validator\RoomAvailabilityValidator;

class BookingService 
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function createBooking(Room $room, DateTime $startDate, DateTime $endDate): BookingResponse
    {
        try{
            $roomIsAvailable = $this->checkRoomAvailability($room, $startDate, $endDate);

            if (!$roomIsAvailable->isSuccess()) {
                return new BookingResponse(null, false, $roomIsAvailable->getMessage());
            }

            $booking = new Booking($startDate, $endDate, $room);
            $this->entityManager->persist($booking);
            $this->entityManager->flush();
    
            return new BookingResponse($booking, true, 'The booking has been created.');
            
        }
        catch (Exception $e) {
            return new BookingResponse(null, false, $e->getMessage());
        }
    }

    private function checkRoomAvailability(Room $room, DateTime $startDate, DateTime $endDate): RoomAvailabilityResponse
    {
        //validate the time slot is valid
        $timeSlotValidator = new TimeSlotValidator($startDate, $endDate);
        $timeSlotValidResponse = $timeSlotValidator->validate();

        if (!$timeSlotValidResponse->isSuccess()) {
            return new RoomAvailabilityResponse(false, $timeSlotValidResponse->getMessage());
        }

        //check if the room is available
        $bookings = $this->entityManager->getRepository(Booking::class)->findBy(['room' => $room]);
        if($bookings === null) {
            return new RoomAvailabilityResponse(true, 'The room has no bookings so the room is available.');
        }

        $roomAvailabilityValidator = new RoomAvailabilityValidator($startDate, $endDate, $bookings);
        $roomAvailabilityValidResponse = $roomAvailabilityValidator->validate();

        if (!$roomAvailabilityValidResponse->isSuccess()) {
            return new RoomAvailabilityResponse(false, $roomAvailabilityValidResponse->getMessage());
        }

        return new RoomAvailabilityResponse(true, 'The room is available.');
    }
}


