<?php
namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model;
use ArrayObject;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator("api_platform.openapi.factory", -25)]
class OpenApiFactory implements OpenApiFactoryInterface
{

    public function __construct(private OpenApiFactoryInterface $decorated)
    {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        $auth200ResponseContent = [
            "application/json" => [
                "schema" => [
                    "type" => "object",
                    "properties" => [
                        "token" => ["type" => "string"],
                        "refreshToken" => ["type" => "string"]
                    ]
                ]
            ]
        ];

        $openApi->getPaths()->addPath("/token", new Model\PathItem(
            post: new Operation(
                tags: ["Authentication"],
                summary: "Generates an auth token",
                requestBody: new RequestBody(
                    content: new ArrayObject([
                        "application/json" => [
                            "schema" => [
                                "type" => "object",
                                "properties" => [
                                    "email" => ["type" => "string"],
                                    "password" => ["type" => "string"]
                                ]
                            ]
                        ]
                    ]),
                    description: "User credentials"
                ),
                responses: [
                    200 => [
                        "description" => "An authentication token and its associated refresh token",
                        "content" => $auth200ResponseContent
                    ],
                    400 => [
                        "description" => "Invalid input"
                    ],
                    401 => [
                        "description" => "Invalid credentials"
                    ]
                ]
            )
        ));

        $openApi->getPaths()->addPath("/token/refresh", new Model\PathItem(
            post: new Operation(
                tags: ["Authentication"],
                summary: "Refreshes an auth token",
                requestBody: new RequestBody(
                    content: new ArrayObject([
                        "application/json" => [
                            "schema" => [
                                "type" => "object",
                                "properties" => [
                                    "refreshToken" => ["type" => "string"]
                                ]
                            ]
                        ]
                    ]),
                    description: "A refresh token retrieved on initial authentication"
                ),
                responses: [
                    200 => [
                        "description" => "A new token and refresh token",
                        "content" => $auth200ResponseContent
                    ],
                    401 => [
                        "description" => "Invalid input"
                    ]
                ]
            )
        ));

        $openApi->getPaths()->addPath("/token/invalidate", new Model\PathItem(
            post: new Operation(
                tags: ["Authentication"],
                summary: "Logs out current user",
                description: "Deletes token-related cookies",
                responses: [
                    200 => [
                        "description" => "Logged out successfully"
                    ],
                    400 => [
                        "description" => "Invalid input"
                    ]
                ]
            )
        ));

        return $openApi;
    }
}