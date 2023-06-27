<?php

namespace App\Entity;

use App\Repository\MenuSectionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MenuSectionRepository::class)]
#[ORM\UniqueConstraint("menu_section_unique", columns: ["menu_id", "section_id"])]
#[ORM\UniqueConstraint("menu_section_rank_unique", columns: ["menu_id", "rank"])]
#[UniqueEntity(
    fields: ["menu", "section"],
    errorPath: "section",
    message: "Cette section est déjà reliée au menu",
)]
#[UniqueEntity(
    fields: ["menu", "rank"],
    errorPath: "rank",
    message: "Ce rang de section est déjà assigné sur ce menu",
)]
class MenuSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(options: ["unsigned" => true])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'menuSections', fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Menu $menu = null;

    #[ORM\OneToOne(inversedBy: 'sectionMenu', cascade: ['persist', 'remove'], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?Section $section = null;

    #[ORM\Column(options: ["unsigned" => true])]
    #[Assert\Positive(message: "Le rang doit être positif")]
    #[Assert\NotBlank]
    private ?int $rank = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): static
    {
        $this->menu = $menu;

        return $this;
    }

    public function getSection(): ?Section
    {
        return $this->section;
    }

    public function setSection(Section $section): static
    {
        $this->section = $section;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(int $rank): static
    {
        $this->rank = $rank;

        return $this;
    }
}
