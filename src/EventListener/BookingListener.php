<?php

namespace App\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Entity\Booking;
use App\Service\BookingService;

class BookingListener
{
    private $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        if ($entity instanceof Booking) {

            $timeSlotValid = $this->bookingService->isRoomAvailable(
                $entity->getRoom(),
                $entity->getStartDate(),
                $entity->getEndDate()
            );

            if (!$timeSlotValid->isSuccess()) {
                // Handle the error, e.g., throw an exception
                throw new \Exception('The booking is not valid: ' . $timeSlotValid->getMessage() . '.');
            }
        }
    }
}
