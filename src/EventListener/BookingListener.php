<?php

namespace App\EventListener;

use Exception;
use App\Entity\Booking;
use App\Service\BookingService;
use Doctrine\ORM\Event\LifecycleEventArgs;

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

            $bookingResponse = $this->bookingService->createBooking(
                $entity->getRoom(),
                $entity->getStartDate(),
                $entity->getEndDate()
            );

            if (!$bookingResponse->isSuccess()) {
                // Handle the error, e.g., throw an exception
                throw new Exception('The booking is not valid: ' . $bookingResponse->getMessage() . '.');
            }
        }
    }
}
