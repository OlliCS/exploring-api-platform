<?php

namespace App\Validation\Constraints;

use App\Repository\BookingRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class RoomValidator extends ConstraintValidator
{
    private $bookingRepository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    public function validate($protocol, Constraint $constraint)
    {
        if (!$constraint instanceof RoomAvailability) {
            throw new UnexpectedTypeException($constraint, RoomAvailability::class);
        }

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
