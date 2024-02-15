<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Repository\MenuRepository;
use App\Repository\RestaurantRepository;
use App\Service\ExceptionService;
use App\Service\RestaurantService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MenuController extends AbstractController
{
    private MenuRepository $repository;

    private SectionController $sectionController;

    public function __construct(MenuRepository $repository, EntityManagerInterface $em, SectionController $sectionController)
    {
        $this->repository = $repository;
        $this->em = $em;
        $this->sectionController = $sectionController;
    }

    #[Route('/api/menu', name: 'menu_all', methods: ['GET'])]
    public function getMenus(): JsonResponse
    {
        $menus = $this->repository->findAll();

        return $this->json($menus, Response::HTTP_OK, context: ["groups" => "getMenus"]);
    }

    #[Route('/api/menu/{id}', name: 'menu_one', methods: ['GET'])]
    public function getMenu(Menu $menu): JsonResponse
    {
        return $this->json($menu, Response::HTTP_OK, context: ["groups" => "getMenus"]);
    }

    #[Route('/api/menu', name: 'menu_create', methods: ['POST'])]
    public function createMenu(
        Request $request,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        RestaurantRepository $restaurantRepository,
        RestaurantService $restaurantService,
        ValidatorInterface $validator,
        ExceptionService $exceptionService
    )
    {
        $menu = $serializer->deserialize($request->getContent(), Menu::class, "json");

        $validationErrors = $validator->validate($menu);
        if($validationErrors->count()) {
            return $exceptionService->generateValidationErrorsResponse($validationErrors);
        }

        $content = $request->toArray();

        if(empty($content["restaurantId"])) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                "Un premier restaurant doit être spécifié pour créer un menu"
            );
        }

        $firstRestaurant = $restaurantRepository->find($content["restaurantId"]);
        if(!$firstRestaurant) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                "Le restaurant spécifié est introuvable"
            );
        }

        $this->em->persist($menu);
        $restaurantService->addMenuToRestaurant($firstRestaurant, $menu);

        $this->em->flush();
        $this->em->refresh($menu);

        $location = $urlGenerator->generate("menu_one", ["id" => $menu->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->json($menu, Response::HTTP_CREATED, ["Location" => $location], ["groups" => "getMenus"]);
    }

    #[Route('/api/menu/{id}', name: 'menu_delete', methods: ['DELETE'])]
    public function deleteMenu(Menu $menu): JsonResponse
    {
        foreach($menu->getMenuSections() as $mSection) {
            $this->sectionController->deleteSection($mSection->getSection());
        }

        $this->em->remove($menu);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
