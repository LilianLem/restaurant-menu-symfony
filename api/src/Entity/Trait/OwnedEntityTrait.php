<?php

namespace App\Entity\Trait;

use App\Entity\Interface\OwnedEntityInterface;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use LogicException;

trait OwnedEntityTrait
{
    public function getOwner(): ?User
    {
        /** @var OwnedEntityInterface $this */

        $parents = $this->getParents();

        if($parents === null || $parents instanceof User) {
            return $parents;
        }

        if($parents instanceof OwnedEntityInterface) {
            return $parents->getOwner();
        }

        if($parents instanceof Collection) {
            if(!$parents->count()) {
                return null;
            }

            $firstParent = $parents->first();

            if($firstParent instanceof OwnedEntityInterface) {
                return $firstParent->getOwner();
            }
        }

        throw new LogicException("Entity owner not found! This should not happen. Please contact the developer.");
    }
}