<?php

namespace App\EventSubscriber;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class VerifyEmailSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private VerifyEmailHelperInterface $verifyEmailHelper
    )
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ["sendMail", EventPriorities::POST_WRITE]
        ];
    }

    public function sendMail(ViewEvent $event): void
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$user instanceof User || $method !== Request::METHOD_POST || $user->isVerified() !== false) {
            return;
        }

        // TODO: possible evolution: replace by a JWT token
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            "app_verify_email",
            $user->getId()->toBase32(),
            $user->getEmail(),
            ["id" => $user->getId()->toBase32()]
        );

        // TODO: make a better and prettier body
        $message = (new Email())
            ->from($_ENV["NOREPLY_EMAIL"])
            ->to($user->getEmail())
            ->subject("Activez votre compte")
            ->html("Pour valider votre compte, cliquez ici : <a href='{$signatureComponents->getSignedUrl()}'>{$signatureComponents->getSignedUrl()}</a>")
        ;

        $this->mailer->send($message);
    }
}