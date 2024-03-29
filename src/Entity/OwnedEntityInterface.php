<?php

namespace App\Entity;

interface OwnedEntityInterface
{
    public function getOwner(): ?User;
}