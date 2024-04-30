<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

interface RankedEntityInterface
{
    /** @return Collection<int, RankingEntityInterface>|RankingEntityInterface|null */
    public function getRankingEntities(): Collection|RankingEntityInterface|null;
}