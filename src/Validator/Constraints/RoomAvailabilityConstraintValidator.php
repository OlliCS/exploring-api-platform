<?php

namespace App\Validation\Constraints;

use App\Repository\BookingRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class RoomAvailabilityConstraintValidator extends ConstraintValidator
{
    private $bookingRepository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    public function validate($protocol, Constraint $constraint)
    {
        $startDate = $protocol->getStartDate();
        $endDate = $protocol->getEndDate();
        $room = $protocol->getRoom();

        // Check room availability logic
        if (!$this->bookingRepository->isRoomAvailable($room, $startDate, $endDate)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ startDate }}', $startDate->format('Y-m-d'))
                ->setParameter('{{ endDate }}', $endDate->format('Y-m-d'))
                ->addViolation();
        }
    }
}
