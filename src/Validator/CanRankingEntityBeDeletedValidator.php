<?php

namespace App\Validator;

use App\Entity\Interface\RankingEntityInterface;
use Doctrine\Common\Collections\Collection;
use LogicException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CanRankingEntityBeDeletedValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        assert($constraint instanceof CanRankingEntityBeDeleted);

        if (null === $value || '' === $value) {
            return;
        }

        if(!$value instanceof RankingEntityInterface) {
            throw new LogicException("CanRankingEntityBeDeletedValidator should only be used on an entity implementing RankingEntityInterface");
        }

        $childOtherRankingEntities = $value->getRankedEntity()->getRankingEntities();
        if($childOtherRankingEntities instanceof Collection && $childOtherRankingEntities->count() > 1) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->atPath("id")
            ->addViolation()
        ;
    }
}
