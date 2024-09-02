<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
{
    $data = [
        'surname' => $this->surname,
        'telephone' => $this->telephone,
        'adresse' => $this->adresse,
    ];

    if ($this->user) {
        $data['user'] = [
            'nom' => $this->user->nom,
            'prenom' => $this->user->prenom,
            'login' => $this->user->login,
            'role_id' => $this->user->role_id,
            'etat' => $this->user->etat,
        ];
    }

    return $data;
}
}