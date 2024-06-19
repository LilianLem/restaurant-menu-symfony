<?php

namespace App\Entity;

interface OwnedEntityInterface extends SoftDeleteableEntityInterface
{
    public function getOwner(): ?User;
}