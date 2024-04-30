<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\RankedEntityInterface;
use App\Service\RankedEntityService;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class RankedEntityStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $innerPersistProcessor,
        #[Autowire(service: RemoveProcessor::class)] private ProcessorInterface $innerRemoveProcessor,
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
        } elseif($operation instanceof DeleteOperationInterface) {
            $data = $this->processDeleteOperation($data);
            return $this->innerRemoveProcessor->process($data, $operation, $uriVariables, $context);
        }

        return $this->innerPersistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function processPostOperation(RankedEntityInterface $data): RankedEntityInterface
    {
        // Fill if needed

        return $data;
    }

    private function processDeleteOperation(RankedEntityInterface $data): RankedEntityInterface
    {
        $this->rankedEntityService->rearrangeRanksOnDeletion($data);

        return $data;
    }
}