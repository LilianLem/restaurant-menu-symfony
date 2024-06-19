<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Doctrine\Common\State\RemoveProcessor;
use ApiPlatform\Metadata\DeleteOperationInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use ApiPlatform\Validator\ValidatorInterface;
use App\Entity\Interface\RankingEntityInterface;
use App\Service\RankingEntityService;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class RankingEntityStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $innerPersistProcessor,
        #[Autowire(service: RemoveProcessor::class)] private ProcessorInterface $innerRemoveProcessor,
        private EntityManagerInterface $em,
        private RankingEntityService $rankingEntityService,
        private ValidatorInterface $validator
    )
    {

    }

    #[Override] public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof RankingEntityInterface);

        if($operation instanceof Post) {
            $data = $this->processPostOperation($data);
        } elseif($operation instanceof Patch) {
            $data = $this->processPatchOperation($data);
        } elseif($operation instanceof DeleteOperationInterface) {
            $this->validator->validate($data, ["groups" => "self:delete"]);
            $data = $this->processDeleteOperation($data);
            return $this->innerRemoveProcessor->process($data, $operation, $uriVariables, $context);
        }

        return $this->innerPersistProcessor->process($data, $operation, $uriVariables, $context);
    }

    private function processPostOperation(RankingEntityInterface $data): RankingEntityInterface
    {
        $maxParentRank = $data->getMaxParentCollectionRank();

        if(!is_null($maxParentRank)) {
            if($data->getRank()) {
                $this->rankingEntityService->rearrangeRanksOnCreation($data);
            } else {
                $data->setRank($maxParentRank + 1);
            }
        } elseif(!$data->getRank()) {
            $data->setRank(1);
        }

        return $data;
    }

    private function processPatchOperation(RankingEntityInterface $data): RankingEntityInterface
    {
        $originalRank = $this->em->getUnitOfWork()->getOriginalEntityData($data)["rank"];
        if($originalRank === $data->getRank()) {
            return $data;
        }

        if($data->getRank() > $data->getMaxParentCollectionRank()) {
            return $data;
        }

        $this->rankingEntityService->changeRanks($data, $originalRank);

        return $data;
    }

    private function processDeleteOperation(RankingEntityInterface $data): RankingEntityInterface
    {
        if($data->getRank() === $data->getMaxParentCollectionRank()) {
            return $data;
        }

        $this->rankingEntityService->rearrangeRanksOnDeletion($data);

        return $data;
    }
}