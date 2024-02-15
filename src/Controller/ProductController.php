<?php

namespace App\Controller;

use App\Entity\MenuSection;
use App\Entity\Product;
use App\Entity\Section;
use App\Entity\SectionProduct;
use App\Repository\AllergenRepository;
use App\Repository\ProductRepository;
use App\Repository\SectionRepository;
use App\Service\ExceptionService;
use App\Service\SectionService;
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
        SectionService $sectionService,
        AllergenRepository $allergenRepository,
        ValidatorInterface $validator,
        ExceptionService $exceptionService
    )
    {
        $product = $serializer->deserialize($request->getContent(), Product::class, "json");

        $validationErrors = $validator->validate($product);
        if($validationErrors->count()) {
            return $exceptionService->generateValidationErrorsResponse($validationErrors);
        }

        $content = $request->toArray();

        if(empty($content["sectionId"])) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                "Une première section doit être spécifiée pour créer un produit"
            );
        }

        $firstSection = $sectionRepository->find($content["sectionId"]);
        if(!$firstSection) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                "La section spécifiée est introuvable"
            );
        }

        if(!empty($content["allergenIds"]) && !is_array($content["allergenIds"])) {
            throw new HttpException(
                Response::HTTP_BAD_REQUEST,
                "Le format spécifié pour les allergènes est incorrect"
            );
        }

        foreach($content["allergenIds"] as $allergenId) {
            $allergen = $allergenRepository->find($allergenId);

            if(!$allergen) {
                throw new HttpException(
                    Response::HTTP_BAD_REQUEST,
                    "Au moins un allergène spécifié est introuvable"
                );
            }

            $product->addAllergen($allergen);
        }

        $this->em->persist($product);
        $sectionService->addProductToSection($firstSection, $product);

        $this->em->flush();
        $this->em->refresh($product);

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
