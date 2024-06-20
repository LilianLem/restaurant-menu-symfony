<?php

namespace App\ApiResource\Auth;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use App\Security\ApiSecurityExpressionDirectory;
use App\State\Auth\ResetPasswordRequestStateProcessor;
use ArrayObject;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(status: 204)
    ],
    uriTemplate: "/reset-password",
    openapi: new Operation(
        tags: ["Authentication"],
        summary: "Sends a password reset email to a user",
        description: "",
        requestBody: new RequestBody(
            content: new ArrayObject([
                "application/json" => [
                    "schema" => [
                        "type" => "object",
                        "properties" => [
                            "email" => ["type" => "string"]
                        ]
                    ]
                ]
            ])
        ),
        responses: [
            204 => [
                "description" => "If the given email address matches an active user, an email has been sent",
            ],
            400 => [
                "description" => "Invalid input"
            ],
            422 => [
                "description" => "Unprocessable entity"
            ]
        ]
    ),
    security: ApiSecurityExpressionDirectory::ADMIN_OR_NOT_LOGGED,
    processor: ResetPasswordRequestStateProcessor::class
)]
class ResetPasswordRequestDto
{
    #[Assert\Length(max: 180, maxMessage: "L'adresse e-mail ne peut pas dépasser {{ limit }} caractères")]
    #[Assert\Email(message: "L'adresse e-mail renseignée n'est pas valide")]
    #[Assert\NotBlank(message: "L'adresse e-mail est obligatoire")]
    public string $email;
}