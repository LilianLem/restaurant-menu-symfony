<?php

namespace App\Factory;

use App\Entity\User;
use Exception;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\String\ByteString;
use Zenstruck\Foundry\Persistence\RepositoryDecorator;

/**
 * @extends PersistentObjectFactory<User>
 *
 * @method        User                      create(array|callable $attributes = [])
 * @method static User                      createOne(array $attributes = [])
 * @method static User                      find(object|array|mixed $criteria)
 * @method static User                      findOrCreate(array $attributes)
 * @method static User                      first(string $sortedField = 'id')
 * @method static User                      last(string $sortedField = 'id')
 * @method static User                      random(array $attributes = [])
 * @method static User                      randomOrCreate(array $attributes = [])
 * @method static RepositoryDecorator<User> repository()
 * @method static User[]                    all()
 * @method static User[]                    createMany(int $number, array|callable $attributes = [])
 * @method static User[]                    createSequence(iterable|callable $sequence)
 * @method static User[]                    findBy(array $attributes)
 * @method static User[]                    randomRange(int $min, int $max, array $attributes = [])
 * @method static User[]                    randomSet(int $number, array $attributes = [])
 */
final class UserFactory extends PersistentObjectFactory
{
    public const string DEFAULT_PASSWORD = "password";

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
    protected function defaults(): array|callable
    {
        return [
            'email' => self::generateEmail(),
            'enabled' => true,
            'password' => "replacedAtPersist",
            'plainPassword' => self::DEFAULT_PASSWORD,
            'roles' => ["ROLE_USER"],
            'verified' => self::faker()->boolean(90),
        ];
    }

    public function asAdmin(): static
    {
        return $this->with([
            "email" => self::generateEmail("admin"),
            "roles" => ["ROLE_ADMIN"],
            "verified" => true
        ]);
    }

    public function asSuperAdmin(): static
    {
        return $this->with([
            "email" => self::generateEmail("sadmin"),
            "roles" => ["ROLE_SUPER_ADMIN"],
            "verified" => true
        ]);
    }

    public static function generateEmail(string $prefix = "user"): string
    {
        return $prefix."-".time()."-".ByteString::fromRandom(8)->toString()."@rmsymfdev.tk";
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            ->afterInstantiate(function(User $user): void {
                $user->hashPassword($this->passwordHasher);
            })
        ;
    }

    public static function class(): string
    {
        return User::class;
    }

    /** @return User */
    public static function randomNormalUser(): User
    {
        $qb = self::repository()->createQueryBuilder("u");
        $qb->where("u.roles = '[\"ROLE_USER\"]'")
            ->orderBy("RAND()")
            ->setMaxResults(1)
        ;

        /** @var User[] $result */
        $result = $qb->getQuery()->getResult();

        if(!$result) {
            throw new Exception("Error: no normal user found!");
        }

        return $qb->getQuery()->getResult()[0];
    }
}
