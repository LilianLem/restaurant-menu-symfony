<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

/**
 * Only use this constraint on Restaurant entity
 * @Annotation
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
class IsRestaurantNameUnique extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $message = "Vous possédez déjà un restaurant avec ce nom";

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
