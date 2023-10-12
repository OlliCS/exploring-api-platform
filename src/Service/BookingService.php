<?php

namespace App\Service;
use DateTime;
use Exception;
use DateInterval;
use App\Entity\Room;
use App\Entity\Booking;
use App\Response\BookingResponse;
use App\Validator\TimeSlotValidator;
use Doctrine\ORM\EntityManagerInterface;
use App\Response\RoomAvailabilityResponse;
use App\Response\TimeSlotValidatorResponse;

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



    public function checkRoomAvailability(Room $room, DateTime $startDate, DateTime $endDate): RoomAvailabilityResponse
    {
        //validate the time slot
        $timeSlotValidator = new TimeSlotValidator($startDate, $endDate);
        $timeSlotValidResponse = $timeSlotValidator->validate();

        if (!$timeSlotValidResponse->isSuccess()) {
            return new RoomAvailabilityResponse(false, $timeSlotValidResponse->getMessage());
        }

        $bookings = $this->entityManager->getRepository(Booking::class)->findBy(['room' => $room]);

        foreach ($bookings as $booking) {
            $existingStart = $booking->getStartDate()->format('Y-m-d H:i:s');
            $existingEnd = $booking->getEndDate()->format('Y-m-d H:i:s');
            $desiredStart = $startDate->format('Y-m-d H:i:s');
            $desiredEnd = $endDate->format('Y-m-d H:i:s');
        
            if ($startDate >= $booking->getStartDate() && $startDate <= $booking->getEndDate()) {
                return new RoomAvailabilityResponse(false, 
                    "Conflict with an existing booking ($existingStart to $existingEnd). " .
                    "The desired start date ($desiredStart) is within this range."
                );
            }
            if ($endDate >= $booking->getStartDate() && $endDate <= $booking->getEndDate()) {
                return new RoomAvailabilityResponse(false, 
                    "Conflict with an existing booking ($existingStart to $existingEnd). " .
                    "The desired end date ($desiredEnd) is within this range."
                );
            }
            if ($startDate <= $booking->getStartDate() && $endDate >= $booking->getEndDate()) {
                return new RoomAvailabilityResponse(false, 
                    "Conflict with an existing booking ($existingStart to $existingEnd). " .
                    "The desired time slot ($desiredStart to $desiredEnd) envelops this booking."
                );
            }
        }
        return new RoomAvailabilityResponse(true, 'The room is available.');
    }













}


