<?php

namespace App\Service;
use DateTime;
use Exception;
use DateInterval;
use App\Entity\Room;
use App\Entity\Booking;
use App\Response\BookingResponse;
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
            $timeSlotValidator = $this->isRoomAvailable($room, $startDate, $endDate);

            if (!$timeSlotValidator->isSuccess()) {
                return new BookingResponse(null, false, $timeSlotValidator->getMessage());
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



    public function isRoomAvailable(Room $room, DateTime $startDate, DateTime $endDate): RoomAvailabilityResponse
    {

        $maxBookingDurationInSeconds = 12 * 60 * 60; // 12 hours
        $now = new DateTime();

        if ($startDate < $now || $endDate < $now) {
            $invalidDates = [];
        
            if($startDate < $now) {
                $invalidDates[] = "start date {$startDate->format('Y-m-d H:i:s')}";
            }
            if($endDate < $now) {
                $invalidDates[] = "end date {$endDate->format('Y-m-d H:i:s')}";
            }
        
            $invalidDatesString = implode(' and ', $invalidDates);
            $errorMessage = "Invalid time slot. The {$invalidDatesString} must be in the future.";
        
            return new RoomAvailabilityResponse(false, $errorMessage);
        }
        $bookingDurationInSeconds = $endDate->getTimestamp() - $startDate->getTimestamp();
            if ($bookingDurationInSeconds > $maxBookingDurationInSeconds) {
                return new RoomAvailabilityResponse(false, 
                    "Invalid booking duration. You cannot book the room for more than 12 hours."
                );
            }
        if (!$this->isStartDateBeforeEndDate($startDate, $endDate)) {
            return new RoomAvailabilityResponse(false, 'The time slot is not valid: start date must be before end date.');
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

    public function isStartDateBeforeEndDate($startDate, $endDate): bool
    {
        return $endDate > $startDate;
    }












}


