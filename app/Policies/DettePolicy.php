<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Dette;
use App\Helpers\ResponseHelper;

class DettePolicy
{
    public function viewAny(User $user)
    {
        if ($user->hasRole(['ADMIN', 'BOUTIQUIER'])) {
            return true;
        }

        ResponseHelper::sendForbidden('Ce profil n\'est pas autorisé à voir les dettes.');
    }

    public function view(User $user, Dette $dette)
    {
        if ($user->hasRole(['ADMIN', 'BOUTIQUIER'])) {
            return true;
        }

        ResponseHelper::sendForbidden('Ce profil n\'est pas autorisé à voir cette dette.');
    }

    public function create(User $user)
    {
        if ($user->hasRole(['ADMIN', 'BOUTIQUIER'])) {
            return true;
        }

        ResponseHelper::sendForbidden('Ce profil n\'est pas autorisé à créer une nouvelle dette.');
    }

    public function update(User $user, Dette $dette)
    {
        if ($user->hasRole(['ADMIN', 'BOUTIQUIER'])) {
            return true;
        }

        ResponseHelper::sendForbidden('Ce profil n\'est pas autorisé à modifier cette dette.');
    }

    public function delete(User $user, Dette $dette)
    {
        if ($user->hasRole(['ADMIN', 'BOUTIQUIER'])) {
            return true;
        }

        ResponseHelper::sendForbidden('Ce profil n\'est pas autorisé à supprimer cette dette.');
    }

    public function restore(User $user, Dette $dette)
    {
        if ($user->hasRole('ADMIN')) {
            return true;
        }

        ResponseHelper::sendForbidden('Ce profil n\'est pas autorisé à restaurer cette dette.');
    }

    public function forceDelete(User $user, Dette $dette)
    {
        if ($user->hasRole('ADMIN')) {
            return true;
        }

        ResponseHelper::sendForbidden('Ce profil n\'est pas autorisé à supprimer définitivement cette dette.');
    }
}