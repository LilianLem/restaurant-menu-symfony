<?php

namespace App\Entity\Interface;

use App\Entity\User;
use Carbon\Carbon;
use Doctrine\Common\Collections\Collection;

interface SoftDeleteableEntityInterface
{
    public function getDeletedAt(): ?Carbon;

    /** Set or clear the deletedAt timestamp */
    public function setDeletedAt(?Carbon $deletedAt = null): static;

    /** Check if the entity has been soft deleted */
    public function isDeleted(): bool;

    public function softDelete(bool $handleParents = false): self;

    /** @return Collection<int, self>|self|null */
    public function getChildren(): Collection|self|null;

    /** @return Collection<int, self>|User|self|null */
    public function getParents(): Collection|User|self|null;
}