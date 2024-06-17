<?php

namespace App\ApiResource\Auth;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Entity\User;
use App\Security\ApiSecurityExpressionDirectory;
use App\State\Auth\ResetPasswordProcessStateProcessor;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post()
    ],
    uriTemplate: "/reset-password/process",
    openapiContext: [
        "tags" => ["Authentication"],
        "summary" => "Resets user password",
        "description" => "",
        "requestBody" => [
            "content" => [
                "application/json" => [
                    "schema" => [
                        "type" => "object",
                        "properties" => [
                            "token" => ["type" => "string"],
                            "password" => ["type" => "string"]
                        ]
                    ]
                ]
            ]
        ],
        "responses" => [
            204 => [
                "description" => "Password reset successful",
            ],
            400 => [
                "description" => "Invalid input (including an invalid password reset token)"
            ],
            422 => [
                "description" => "Unprocessable entity"
            ]
        ]
    ],
    security: ApiSecurityExpressionDirectory::ADMIN_OR_NOT_LOGGED,
    processor: ResetPasswordProcessStateProcessor::class
)]
class ResetPasswordProcessDto
{
    #[Assert\Length(max: 180, maxMessage: "Le token ne peut pas dépasser {{ limit }} caractères")]
    #[Assert\NotBlank(message: "Le token est obligatoire")]
    public string $token;

    #[Assert\PasswordStrength(["message" => "Ce mot de passe est trop vulnérable. Veuillez choisir un mot de passe plus sécurisé."], minScore: Assert\PasswordStrength::STRENGTH_WEAK)]
    #[Assert\NotCompromisedPassword(message: "Ce mot de passe est présent dans une fuite de données connue. Veuillez en choisir un autre.", skipOnError: true)]
    #[Assert\Length(min: 12, max: 128, minMessage: "Ce mot de passe est trop court, il doit compter au moins {{ limit }} caractères", maxMessage: "Ce mot de passe est trop long, il ne doit pas dépasser {{ limit }} caractères")]
    #[Assert\NotBlank(message: "Un mot de passe est obligatoire")]
    #[SerializedName("password")]
    private string $plainPassword;

    // Password is hashed directly into this entity method to keep plainPassword private
    public function getHashedPassword(User $user, UserPasswordHasherInterface $passwordHasher): string
    {
        return $passwordHasher->hashPassword($user, $this->plainPassword);
    }

    public function setPlainPassword(string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }
}