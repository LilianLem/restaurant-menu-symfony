<?php

namespace App\Security;

use App\Entity\User;
use Override;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{

    #[Override] public function checkPreAuth(UserInterface $user): void
    {
        if(!$user instanceof User) {
            return;
        }

        if(!$user->isEnabled()) {
            throw new CustomUserMessageAccountStatusException("Ce compte est actuellement désactivé. Veuillez nous contacter pour plus d'informations.");
        }

        if(!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException("Ce compte est en attente de vérification. Veuillez confirmer votre inscription en cliquant sur le lien envoyé par e-mail.");
        }
    }

    #[Override] public function checkPostAuth(UserInterface $user): void
    {
    }
}