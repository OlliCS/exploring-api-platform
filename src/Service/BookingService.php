<?php

namespace App\Service;
use DateTime;
use Exception;
use App\Entity\Room;
use App\Entity\Booking;
use App\Response\BookingResponse;
use Doctrine\ORM\EntityManagerInterface;
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

            $booking = new Booking();
            $booking->setRoom($room);
            $booking->setStartDate($startDate);
            $booking->setEndDate($endDate);
    
            $this->entityManager->persist($booking);
            $this->entityManager->flush();
    
            return new BookingResponse($booking, true, 'The booking has been created.');
            
        }
        catch (Exception $e) {
            return new BookingResponse(null, false, $e->getMessage());
        }
    }

    public function isRoomAvailable(Room $room, DateTime $startDate, DateTime $endDate): TimeSlotValidatorResponse
    {
        if (!$this->isStartDateBeforeEndDate($startDate, $endDate)) {
            return new TimeSlotValidatorResponse(false, 'The time slot is not valid: start date must be before end date.');
        }
        
        $bookings = $this->entityManager->getRepository(Booking::class)->findBy(['room' => $room]);

        foreach ($bookings as $booking) {
            $existingStart = $booking->getStartDate()->format('Y-m-d H:i:s');
            $existingEnd = $booking->getEndDate()->format('Y-m-d H:i:s');
            $desiredStart = $startDate->format('Y-m-d H:i:s');
            $desiredEnd = $endDate->format('Y-m-d H:i:s');
        
            if ($startDate >= $booking->getStartDate() && $startDate <= $booking->getEndDate()) {
                return new TimeSlotValidatorResponse(false, 
                    "Conflict with an existing booking ($existingStart to $existingEnd). " .
                    "The desired start date ($desiredStart) is within this range."
                );
            }
            if ($endDate >= $booking->getStartDate() && $endDate <= $booking->getEndDate()) {
                return new TimeSlotValidatorResponse(false, 
                    "Conflict with an existing booking ($existingStart to $existingEnd). " .
                    "The desired end date ($desiredEnd) is within this range."
                );
            }
            if ($startDate <= $booking->getStartDate() && $endDate >= $booking->getEndDate()) {
                return new TimeSlotValidatorResponse(false, 
                    "Conflict with an existing booking ($existingStart to $existingEnd). " .
                    "The desired time slot ($desiredStart to $desiredEnd) envelops this booking."
                );
            }
        }
        return new TimeSlotValidatorResponse(true, 'The room is available.');
    }

    public function isStartDateBeforeEndDate($startDate, $endDate): bool
    {
        return $endDate > $startDate;
    }
}


