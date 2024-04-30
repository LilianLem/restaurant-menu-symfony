<?php

namespace App\Service;

use App\Entity\RankedEntityInterface;
use App\Entity\RankingEntityInterface;
use Doctrine\Common\Collections\Collection;

class RankedEntityService
{
    public function __construct(private RankingEntityService $rankingEntityService)
    {
    }

    public function rearrangeRanksOnDeletion(RankedEntityInterface $entityToDelete): void
    {
        $rankingEntities = $entityToDelete->getRankingEntities();
        if(!$rankingEntities) {
            return;
        }

        if($rankingEntities instanceof RankingEntityInterface) {
            $rankingEntities = [$rankingEntities];
        }

        /**
         * @var RankingEntityInterface[]|Collection<RankingEntityInterface> $rankingEntities
         * @var RankingEntityInterface $entity
         */
        foreach($rankingEntities as $entity) {
            $this->rankingEntityService->rearrangeRanksOnDeletion($entity);
        }
    }
}