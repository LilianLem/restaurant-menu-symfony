<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Product;
use App\Service\RankingEntityService;
use App\Service\SectionService;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ProductStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $innerProcessor,
        private SectionService $sectionService,
        private RankingEntityService $rankingEntityService
    )
    {

    }

    #[Override] public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof Product);

        if($operation instanceof Post && $data->getSectionForInit()) {
            $rankingEntity = $this->sectionService->addProductToSection($data->getSectionForInit(), $data);

            if($data->getRankOnSectionForInit()) {
                $originalRank = $rankingEntity->getRank();
                $rankingEntity->setRank($data->getRankOnSectionForInit());
                $this->rankingEntityService->changeRanks($rankingEntity, $originalRank);
            }
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}