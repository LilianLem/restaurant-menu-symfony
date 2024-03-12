<?php

namespace App\EventSubscriber;

use App\Service\JwtAutorefreshTokenService;
use Gesdinet\JWTRefreshTokenBundle\Request\Extractor\ExtractorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

// DISABLED / TODO: try to generate new token and refresh token in a kernel.request subscriber (may need to change service to prevent dispatching events too early) => not possible because new cookies can't be added at that step (it seems impossible to create a response that can be used later in controllers and services)
class JwtFailureSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ExtractorInterface $refreshTokenExtractor,
        private JwtAutorefreshTokenService $jwtAutorefreshTokenService
    ) {
    }

    public function onJWTFailure(AuthenticationFailureEvent $event)
    {
        $request = $event->getRequest();
        $refreshToken = $this->refreshTokenExtractor->getRefreshToken($request, "refreshToken");

        if(!$refreshToken) {
            return $event->getResponse();
        }

        $result = $this->jwtAutorefreshTokenService->refreshToken($event, $refreshToken);

        if(!$result) {
            return $event->getResponse();
        }

        // TODO: find a way to launch the original request once again
        return $event->getResponse();
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            //"lexik_jwt_authentication.on_jwt_not_found" => "onJWTFailure",
            //"lexik_jwt_authentication.on_jwt_expired" => "onJWTFailure",
        ];
    }
}