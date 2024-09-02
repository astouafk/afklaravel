<?php

namespace App\Traits;

use App\Enums\StateEnum;

trait RestResponseTrait
{
    public function sendResponse($data, StateEnum $status = StateEnum::SUCCESS, $message = 'Opération réussie', $codeStatut = 200)
    {
        return response()->json([
            'data' => $data,
            'status' => $status->value,
            'message' => $message,
        ], $codeStatut);
    }

    public function sendError($message = 'Erreur interne du serveur', $codeStatut = 500, $status = StateEnum::ECHEC)
    {
        // Vous pouvez utiliser `abort()` pour arrêter l'exécution et renvoyer une réponse d'erreur
        abort($codeStatut, $message, [
            'status' => $status->value,
        ]);
    }
}
