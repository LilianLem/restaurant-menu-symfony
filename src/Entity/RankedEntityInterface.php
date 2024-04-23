<?php

namespace App\Entity;

use Doctrine\Common\Collections\Collection;

interface RankedEntityInterface
{
    public function getRank(): ?int;

    public function setRank(int $rank): static;

    /** @return Collection<static> */
    public function getSiblings(): Collection;

    public function getMaxParentCollectionRank(): ?int;
}