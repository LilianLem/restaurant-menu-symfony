<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Section;
use App\Service\MenuService;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class SectionStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $innerProcessor,
        private MenuService $menuService
    )
    {

    }

    #[Override] public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof Section);

        if($operation instanceof Post && $data->getMenuForInit()) {
            $this->menuService->addSectionToMenu($data->getMenuForInit(), $data);
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}