<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user)
    {
        return $user->hasRole(['ADMIN', 'BOUTIQUIER']);
    }

    public function view(User $user, User $model)
    {
        if ($user->hasRole(['ADMIN', 'BOUTIQUIER'])) {
            return true;
        }
        // Le CLIENT ne peut voir que son propre profil
        return $user->hasRole('CLIENT') && $user->id === $model->id;
    }

    public function create(User $user)
    {
        return $user->hasRole('ADMIN');
    }

    public function update(User $user, User $model)
    {
        if ($user->hasRole('ADMIN')) {
            return true;
        }
        // Le CLIENT ne peut mettre à jour que son propre profil
        return $user->hasRole('CLIENT') && $user->id === $model->id;
    }

    public function delete(User $user, User $model)
    {
        return $user->hasRole('ADMIN');
    }
}