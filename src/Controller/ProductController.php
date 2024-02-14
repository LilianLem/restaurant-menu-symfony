<?php

namespace App\Controller;

use App\Entity\MenuSection;
use App\Entity\Product;
use App\Entity\Section;
use App\Entity\SectionProduct;
use App\Repository\AllergenRepository;
use App\Repository\ProductRepository;
use App\Repository\SectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProductController extends AbstractController
{
    private ProductRepository $repository;

    public function __construct(ProductRepository $repository, EntityManagerInterface $em)
    {
        $this->repository = $repository;
        $this->em = $em;
    }

    #[Route('/api/product', name: 'product_all', methods: ['GET'])]
    public function getProducts(): JsonResponse
    {
        $products = $this->repository->findAll();

        return $this->json($products, Response::HTTP_OK, context: ["groups" => "getProducts"]);
    }

    #[Route('/api/product/{id}', name: 'product_one', methods: ['GET'])]
    public function getProduct(Product $product): JsonResponse
    {
        return $this->json($product, Response::HTTP_OK, context: ["groups" => "getProducts"]);
    }

    #[Route('/api/product', name: 'product_create', methods: ['POST'])]
    public function createProduct(
        Request $request,
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        SectionRepository $sectionRepository,
        AllergenRepository $allergenRepository
    )
    {
        $product = $serializer->deserialize($request->getContent(), Product::class, "json");

        $content = $request->toArray();

        if(!$content["allergenIds"]) {
            // TODO: throw error
        }
        if(!is_array($content["allergenIds"])) {
            // TODO: throw error
        }

        $section = $sectionRepository->find($content["sectionId"]);
        if(!$section) {
            // TODO: throw error
        }

        foreach($content["allergenIds"] as $allergenId) {
            $allergen = $allergenRepository->find($allergenId);

            if(!$allergen) {
                // TODO: throw error
            }

            $product->addAllergen($allergen);
        }

        $sectionProduct = new SectionProduct();
        $sectionProduct->setSection($section)
            ->setRank($section->getMaxProductRank() + 1)
        ;

        $product->addSectionProduct($sectionProduct);

        $this->em->persist($product);
        $this->em->flush();

        $location = $urlGenerator->generate("product_one", ["id" => $product->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->json($product, Response::HTTP_CREATED, ["Location" => $location], ["groups" => "getProducts"]);
    }

    #[Route('/api/product/{id}', name: 'product_delete', methods: ['DELETE'])]
    public function deleteProduct(Product $product): JsonResponse
    {
        $this->em->remove($product);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
