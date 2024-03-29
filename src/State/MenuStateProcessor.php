<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Menu;
use App\Service\RestaurantService;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MenuStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $innerProcessor,
        private RestaurantService $restaurantService
    )
    {

    }

    #[Override] public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof Menu);

        if($operation instanceof Post && $data->getRestaurantForInit()) {
            $this->restaurantService->addMenuToRestaurant($data->getRestaurantForInit(), $data);
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}