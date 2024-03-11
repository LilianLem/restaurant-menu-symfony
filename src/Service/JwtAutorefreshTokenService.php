<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Security\Http\Authentication\AuthenticationSuccessHandler as GesdinetAuthenticationSuccessHandler;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\JWTUserToken;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class JwtAutorefreshTokenService
{
    /**
     * @param GesdinetAuthenticationSuccessHandler $gesdinetAuthenticationSuccessHandler
     */
    public function __construct(
        private RefreshTokenManagerInterface          $refreshTokenManager,
        private AuthenticationSuccessHandlerInterface $gesdinetAuthenticationSuccessHandler,
        private JWTTokenManagerInterface              $jwtManager,
        private UserRepository                        $userRepository,
        private ParameterBagInterface                 $paramsBag
    )
    {
    }

    private function createNewJwtToken(User $user): JWTUserToken
    {
        $tokenString = $this->jwtManager->create($user);
        return new JWTUserToken($user->getRoles(), $user, $tokenString, "api");
    }

    /**
     *  @return array{
     *    token: string,
     *    refreshToken: string
     *  }|false
     */
    public function refreshToken(AuthenticationFailureEvent $event, string $refreshTokenString): array|false
    {
        $refreshToken = $this->refreshTokenManager->get($refreshTokenString);

        if($refreshToken && $refreshToken->isValid()) {
            $user = $this->userRepository->findOneBy([$this->paramsBag->get("app.user_provider.property") => $refreshToken->getUsername()]);

            if(!$user) {
                return false;
            }

            $newToken = $this->createNewJwtToken($user);

            $tokenManagementResponse = $this->gesdinetAuthenticationSuccessHandler->onAuthenticationSuccess($event->getRequest(), $newToken);

            $responseCookies = $tokenManagementResponse->headers->getCookies();
            $tokenCookies = array_filter($responseCookies, fn($cookie) => in_array($cookie->getName(), ["token", "refreshToken"]));
            foreach($tokenCookies as $cookie) {
                $event->getResponse()->headers->setCookie($cookie);
            }

            return [
                "token" => $newToken->getCredentials(),
                "refreshToken" => $this->refreshTokenManager->getLastFromUsername($refreshToken->getUsername())
            ];
        }

        return false;
    }
}