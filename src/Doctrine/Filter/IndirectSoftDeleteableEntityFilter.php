<?php

namespace App\Doctrine\Filter;

use App\Entity\IndirectSoftDeleteableEntityInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/** Useful for classes implementing IndirectSoftDeleteableEntityInterface, which are not supposed to have Gedmo\SoftDeleteable attribute, because they can only be soft-deleted following a soft-delete of another entity having this attribute (see SoftDeleteableEntityTrait::softDelete() ) */
class IndirectSoftDeleteableEntityFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if(!is_subclass_of($targetEntity->getName(), IndirectSoftDeleteableEntityInterface::class)) {
            return "";
        }

        return "$targetTableAlias.deleted_at IS NULL";
    }
}