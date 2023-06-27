<?php

namespace App\Service;

use App\Entity\Menu;
use App\Entity\MenuSection;
use App\Entity\Section;
use Doctrine\ORM\EntityManagerInterface;

class MenuService
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function addSectionToMenu(Menu $menu, Section $section): MenuSection {
        $rank = $menu->getMaxSectionRank() + 1;

        $menuSection = new MenuSection();
        $menu->addMenuSection($menuSection);
        $menuSection->setSection($section)
            ->setRank($rank)
        ;
        $this->em->persist($menuSection);

        return $menuSection;
    }

    public function removeSectionFromMenu(Section $section): void {
        $menu = $section->getSectionMenu()->getMenu();
        $rank = $section->getSectionMenu()->getRank();

        $this->em->remove($section);

        $higherSections = $menu->getMenuSections()->filter((fn(MenuSection $menuSection) => $menuSection->getRank() > $rank));

        foreach($higherSections as $section) {
            $section->setRank($section->getRank() - 1);
        }
    }
}