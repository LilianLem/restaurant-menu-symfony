<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Product;
use App\Service\SectionService;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ProductStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $innerProcessor,
        private SectionService $sectionService
    )
    {

    }

    #[Override] public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof Product);

        if($operation instanceof Post && $data->getSectionForInit()) {
            $this->sectionService->addProductToSection($data->getSectionForInit(), $data);
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}