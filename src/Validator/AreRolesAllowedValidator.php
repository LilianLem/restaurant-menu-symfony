<?php

namespace App\Validator;

use LogicException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AreRolesAllowedValidator extends ConstraintValidator
{
    /** @var string[] */
    private const array FORBIDDEN_ROLES = ["ROLE_ADMIN", "ROLE_SUPER_ADMIN"];

    public function __construct(private Security $security)
    {
    }

    public function validate($value, Constraint $constraint)
    {
        assert($constraint instanceof AreRolesAllowed);

        if (null === $value || [] === $value) {
            return;
        }

        if(!is_array($value)) {
            throw new LogicException("AreRolesAllowedValidator should only be used on array properties");
        }

        $user = $this->security->getUser();
        if(!$user) {
            throw new LogicException("AreRolesAllowedValidator should only be used when a user is logged in");
        }

        // Given that roles property is read-only for non-admins, validator checks should have ended at the first condition check in this validator (because $value should be empty)
        if(!$this->security->isGranted("ROLE_ADMIN")) {
            throw new LogicException("AreRolesAllowedValidator should only be fully triggered when user is admin");
        }

        if($this->security->isGranted("ROLE_SUPER_ADMIN")) {
            return;
        }

        foreach(self::FORBIDDEN_ROLES as $forbiddenRole) {
            if(!in_array($forbiddenRole, $value)) {
                continue;
            }

            $this->context->buildViolation($constraint->message)
                ->setInvalidValue($forbiddenRole)
                ->addViolation()
            ;
        }
    }
}
