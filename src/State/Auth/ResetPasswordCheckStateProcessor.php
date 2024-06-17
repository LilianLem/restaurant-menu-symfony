<?php

namespace App\State\Auth;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Auth\ResetPasswordCheckDto;
use Override;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordCheckStateProcessor implements ProcessorInterface
{
    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper
    )
    {
    }

    #[Override] public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        assert($data instanceof ResetPasswordCheckDto);

        try {
            $this->resetPasswordHelper->validateTokenAndFetchUser($data->token);
        } catch (ResetPasswordExceptionInterface $e) {
            throw new NotFoundHttpException("Token invalide ou expiré");
        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
