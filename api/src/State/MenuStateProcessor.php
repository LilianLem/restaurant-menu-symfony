<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Menu;
use App\Service\RankingEntityService;
use App\Service\RestaurantService;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MenuStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $innerProcessor,
        private RestaurantService $restaurantService,
        private RankingEntityService $rankingEntityService
    )
    {

    }

    /**
     * @template T2
     * @return T2
     */
    #[Override] public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof Menu);

        if($operation instanceof Post && $data->getRestaurantForInit()) {
            $rankingEntity = $this->restaurantService->addMenuToRestaurant($data->getRestaurantForInit(), $data);

            if($data->getRankOnRestaurantForInit()) {
                $originalRank = $rankingEntity->getRank();
                $rankingEntity->setRank($data->getRankOnRestaurantForInit());
                $this->rankingEntityService->changeRanks($rankingEntity, $originalRank);
            }
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}