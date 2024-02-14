<?php

namespace App\Service;

use App\Entity\Menu;
use App\Entity\Restaurant;
use App\Entity\RestaurantMenu;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class RestaurantService
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function addMenuToRestaurant(Restaurant $restaurant, Menu $menu): RestaurantMenu {
        $rank = $restaurant->getMaxMenuRank() + 1;

        $restaurantMenu = new RestaurantMenu();
        $restaurant->addRestaurantMenu($restaurantMenu);
        $restaurantMenu->setMenu($menu)
            ->setRank($rank)
        ;
        $this->em->persist($restaurantMenu);

        return $restaurantMenu;
    }

    public function removeMenuFromRestaurant(Restaurant $restaurant, Menu $menu): void {
        $restaurantMenu = $restaurant->getRestaurantMenus()->findFirst(fn(int $key, RestaurantMenu $rm) => $rm->getMenu()->getId() === $menu->getId());
        if(!$restaurantMenu) {
            throw new Exception("Erreur : le menu n'existe pas dans ce restaurant !");
        }

        $rank = $restaurantMenu->getRank();

        $this->em->remove($restaurantMenu);

        $higherMenus = $restaurant->getRestaurantMenus()->filter((fn(RestaurantMenu $rm) => $rm->getRank() > $rank));

        /** @var RestaurantMenu $rm */
        foreach($higherMenus as $rm) {
            $rm->setRank($rm->getRank() - 1);
        }
    }
}