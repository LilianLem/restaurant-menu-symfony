<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Repository\RestaurantRepository;
use App\Repository\UserRepository;
use App\Service\ExceptionService;
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

class RestaurantController extends AbstractController
{
    private RestaurantRepository $repository;

    public function __construct(RestaurantRepository $repository, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    #[Route('/api/restaurant', name: 'restaurant_all', methods: ['GET'])]
    public function getRestaurants(): JsonResponse
    {
        $restaurants = $this->repository->findAll();

        return $this->json($restaurants, Response::HTTP_OK, context: ["groups" => "getRestaurants"]);
    }

    #[Route('/api/restaurant/{id}', name: 'restaurant_one', methods: ['GET'])]
    public function getRestaurant(Restaurant $restaurant): JsonResponse
    {
        return $this->json($restaurant, Response::HTTP_OK, context: ["groups" => "getRestaurants"]);
    }

    #[Route('/api/restaurant', name: 'restaurant_create', methods: ['POST'])]
    public function createRestaurant(
        Request $request,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        ExceptionService $exceptionService
    )
    {
        $restaurant = $serializer->deserialize($request->getContent(), Restaurant::class, "json");

        $validationErrors = $validator->validate($restaurant);
        if($validationErrors->count()) {
            return $exceptionService->generateValidationErrorsResponse($validationErrors);
        }

        $content = $request->toArray();

        if(empty($content["ownerId"])) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                "Un propriétaire du restaurant doit être spécifié",
            );
        }

        $owner = $userRepository->find($content["ownerId"]);
        if(!$owner) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                "L'utilisateur spécifié en tant que propriétaire est introuvable"
            );
        }

        $restaurant->setOwner($owner);

        $this->em->persist($restaurant);
        $this->em->flush();

        $location = $urlGenerator->generate("restaurant_one", ["id" => $restaurant->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->json($restaurant, Response::HTTP_CREATED, ["Location" => $location], ["groups" => "getRestaurants"]);
    }

    #[Route('/api/restaurant/{id}', name: 'restaurant_delete', methods: ['DELETE'])]
    public function deleteRestaurant(
        Restaurant $restaurant,
        MenuController $menuController
    ): JsonResponse
    {
        foreach($restaurant->getRestaurantMenus() as $rMenu) {
            if($rMenu->getMenu()?->getMenuRestaurants()->count() === 1) {
                $menuController->deleteMenu($rMenu->getMenu());
            }
        }

        $this->em->remove($restaurant);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
