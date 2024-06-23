<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\BrowserKit\CookieJar;
use Zenstruck\Browser\HttpOptions;
use Zenstruck\Browser\Test\HasBrowser;

// TODO: improve test performance with https://github.com/dmaicher/doctrine-test-bundle (bundle recommended in zenstruck/foundry docs)
abstract class ApiTestCase extends KernelTestCase
{
    use HasBrowser {
        browser as appBrowser;
    }

    /** @param User $actingAs */
    protected function browser(array $options = [], array $server = [], ?User $actingAs = null): AppBrowser
    {
        /** @var AppBrowser $browser */
        $browser = $this->appBrowser($options, $server)
            ->setDefaultHttpOptions(
                HttpOptions::create()
                    ->withHeader("Accept", "application/ld+json")
            )
        ;

        // Workaround to log users with JWT token stateless authentication (replaces usage of Zenstruck/Browser's actingAs() method)

        if(!$actingAs) {
            return $browser;
        }

        $authResponse = $browser->post("/token", [
            "json" => [
                "email" => $actingAs->getEmail(),
                "password" => UserFactory::DEFAULT_PASSWORD
            ]
        ])->client()->getInternalResponse();

        $browser->use(fn(CookieJar $cookieJar) => $cookieJar->updateFromResponse($authResponse));

        return $browser;
    }
}