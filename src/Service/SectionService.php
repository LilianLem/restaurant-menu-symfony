<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\Section;
use App\Entity\SectionProduct;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class SectionService
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function addProductToSection(Section $section, Product $product): SectionProduct {
        $rank = $section->getMaxProductRank() + 1;

        $sectionProduct = new SectionProduct();
        $section->addSectionProduct($sectionProduct);
        $sectionProduct->setProduct($product)
            ->setRank($rank)
        ;
        $this->em->persist($sectionProduct);

        return $sectionProduct;
    }

    public function removeProductFromSection(Section $section, Product $product): void {
        $sectionProduct = $section->getSectionProducts()->findFirst(fn(int $key, SectionProduct $sp) => $sp->getProduct()->getId() === $product->getId());
        if(!$sectionProduct) {
            throw new Exception("Erreur : le produit n'existe pas dans cette section !");
        }

        $rank = $sectionProduct->getRank();

        $this->em->remove($sectionProduct);

        $higherProducts = $section->getSectionProducts()->filter((fn(SectionProduct $sp) => $sp->getRank() > $rank));

        foreach($higherProducts as $product) {
            $product->setRank($product->getRank() - 1);
        }
    }
}