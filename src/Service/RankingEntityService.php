<?php

namespace App\Service;

use App\Entity\RankingEntityInterface;

class RankingEntityService
{
    public function __construct()
    {
    }

    public function changeRanks(RankingEntityInterface $rankChangingEntity, int $originalRank): void
    {
        /** @var int $newRank */
        $newRank = $rankChangingEntity->getRank();
        $siblingEntities = $rankChangingEntity->getSiblings();

        if($newRank > $originalRank) {
            $inBetweenEntities = $siblingEntities->filter(fn(RankingEntityInterface $entity) => $entity->getRank() <= $newRank && $entity->getRank() > $originalRank);

            /** @var RankingEntityInterface|null $entityOriginallyAtTargetedRank */
            $entityOriginallyAtTargetedRank = $siblingEntities->findFirst(fn(int $key, RankingEntityInterface $entity) => $entity->getRank() === $newRank);

            // Decrease ranks for entities directly following original rank
            $currentRank = $originalRank;
            /** @var RankingEntityInterface $entity */
            foreach($inBetweenEntities as $entity) {
                if($entity->getRank() > ++$currentRank) {
                    break;
                }

                $entity->setRank($currentRank - 1);
            }

            if(!$entityOriginallyAtTargetedRank) {
                return;
            }

            // Increase ranks for entities at targeted rank and directly following new rank

            $newFollowingEntities = $siblingEntities->filter(fn(RankingEntityInterface $entity) => $entity->getRank() >= $newRank);

            $currentRank = $newRank;
            /** @var RankingEntityInterface $entity */
            foreach($newFollowingEntities as $entity) {
                if($entity->getRank() > $currentRank++) {
                    break;
                }

                $entity->setRank($currentRank);
            }
        } else {
            // Increase ranks for entities at targeted rank and directly following new rank

            $inBetweenEntities = $siblingEntities->filter(fn(RankingEntityInterface $entity) => $entity->getRank() >= $newRank && $entity->getRank() < $originalRank);

            $currentRank = $newRank;
            /** @var RankingEntityInterface $entity */
            foreach($inBetweenEntities as $entity) {
                if($entity->getRank() > $currentRank++) {
                    break;
                }

                $entity->setRank($currentRank);
            }

            // Decrease ranks for entities directly following original rank

            /** @var RankingEntityInterface|null $newEntityAtOriginalRank */
            $newEntityAtOriginalRank = $siblingEntities->findFirst(fn(int $key, RankingEntityInterface $entity) => $entity->getRank() === $originalRank);

            if($newEntityAtOriginalRank) {
                return;
            }

            $oldFollowingEntities = $siblingEntities->filter(fn(RankingEntityInterface $entity) => $entity->getRank() > $originalRank);

            $currentRank = $originalRank;
            /** @var RankingEntityInterface $entity */
            foreach($oldFollowingEntities as $entity) {
                if($entity->getRank() > ++$currentRank) {
                    break;
                }

                $entity->setRank($entity->getRank() - 1);
            }
        }
    }

    public function rearrangeRanksOnCreation(RankingEntityInterface $newEntity): void
    {
        $rank = $newEntity->getRank();
        $followingEntities = $newEntity->getSiblings()->filter(fn(RankingEntityInterface $entity) => $entity->getRank() >= $rank);

        if(!$followingEntities) {
            return;
        }

        /** @var RankingEntityInterface $entity */
        foreach($followingEntities as $entity) {
            if($entity->getRank() > $rank) {
                return;
            }

            $entity->setRank(++$rank);
        }
    }

    public function rearrangeRanksOnDeletion(RankingEntityInterface $entityToDelete): void
    {
        $rank = $entityToDelete->getRank();
        $followingEntities = $entityToDelete->getSiblings()->filter(fn(RankingEntityInterface $entity) => $entity->getRank() > $rank);

        if(!$followingEntities) {
            return;
        }

        /** @var RankingEntityInterface $entity */
        foreach($followingEntities as $entity) {
            if($entity->getRank() > ++$rank) {
                return;
            }

            $entity->setRank($rank - 1);
        }
    }
}