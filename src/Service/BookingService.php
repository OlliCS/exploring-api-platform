<?php

namespace App\Service;

use DateTime;
use Exception;
use App\Entity\Room;
use App\Entity\Booking;
use App\Validator\TimeValidator;
use App\Response\BookingResponse;
use App\Validator\TimeSlotValidator;
use App\Response\TimeValidatorResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Response\RoomAvailabilityResponse;
use App\Response\TimeSlotValidatorResponse;
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
        try {
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
        $timeSlotValidResponse = $this->validateTimeSlot($startDate, $endDate);
        if (!$timeSlotValidResponse->isSuccess()) {
            return new RoomAvailabilityResponse(false, $timeSlotValidResponse->getMessage());
        }

        //check if room has no bookings
        $roomAvailabilityResponse = $this->validateRoomAvailability($room, $startDate, $endDate);
        if (!$roomAvailabilityResponse->isSuccess()) {
            return new RoomAvailabilityResponse(false, $roomAvailabilityResponse->getMessage());
        }

        return new RoomAvailabilityResponse(true, 'The room is available.');
    }

    private function validateRoomAvailability(Room $room, DateTime $startDate, DateTime $endDate): RoomAvailabilityResponse
    {
        try {
            $bookings = $this->entityManager->getRepository(Booking::class)->findBy(['room' => $room]);
            if ($bookings === null) {
                return new RoomAvailabilityResponse(true, 'The room has no bookings so the room is available.');
            }

            $roomAvailabilityValidator = new RoomAvailabilityValidator($startDate, $endDate, $bookings);
            return $roomAvailabilityValidator->validate();
        } 
        catch (Exception $e) {
            return new RoomAvailabilityResponse(false, $e->getMessage());
        }
    }

    private function validateTimeSlot($startDate, $endDate): TimeValidatorResponse
    {
        try{
            $timeSlotValidator = new TimeValidator($startDate, $endDate);
            return $timeSlotValidator->validate();
        }
        catch(Exception $e){
            return new TimeValidatorResponse(false, $e->getMessage());
        }
    }
}