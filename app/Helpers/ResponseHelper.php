<?php
namespace App\Helpers;

use App\Traits\RestResponseTrait;
use App\Enums\StateEnum;

class ResponseHelper
{
    use RestResponseTrait;

    public static function sendBadRequest($message = 'Erreur sur la requete')
    {
        return (new self())->sendError($message, 400, StateEnum::ECHEC);
    }

    public static function sendUnauthorized($message = 'Notez que l\'authentification requise')
    {
        return (new self())->sendError($message, 401, StateEnum::ECHEC);
    }

    public static function sendForbidden($message = 'Acces interdit')
    {
        return (new self())->sendError($message, 403, StateEnum::ECHEC);
    }

    public static function sendNotFound($message = 'Acces interdit')
    {
        return (new self())->sendError($message, 404, StateEnum::ECHEC);
    }

    public static function sendServerError($message = 'Erreur du serveur')
    {
        return (new self())->sendError($message, 500, StateEnum::ECHEC);
    }

    public static function sendBadGateway($message = 'Probleme de passerelle')
    {
        return (new self())->sendError($message, 502, StateEnum::ECHEC);
    }

    public static function sendServiceUnavailable($message = 'Le service est non disponible')
    {
        return (new self())->sendError($message, 503, StateEnum::ECHEC);
    }

    public static function sendOk($data, $message = 'Traitement réussie')
    {
        return (new self())->sendResponse($data, StateEnum::SUCCESS, $message, 200);
    }

    public static function sendCreated($data, $message = 'Ressource creee')
    {
        return (new self())->sendResponse($data, StateEnum::SUCCESS, $message, 201);
    }

    public static function sendNoContent($message = 'Contenu vide')
    {
        return (new self())->sendResponse(null, StateEnum::SUCCESS, $message, 204);
    }

    public static function sendLengthRequired($message = 'Length Requis')
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
        ], 411);
    }
}