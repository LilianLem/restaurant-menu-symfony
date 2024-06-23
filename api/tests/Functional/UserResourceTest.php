<?php

namespace App\Tests\Functional;

use App\Entity\User;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManager;
use Override;
use Symfony\Component\BrowserKit\CookieJar;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserResourceTest extends ApiTestCase
{
    use ResetDatabase;

    private ?EntityManager $em;

    #[Override] protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->em = $kernel->getContainer()
            ->get("doctrine")
            ->getManager()
        ;
    }

    public function testGetCollectionOfUsers(): void
    {
        UserFactory::createMany(9, ["verified" => true]);
        $user = UserFactory::first();

        // As guest

        $this->browser()
            ->get("/users")
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As user

        $this->browser(actingAs: $user)
            ->get("/users")
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;

        // As admin

        $admin = UserFactory::new()->asAdmin()->create();

        $json = $this->browser(actingAs: $admin)
            ->get("/users")
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonMatches('"hydra:totalItems"', 10)
            ->json()->decoded()
        ;
        $users = $json["hydra:member"];

        $this->assertSame(
            array_keys($users[0]),
            [
                "@id",
                "@type",
                "id",
                "email",
                "roles",
                "restaurants",
                "enabled",
                "verified"
            ],
            "User keys are not matching when connected as admin"
        );
    }

    public function testGetUser(): void
    {
        $user = UserFactory::createOne(["verified" => true]);
        $user2 = UserFactory::createOne(["verified" => true]);

        // As guest

        $this->browser()
            ->get("/users/".$user->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As user (self)

        $userData = $this->browser(actingAs: $user)
            ->get("/users/".$user->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json()->decoded()
        ;

        $this->assertSame(
            array_keys($userData),
            [
                "@context",
                "@id",
                "@type",
                "id",
                "email",
                "restaurants"
            ],
            "User keys are not matching when connected as normal user (self)"
        );

        // As user (other)

        $this->browser(actingAs: $user2)
            ->get("/users/".$user->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;

        // As admin

        $admin = UserFactory::new()->asAdmin()->create();

        $userData = $this->browser(actingAs: $admin)
            ->get("/users/".$user->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json()->decoded()
        ;

        $this->assertSame(
            array_keys($userData),
            [
                "@context",
                "@id",
                "@type",
                "id",
                "email",
                "roles",
                "restaurants",
                "enabled",
                "verified"
            ],
            "User keys are not matching when connected as admin"
        );
    }

    public function testPostUser(): void
    {
        $admin = UserFactory::new()->asAdmin()->create();

        // As guest

        $browser = $this->browser()->post("/users", [
                "json" => []
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ;

        $email = UserFactory::generateEmail();
        $password = UserFactory::getFaker()->password(12, 128);

        $browser->post("/users", [
                "json" => [
                    "email" => $email,
                    "password" => "password1234"
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('detail', "password: Ce mot de passe est trop vulnérable. Veuillez choisir un mot de passe plus sécurisé.")
        ;

        $userData = $browser->post("/users", [
                "json" => [
                    "email" => $email,
                    "password" => $password,
                    "roles" => ["ROLE_ADMIN"],
                    "enabled" => false,
                    "verified" => true
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonMatches('email', $email)
            ->json()->decoded()
        ;

        $this->assertSame(
            array_keys($userData),
            [
                "@context",
                "@id",
                "@type",
                "id",
                "email",
                "restaurants"
            ],
            "User keys are not matching when posting as guest"
        );

        /** @var User $userObject */
        $userObject = $this->em->getRepository(User::class)->find($userData["id"]);
        $this->assertIsObject($userObject, "User has not been created in repository after posting as guest");
        $this->assertObjectHasProperty("email", $userObject, "Object retrieved from repository is not an instance of User class");
        $this->assertSame(["ROLE_USER"], $userObject->getRoles());
        $this->assertSame(true, $userObject->isEnabled());
        $this->assertSame(false, $userObject->isVerified());

        // Attempt to retrieve auth tokens while not verified
        $browser->post("/token", [
                "json" => [
                    "email" => $email,
                    "password" => $password
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJsonMatches('message', "Ce compte est en attente de vérification. Veuillez confirmer votre inscription en cliquant sur le lien envoyé par e-mail.")
            ->json()->decoded()
        ;

        // Admin forces account to be verified
        $this->browser(actingAs: $admin)->patch($userData["@id"], [
                "json" => [
                    "verified" => true
                ]
            ])
            ->assertStatus(Response::HTTP_OK)
        ;

        // Attempt to retrieve auth tokens after being verified
        $tokens = $browser->post("/token", [
                "json" => [
                    "email" => $email,
                    "password" => $password
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_OK)
            ->json()->decoded()
        ;

        $this->assertSame(
            array_keys($tokens),
            [
                "token",
                "refreshToken"
            ],
            "Tokens request fields are incorrect"
        );

        $browser->use(function(CookieJar $cookieJar) {
            $this->assertNotNull($cookieJar->get("token"), "Token cookie has not been generated");
            $this->assertNotNull($cookieJar->get("refreshToken"), "Refresh token cookie has not been generated");
        });

        // Trying to create a new account with same email as previously
        $this->browser()->post("/users", [
                "json" => [
                    "email" => $email,
                    "password" => $password
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('detail', "email: Une erreur est survenue dans la création du compte. Si vous en avez déjà un, cliquez sur Connexion")
        ;

        // As normal user

        $user = UserFactory::createOne();
        $this->browser(actingAs: $user)
            ->post("/users", [
                "json" => []
            ])
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;

        // As admin, on behalf of a new user

        $email = UserFactory::generateEmail();
        $password = UserFactory::getFaker()->password(12, 128);

        $browser = $this->browser(actingAs: $admin);

        // Check that "classic" admins can't create another admin user
        $browser->post("/users", [
                "json" => [
                    "email" => $email,
                    "password" => $password,
                    "roles" => ["ROLE_ADMIN"],
                ]
            ])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonMatches('detail', "roles: Vous n'êtes pas autorisé à attribuer au moins l'un des rôles sélectionnés")
        ;

        $userData = $browser->post("/users", [
                "json" => [
                    "email" => $email,
                    "password" => $password,
                    "enabled" => false,
                    "verified" => true
                ]
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonMatches('email', $email)
            ->assertJsonMatches('roles', ["ROLE_USER"])
            ->assertJsonMatches('enabled', false)
            ->assertJsonMatches('verified', true)
            ->json()->decoded()
        ;

        $this->assertSame(
            array_keys($userData),
            [
                "@context",
                "@id",
                "@type",
                "id",
                "email",
                "roles",
                "restaurants",
                "enabled",
                "verified"
            ],
            "User keys are not matching when posting as admin"
        );

        // Attempt to retrieve auth tokens while not enabled
        $browser->post("/token", [
                "json" => [
                    "email" => $email,
                    "password" => $password
                ]
            ])
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJsonMatches('message', "Ce compte est actuellement désactivé. Veuillez nous contacter pour plus d'informations.")
            ->json()->decoded()
        ;



        // As super-admin

        $superAdmin = UserFactory::new()->asSuperAdmin()->create();
        $email = UserFactory::generateEmail();
        $password = UserFactory::getFaker()->password(12, 128);

        $this->browser(actingAs: $superAdmin)
            ->post("/users", [
                "json" => [
                    "email" => $email,
                    "password" => $password,
                    "roles" => ["ROLE_ADMIN"]
                ]
            ])
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonMatches('email', $email)
            ->assertJsonMatches('roles', ["ROLE_ADMIN", "ROLE_USER"])
            ->json()->decoded()
        ;
    }

    public function testHardDeleteUser(): void
    {
        $user = UserFactory::createOne(["verified" => true]);
        $user2 = UserFactory::createOne(["verified" => true]);

        // As guest

        $this->browser()
            ->delete("/users/".$user->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
        ;

        // As normal user (self and other)

        $this->browser(actingAs: $user)
            ->delete("/users/".$user->getId())
            ->assertStatus(Response::HTTP_FORBIDDEN)

            ->delete("/users/".$user2->getId())
            ->assertStatus(Response::HTTP_FORBIDDEN)
        ;

        // As admin

        $admin = UserFactory::new()->asAdmin()->create();

        $this->browser(actingAs: $admin)
            ->delete("/users/".$admin->getId())
            ->assertStatus(Response::HTTP_FORBIDDEN)

            ->delete("/users/".$user->getId())
            ->assertStatus(Response::HTTP_NO_CONTENT)

            ->delete("/users/".$user->getId())
            ->assertJson()
            ->assertStatus(Response::HTTP_NOT_FOUND)
        ;
    }

    public function testPatchUser(): void
    {
        // TODO: implement test
    }

    #[Override] protected function tearDown(): void
    {
        parent::tearDown();

        $this->em->close();
        $this->em = null;
    }
}