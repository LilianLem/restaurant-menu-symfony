<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Interface\DirectSoftDeleteableEntityInterface;
use App\Entity\Interface\RankedEntityInterface;
use App\Entity\Interface\RankingEntityInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class DirectSoftDeleteableEntityStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: RemoveProcessor::class)] private ProcessorInterface $innerRemoveProcessor,
        #[Autowire(service: RankedEntityStateProcessor::class)] private ProcessorInterface $innerRankedEntityStateProcessor,
        #[Autowire(service: RankingEntityStateProcessor::class)] private ProcessorInterface $innerRankingEntityStateProcessor,
        private EntityManagerInterface $em
    )
    {

    }

    #[Override] public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof DirectSoftDeleteableEntityInterface);

        if(!$operation instanceof DeleteOperationInterface) {
            throw new Exception("This state processor should not be called in this situation! Please contact the developer.");
        }

        if(!$data->isDeleted()) {
            $data->softDelete(true);
            $this->em->flush();

            return $this->innerRemoveProcessor->process($data, $operation, $uriVariables, $context);
        }

        if($data instanceof RankingEntityInterface) {
            return $this->innerRankingEntityStateProcessor->process($data, $operation, $uriVariables, $context);
        }

        if($data instanceof RankedEntityInterface) {
            return $this->innerRankedEntityStateProcessor->process($data, $operation, $uriVariables, $context);
        }

        return $this->innerRemoveProcessor->process($data, $operation, $uriVariables, $context);
    }
}