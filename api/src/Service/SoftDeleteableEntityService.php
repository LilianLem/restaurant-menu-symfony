<?php

namespace App\Service;

use App\Entity\Interface\DirectSoftDeleteableEntityInterface;
use App\Entity\Interface\JoinEntityInterface;
use App\Entity\Interface\RankedEntityInterface;
use App\Entity\Interface\RankingEntityInterface;
use App\Entity\Interface\SoftDeleteableEntityInterface;
use App\Entity\Trait\TimestampableEntityTrait;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use LogicException;

class SoftDeleteableEntityService
{
    public function softDelete(SoftDeleteableEntityInterface $entity, bool $firstCall = false): SoftDeleteableEntityInterface
    {
        if($entity->isDeleted()) {
            return $entity;
        }

        $entity->setDeletedAt(new Carbon());

        $parents = $entity->getParents();
        if($firstCall) {
            if($parents instanceof JoinEntityInterface) {
                $parents->setDeletedAt(new Carbon());
            } elseif(
                $parents instanceof Collection &&
                $parents->count() &&
                $parents->first() instanceof JoinEntityInterface
            ) {
                /** @var Collection<int, JoinEntityInterface> $parents */
                foreach($parents as $parent) {
                    $parent->setDeletedAt(new Carbon());
                }
            }
        }

        $children = $entity->getChildren();
        if(!$children) {
            return $entity;
        }

        if($children instanceof SoftDeleteableEntityInterface) {
            $children = new ArrayCollection([$children]);
        }

        if(!$children instanceof Collection) {
            throw new LogicException("An error occurred while processing deletion of an object! This should not happen. Please contact the developer.");
        }

        /** @var Collection<int, SoftDeleteableEntityInterface> $children */
        foreach($children as $child) {
            if(!$child instanceof RankedEntityInterface) {
                $this->softDelete($child);
                continue;
            }

            $rankingEntities = $child->getRankingEntities();

            if(!$rankingEntities) {
                continue;
            }

            if($rankingEntities instanceof RankingEntityInterface) {
                $this->softDelete($child);
                continue;
            }

            foreach($child->getRankingEntities() as $rankingEntity) {
                if(!$rankingEntity->isDeleted()) {
                    continue 2;
                }
            }

            $this->softDelete($child);
        }

        if($firstCall && $entity instanceof DirectSoftDeleteableEntityInterface) {
            // Resets value to let internal Gedmo\SoftDeleteable functions handle it correctly
            $entity->setDeletedAt();

            // Manually updates Timestampable field because it's not triggered otherwise
            if(in_array(TimestampableEntityTrait::class, class_uses($entity))) {
                $entity->setUpdatedAt(new Carbon());
            }
        }

        return $entity;
    }
}