<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ExceptionService
{
    public function generateValidationErrorsResponse(ConstraintViolationListInterface $validationErrors): JsonResponse
    {
        $data = array(
            "status" => Response::HTTP_BAD_REQUEST,
            "message" => "Il y a au moins une erreur dans les données spécifiées",
            "details" => $this->getFieldMappedValidationErrors($validationErrors)
        );

        return new JsonResponse($data, Response::HTTP_BAD_REQUEST);
    }

    /** @return array{string, string[]} */
    private function getFieldMappedValidationErrors(ConstraintViolationListInterface $validationErrors): array
    {
        $mappedErrors = [];
        foreach($validationErrors as $e) {
            $mappedErrors[$e->getPropertyPath()][] = $e->getMessage();
        }

        return $mappedErrors;
    }
}