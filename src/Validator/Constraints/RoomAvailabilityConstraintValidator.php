<?php

namespace App\Validator\Constraints;

use App\Service\BookingService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class RoomAvailabilityConstraintValidator extends ConstraintValidator
{
    private $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function validate($protocol, Constraint $constraint)
    {
        $startDate = $protocol->getStartDate();
        $endDate = $protocol->getEndDate();
        $room = $protocol->getRoom();

        $roomAvailabiltity = $this->bookingService->checkRoomAvailability($room, $startDate, $endDate);

        if (!$roomAvailabiltity->isSuccess()) {
            $this->context->buildViolation($constraint->responseMessage)
                ->setParameter('{{ responseMessage }}', $roomAvailabiltity->getMessage())
                ->addViolation();
        }
    }
}
