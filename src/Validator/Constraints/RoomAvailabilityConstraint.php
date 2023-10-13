<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class RoomAvailabilityConstraint extends Constraint
{
    public $responseMessage = '{{ responseMessage }}';
}