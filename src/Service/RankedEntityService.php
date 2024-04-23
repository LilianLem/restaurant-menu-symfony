<?php

namespace App\Service;

use App\Entity\RankedEntityInterface;

class RankedEntityService
{
    public function __construct()
    {
    }

    /** TODO: test algorithm (not working currently - multiple final ranks are identical) */
    public function changeRanks(RankedEntityInterface $rankChangingEntity, int $originalRank): void
    {
        /** @var int $newRank */
        $newRank = $rankChangingEntity->getRank();
        $siblingEntities = $rankChangingEntity->getSiblings();

        $rankChangingEntity->setRank($newRank);

        if($newRank > $originalRank) {
            $inBetweenEntities = $siblingEntities->filter(fn(RankedEntityInterface $entity) => $entity->getRank() <= $newRank && $entity->getRank() > $originalRank);

            /** @var RankedEntityInterface|null $entityOriginallyAtTargetedRank */
            $entityOriginallyAtTargetedRank = $siblingEntities->findFirst(fn(int $key, RankedEntityInterface $entity) => $entity->getRank() === $newRank);

            // Decrease ranks for entities directly following original rank
            $currentRank = $originalRank;
            /** @var RankedEntityInterface $entity */
            foreach($inBetweenEntities as $entity) {
                if($entity->getRank() > ++$currentRank) {
                    break;
                }
            }

            if(!$entityOriginallyAtTargetedRank) {
                return;
            }

            // Increase ranks for entities at targeted rank and directly following new rank

            $newFollowingEntities = $siblingEntities->filter(fn(RankedEntityInterface $entity) => $entity->getRank() >= $newRank);

            $currentRank = $newRank;
            /** @var RankedEntityInterface $entity */
            foreach($newFollowingEntities as $entity) {
                if($entity->getRank() > $currentRank++) {
                    break;
                }

                $entity->setRank($currentRank);
            }
        } else {
            // Increase ranks for entities at targeted rank and directly following new rank

            $inBetweenEntities = $siblingEntities->filter(fn(RankedEntityInterface $entity) => $entity->getRank() >= $newRank && $entity->getRank() < $originalRank);

            $currentRank = $newRank;
            /** @var RankedEntityInterface $entity */
            foreach($inBetweenEntities as $entity) {
                if($entity->getRank() > $currentRank++) {
                    break;
                }

                $entity->setRank($currentRank);
            }

            // Decrease ranks for entities directly following original rank

            /** @var RankedEntityInterface|null $newEntityAtOriginalRank */
            $newEntityAtOriginalRank = $siblingEntities->findFirst(fn(int $key, RankedEntityInterface $entity) => $entity->getRank() === $newRank);

            if($newEntityAtOriginalRank) {
                return;
            }

            $oldFollowingEntities = $siblingEntities->filter(fn(RankedEntityInterface $entity) => $entity->getRank() > $originalRank);

            $currentRank = $originalRank;
            /** @var RankedEntityInterface $entity */
            foreach($oldFollowingEntities as $entity) {
                if($entity->getRank() > ++$currentRank) {
                    break;
                }

                $entity->setRank($entity->getRank() - 1);
            }
        }
    }
}