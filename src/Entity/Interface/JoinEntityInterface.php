<?php

namespace App\Entity\Interface;

interface JoinEntityInterface extends IndirectSoftDeleteableEntityInterface
{
    public function getChildren(): ?SoftDeleteableEntityInterface;

    public function getParents(): ?SoftDeleteableEntityInterface;
}