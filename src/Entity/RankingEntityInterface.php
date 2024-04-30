<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

interface RankingEntityInterface
{
    public function getRank(): ?int;

    public function setRank(int $rank): static;

    /** @return Collection<static> */
    public function getSiblings(): Collection;

    public function getRankedEntity(): ?RankedEntityInterface;

    public function getMaxParentCollectionRank(): ?int;
}