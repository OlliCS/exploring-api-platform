<?php

namespace App\Validator\Constraints;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
class RoomAvailabilityConstraint extends Constraint
{
    public $responseMessage = '{{ responseMessage }}';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}