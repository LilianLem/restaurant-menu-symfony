<?php

namespace App\Entity\Trait;

use ApiPlatform\Metadata\ApiProperty;
use App\Entity\Interface\RankedEntityInterface;
use App\Entity\Interface\RankingEntityInterface;
use App\Entity\Interface\SoftDeleteableEntityInterface;
use App\Security\ApiSecurityExpressionDirectory;
use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait SoftDeleteableEntityTrait
{
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[ApiProperty(security: ApiSecurityExpressionDirectory::ADMIN_ONLY)]
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

    public function softDelete(bool $handleParents = false): self
    {
        if($this->isDeleted()) {
            return $this;
        }

        $this->setDeletedAt(new Carbon());

        $parents = $this->getParents();
        if(
            $handleParents &&
            $parents instanceof Collection &&
            $parents->count() &&
            $parents->first() instanceof RankingEntityInterface
        ) {
            /** @var Collection<int, RankingEntityInterface> $parents */
            foreach($parents as $parent) {
                $parent->setDeletedAt(new Carbon());
            }
        }

        $children = $this->getChildren();
        if(!$children) {
            return $this;
        }

        if($children instanceof SoftDeleteableEntityInterface) {
            $children = new ArrayCollection([$children]);
        }

        /** @var Collection<int, SoftDeleteableEntityInterface> $children */
        foreach($children as $child) {
            if(!$child instanceof RankedEntityInterface) {
                $child->softDelete();
                continue;
            }

            $rankingEntities = $child->getRankingEntities();

            if(!$rankingEntities) {
                continue;
            }

            if($rankingEntities instanceof RankingEntityInterface) {
                $child->softDelete();
                continue;
            }

            foreach($child->getRankingEntities() as $rankingEntity) {
                if(!$rankingEntity->isDeleted()) {
                    continue 2;
                }
            }

            $child->softDelete();
        }

        return $this;
    }
}