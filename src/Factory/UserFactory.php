<?php

namespace App\Factory;

use App\Entity\User;
use App\Repository\UserRepository;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\ByteString;
use Zenstruck\Foundry\ModelFactory;
use Zenstruck\Foundry\Proxy;
use Zenstruck\Foundry\RepositoryProxy;

/**
 * @extends ModelFactory<User>
 *
 * @method        User|Proxy                     create(array|callable $attributes = [])
 * @method static User|Proxy                     createOne(array $attributes = [])
 * @method static User|Proxy                     find(object|array|mixed $criteria)
 * @method static User|Proxy                     findOrCreate(array $attributes)
 * @method static User|Proxy                     first(string $sortedField = 'id')
 * @method static User|Proxy                     last(string $sortedField = 'id')
 * @method static User|Proxy                     random(array $attributes = [])
 * @method static User|Proxy                     randomOrCreate(array $attributes = [])
 * @method static UserRepository|RepositoryProxy repository()
 * @method static User[]|Proxy[]                 all()
 * @method static User[]|Proxy[]                 createMany(int $number, array|callable $attributes = [])
 * @method static User[]|Proxy[]                 createSequence(iterable|callable $sequence)
 * @method static User[]|Proxy[]                 findBy(array $attributes)
 * @method static User[]|Proxy[]                 randomRange(int $min, int $max, array $attributes = [])
 * @method static User[]|Proxy[]                 randomSet(int $number, array $attributes = [])
 */
final class UserFactory extends ModelFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    )
    {
        parent::__construct();
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function getDefaults(): array
    {
        return [
            'email' => self::generateEmail(),
            'enabled' => true,
            'password' => "replacedAtPersist",
            'plainPassword' => "password",
            'roles' => ["ROLE_USER"],
            'verified' => self::faker()->boolean(90),
        ];
    }

    public function asAdmin(): static
    {
        return $this->addState([
            "email" => self::generateEmail("admin"),
            "roles" => ["ROLE_ADMIN"],
            "verified" => true
        ]);
    }

    public function asSuperAdmin(): static
    {
        return $this->addState([
            "email" => self::generateEmail("sadmin"),
            "roles" => ["ROLE_SUPER_ADMIN"],
            "verified" => true
        ]);
    }

    protected static function generateEmail(string $prefix = "user"): string
    {
        return $prefix."-".self::faker()->unixTime()."-".ByteString::fromRandom(8)->toString()."@rmsymfdev.tk";
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): self
    {
        return $this
            ->afterInstantiate(function(User $user): void {
                $user->hashPassword($this->passwordHasher);
            })
        ;
    }

    protected static function getClass(): string
    {
        return User::class;
    }

    /** @return User|Proxy<User> */
    public static function randomNormalUser(): User|Proxy
    {
        $qb = self::repository()->createQueryBuilder("u");
        $qb->where("u.roles = '[\"ROLE_USER\"]'")
            ->orderBy("RAND()")
            ->setMaxResults(1)
        ;

        /** @var User[]|array<Proxy<User>> $result */
        $result = $qb->getQuery()->getResult();

        if(!$result) {
            throw new Exception("Error: no normal user found!");
        }

        return $qb->getQuery()->getResult()[0];
    }
}
