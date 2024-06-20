<?php

namespace App\Entity\Interface;

use App\Entity\User;

interface OwnedEntityInterface extends SoftDeleteableEntityInterface
{
    public function getOwner(): ?User;
}