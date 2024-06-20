<?php

namespace App\Entity\Interface;

use Doctrine\Common\Collections\Collection;

interface RankedEntityInterface extends OwnedEntityInterface
{
    /** @return Collection<int, RankingEntityInterface>|RankingEntityInterface|null */
    public function getRankingEntities(): Collection|RankingEntityInterface|null;
}