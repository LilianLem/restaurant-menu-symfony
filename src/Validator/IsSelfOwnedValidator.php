<?php

namespace App\Validator;

use App\Entity\OwnedEntityInterface;
use LogicException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsSelfOwnedValidator extends ConstraintValidator
{
    public function __construct(private Security $security)
    {
    }

    public function validate($value, Constraint $constraint)
    {
        assert($constraint instanceof IsSelfOwned);

        if (null === $value || '' === $value) {
            return;
        }

        assert($value instanceof OwnedEntityInterface);

        $user = $this->security->getUser();
        if(!$user) {
            throw new LogicException("IsSelfOwnedValidator should only be used when a user is logged in");
        }

        if($this->security->isGranted("ROLE_ADMIN")) {
            return;
        }

        if($value->getOwner() === $user) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->addViolation();
    }
}
