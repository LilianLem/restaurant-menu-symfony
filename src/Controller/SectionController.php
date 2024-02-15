<?php

namespace App\Controller;

use App\Entity\MenuSection;
use App\Entity\Section;
use App\Repository\MenuRepository;
use App\Repository\SectionRepository;
use App\Service\ExceptionService;
use App\Service\MenuService;
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

class SectionController extends AbstractController
{
    private SectionRepository $repository;

    private ProductController $productController;

    public function __construct(SectionRepository $repository, EntityManagerInterface $em, ProductController $productController)
    {
        $this->repository = $repository;
        $this->em = $em;
        $this->productController = $productController;
    }

    #[Route('/api/section', name: 'section_all', methods: ['GET'])]
    public function getSections(): JsonResponse
    {
        $sections = $this->repository->findAll();

        return $this->json($sections, Response::HTTP_OK, context: ["groups" => "getSections"]);
    }

    #[Route('/api/section/{id}', name: 'section_one', methods: ['GET'])]
    public function getSection(Section $section): JsonResponse
    {
        return $this->json($section, Response::HTTP_OK, context: ["groups" => "getSections"]);
    }

    #[Route('/api/section', name: 'section_create', methods: ['POST'])]
    public function createSection(
        Request $request,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        MenuRepository $menuRepository,
        MenuService $menuService,
        ValidatorInterface $validator,
        ExceptionService $exceptionService
    )
    {
        $section = $serializer->deserialize($request->getContent(), Section::class, "json");

        $validationErrors = $validator->validate($section);
        if($validationErrors->count()) {
            return $exceptionService->generateValidationErrorsResponse($validationErrors);
        }

        $content = $request->toArray();

        if(empty($content["menuId"])) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                "Un premier menu doit être spécifié pour créer une section"
            );
        }

        $firstMenu = $menuRepository->find($content["menuId"]);
        if(!$firstMenu) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                "Le menu spécifié est introuvable"
            );
        }

        $this->em->persist($section);
        $menuService->addSectionToMenu($firstMenu, $section);

        $this->em->flush();
        $this->em->refresh($section);

        $location = $urlGenerator->generate("section_one", ["id" => $section->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->json($section, Response::HTTP_CREATED, ["Location" => $location], ["groups" => "getSections"]);
    }

    #[Route('/api/section/{id}', name: 'section_delete', methods: ['DELETE'])]
    public function deleteSection(Section $section): JsonResponse
    {
        foreach($section->getSectionProducts() as $sProduct) {
            if($sProduct->getProduct()?->getSectionProducts()->count() === 1) {
                $this->productController->deleteProduct($sProduct->getProduct());
            }
        }

        $this->em->remove($section);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
