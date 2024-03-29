<?php

namespace App\Validator;

use App\Entity\Restaurant;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsRestaurantNameUniqueValidator extends ConstraintValidator
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $em
    )
    {
    }

    public function validate($value, Constraint $constraint)
    {
        assert($constraint instanceof IsRestaurantNameUnique);

        if (null === $value || '' === $value) {
            return;
        }

        if(!$value instanceof Restaurant) {
            throw new LogicException("IsRestaurantNameUniqueValidator should only be used on Restaurant entity");
        }

        $user = $this->security->getUser();
        if(!$user) {
            throw new LogicException("IsRestaurantNameUniqueValidator should only be used when a user is logged in");
        }

        // If owner is already set at this moment, UniqueEntity constraint wouldn't have passed
        if($value->getOwner()) {
            return;
        }

        if(!$this->em->getRepository(Restaurant::class)->findOneBy([
            "owner" => $user,
            "name" => $value->getName()
        ])) {
            return;
        }

        $this->context->buildViolation($constraint->message)
            ->atPath("name")
            ->setInvalidValue($value->getName())
            ->setCode(UniqueEntity::NOT_UNIQUE_ERROR)
            ->addViolation()
        ;
    }
}
