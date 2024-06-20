<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Restaurant;
use Override;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class RestaurantStateProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $innerProcessor,
        private Security $security
    )
    {

    }

    #[Override] public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof Restaurant);

        if($operation instanceof Post && !$data->getOwner() && $this->security->getUser()) {
            $data->setOwner($this->security->getUser());
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}