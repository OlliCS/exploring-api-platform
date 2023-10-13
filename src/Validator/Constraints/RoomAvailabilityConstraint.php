<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class RoomAvailabilityConstraint extends Constraint
{
    public $message = 'The room is not available from {{ startDate }} to {{ endDate }}.';
}