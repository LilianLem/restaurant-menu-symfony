<?php

namespace App\ApiResource\Auth;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Security\ApiSecurityExpressionDirectory;
use App\State\Auth\ResetPasswordCheckStateProcessor;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post()
    ],
    uriTemplate: "/reset-password/check",
    openapiContext: [
        "tags" => ["Authentication"],
        "summary" => "Checks if a password reset token is valid",
        "description" => "",
        "requestBody" => [
            "content" => [
                "application/json" => [
                    "schema" => [
                        "type" => "object",
                        "properties" => [
                            "token" => ["type" => "string"]
                        ]
                    ]
                ]
            ]
        ],
        "responses" => [
            204 => [
                "description" => "The token is valid",
            ],
            400 => [
                "description" => "Invalid input"
            ],
            404 => [
                "description" => "The token is invalid"
            ],
            422 => [
                "description" => "Unprocessable entity"
            ]
        ]
    ],
    security: ApiSecurityExpressionDirectory::ADMIN_OR_NOT_LOGGED,
    processor: ResetPasswordCheckStateProcessor::class
)]
class ResetPasswordCheckDto
{
    #[Assert\Length(max: 180, maxMessage: "Le token ne peut pas dépasser {{ limit }} caractères")]
    #[Assert\NotBlank(message: "Le token est obligatoire")]
    public string $token;
}