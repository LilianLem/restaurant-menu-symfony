<?php

namespace App\State\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Auth\ResetPasswordRequestDto;
use App\Repository\ResetPasswordRequestRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordRequestStateProcessor implements ProcessorInterface
{
    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private ResetPasswordRequestRepository $resetPasswordRequestRepository,
        private UserRepository $userRepository,
        private MailerInterface $mailer,
        private EntityManagerInterface $em
    )
    {
    }

    #[Override] public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof ResetPasswordRequestDto);

        $user = $this->userRepository->findOneBy([
            "email" => $data->email,
        ]);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        if(!$user->isVerified() || !$user->isEnabled()) {
            $email = (new TemplatedEmail())
                ->from($_ENV["NOREPLY_EMAIL"])
                ->to($user->getEmail())
                ->subject("Réinitialisation de votre mot de passe")
                ->htmlTemplate("reset_password/email_failed.html.twig")
                ->context([
                    "enabled" => $user->isEnabled()
                ])
            ;

            $this->mailer->send($email);

            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        $this->resetPasswordRequestRepository->removeRequests($user);

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
            $this->em->flush();
        } catch (ResetPasswordExceptionInterface $e) {
            return new Response(null, Response::HTTP_NO_CONTENT);
        }

        $email = (new TemplatedEmail())
            ->from($_ENV["NOREPLY_EMAIL"])
            ->to($user->getEmail())
            ->subject("Réinitialisation de votre mot de passe")
            ->htmlTemplate("reset_password/email.html.twig")
            ->context([
                "resetToken" => $resetToken
            ])
        ;

        $this->mailer->send($email);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
