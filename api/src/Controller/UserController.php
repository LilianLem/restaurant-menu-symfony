<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Uid\Ulid;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

// TODO: allow sending a new verify email
class UserController extends AbstractController
{
    // TODO: improvement: process entirely through API (frontend makes a POST request to /api/users/{id}/verify with GET parameters in the POST body)
    #[Route("/verify", name: "app_verify_email")]
    public function verifyUserEmail(Request $request, VerifyEmailHelperInterface $verifyEmailHelper, UserRepository $userRepository, EntityManagerInterface $em)
    {
        $userId = $request->query->get("id");
        if(!$userId || !Ulid::isValid($userId)) {
            throw new BadRequestHttpException("Erreur : l'identifiant utilisateur est manquant ou incorrect !");
        }

        $user = $userRepository->find(Ulid::fromString($userId));
        if(!$user) {
            throw $this->createNotFoundException("Erreur : l'utilisateur est introuvable !");
        }

        if($user->isVerified()) {
            // TODO: redirect to frontend
            throw new BadRequestHttpException("Erreur : ce compte est déjà activé !");

            // $this->addFlash("error", "Ce compte est déjà activé !");
        }

        try {
            $verifyEmailHelper->validateEmailConfirmationFromRequest(
                $request,
                $user->getId()->toBase32(),
                $user->getEmail()
            );
        } catch(VerifyEmailExceptionInterface $e) {
            throw new BadRequestHttpException("
                <p>Erreur : le lien de vérification est invalide ou expiré !</p>
                <p>Pour obtenir de l'assistance, veuillez nous contacter par mail à <a href='mailto:{$_ENV["CONTACT_EMAIL"]}'>{$_ENV["CONTACT_EMAIL"]}</a>.</p>
            ");

            // $this->addFlash("error", "Le lien de vérification est invalide ou expiré !<br>Pour obtenir de l'assistance, veuillez nous contacter par mail à <a href='mailto:{$_ENV["CONTACT_EMAIL"]}'>{$_ENV["CONTACT_EMAIL"]}</a>");
            // TODO: redirect to frontend
        }

        $user->setVerified(true);
        $em->flush();

        // TODO: remove and redirect to frontend
        return new Response("Compte vérifié ! Vous pouvez maintenant vous connecter. <a href='{$this->generateUrl("api_entrypoint")}'>Vers la documentation API</a>");

        // $this->addFlash("success", "Compte vérifié ! Vous pouvez maintenant vous connecter.");
    }
}