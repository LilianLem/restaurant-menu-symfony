<?php

namespace App\Entity\Trait;

use ApiPlatform\Metadata\ApiProperty;
use App\Security\ApiSecurityExpressionDirectory;
use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait SoftDeleteableEntityTrait
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    ##[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_ONLY)] // Useless to display it because soft-deleted entities are not exposed
    protected ?Carbon $deletedAt = null;

    /** Get the deletedAt timestamp value. Will return null if the entity has not been soft deleted */
    public function getDeletedAt(): ?Carbon
    {
        return $this->deletedAt;
    }

    /** Set or clear the deletedAt timestamp */
    public function setDeletedAt(?Carbon $deletedAt = null): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /** Check if the entity has been soft deleted */
    public function isDeleted(): bool
    {
        return $this->deletedAt !== null;
    }
}