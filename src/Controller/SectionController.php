<?php

namespace App\Controller;

use App\Entity\MenuSection;
use App\Entity\Section;
use App\Repository\MenuRepository;
use App\Repository\SectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

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
        MenuRepository $menuRepository
    )
    {
        $section = $serializer->deserialize($request->getContent(), Section::class, "json");

        $content = $request->toArray();

        if(!$content["menuId"]) {
            // TODO: throw error
        }

        $menu = $menuRepository->find($content["menuId"]);

        if(!$menu) {
            // TODO: throw error
        }

        $sectionMenu = new MenuSection();
        $sectionMenu->setMenu($menu)
            ->setRank($menu->getMaxSectionRank() + 1)
        ;

        $section->setSectionMenu($sectionMenu);

        $this->em->persist($section);
        $this->em->flush();

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
