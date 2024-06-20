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
class CanRankingEntityBeDeleted extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $message = "Impossible de supprimer cette entité : veuillez supprimer l'entité parente";

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
