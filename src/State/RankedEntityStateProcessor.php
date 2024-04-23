<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Menu;
use App\Entity\RankedEntityInterface;
use App\Entity\Restaurant;
use App\Entity\Section;
use App\Service\RankedEntityService;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use UnexpectedValueException;

class RankedEntityStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $innerProcessor,
        private EntityManagerInterface $em,
        private RankedEntityService $rankedEntityService
    )
    {

    }

    #[Override] public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof RankedEntityInterface);

        if($operation instanceof Post) {
            $data = $this->processPostOperation($data);
        } elseif($operation instanceof Patch) {
            $data = $this->processPatchOperation($data);
        } elseif($operation instanceof DeleteOperationInterface) {
            $data = $this->processDeleteOperation($data);
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function processPostOperation(RankedEntityInterface $data): RankedEntityInterface
    {
        $maxParentRank = $data->getMaxParentCollectionRank();
        if(!is_null($maxParentRank) && !$data->getRank()) {
            $data->setRank($maxParentRank + 1);
        }

        return $data;
    }

    private function processPatchOperation(RankedEntityInterface $data): RankedEntityInterface
    {
        $originalRank = $this->em->getUnitOfWork()->getOriginalEntityData($data)["rank"];
        if($originalRank === $data->getRank()) {
            return $data;
        }

        if($data->getRank() > $data->getMaxParentCollectionRank()) {
            return $data;
        }

        // TODO: test method
        $this->rankedEntityService->changeRanks($data, $originalRank);

        return $data;
    }

    private function processDeleteOperation(RankedEntityInterface $data): RankedEntityInterface
    {
        // TODO: change ranks of other elements

        return $data;
    }
}