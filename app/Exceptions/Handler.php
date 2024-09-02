<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Auth\AuthenticationException;
use App\Traits\RestResponseTrait;
use App\Enums\StateEnum;

class Handler extends ExceptionHandler
{
    use RestResponseTrait;

    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            if ($exception instanceof ValidationException) {
                return $this->sendResponse(
                    null,
                    StateEnum::ECHEC,
                    $exception->errors()['libelle'][0] ?? 'Erreur de validation',
                    422
                );
            }

            if ($exception instanceof AuthorizationException) {
                return $this->sendResponse(
                    null,
                    StateEnum::ECHEC,
                    'Vous n\'avez pas les permissions nécessaires pour effectuer cette action',
                    403
                );
            }

            if ($exception instanceof ModelNotFoundException) {
                return $this->sendResponse(
                    null,
                    StateEnum::ECHEC,
                    'La ressource demandée n\'a pas été trouvée',
                    404
                );
            }

            if ($exception instanceof NotFoundHttpException) {
                return $this->sendResponse(
                    null,
                    StateEnum::ECHEC,
                    'La route demandée n\'existe pas',
                    404
                );
            }

            if ($exception instanceof MethodNotAllowedHttpException) {
                return $this->sendResponse(
                    null,
                    StateEnum::ECHEC,
                    'La méthode HTTP utilisée n\'est pas autorisée pour cette route',
                    405
                );
            }

            if ($exception instanceof AuthenticationException) {
                return $this->sendResponse(
                    null,
                    StateEnum::ECHEC,
                    'Authentification requise pour accéder à cette ressource',
                    401
                );
            }

            // Pour toutes les autres exceptions non gérées
            return $this->sendResponse(
                null,
                StateEnum::ECHEC,
                'Une erreur inattendue est survenue : ' . $exception->getMessage(),
                500
            );
        }

        return parent::render($request, $exception);
    }
}