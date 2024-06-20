<?php

namespace App\State;

use ApiPlatform\Doctrine\Common\State\PersistProcessor;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\User;
use Override;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserHashPasswordProcessor implements ProcessorInterface
{
    public function __construct(
        #[Autowire(service: PersistProcessor::class)] private ProcessorInterface $innerProcessor,
        private UserPasswordHasherInterface $passwordHasher
    )
    {

    }

    #[Override] public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof User);

        if($data->isPlainPasswordFilled()) {
            $data->hashPassword($this->passwordHasher);
        }

        return $this->innerProcessor->process($data, $operation, $uriVariables, $context);
    }
}
