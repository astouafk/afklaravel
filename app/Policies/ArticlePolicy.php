<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Article;
use App\Enums\StateEnum;
use App\Helpers\ResponseHelper;

class ArticlePolicy
{   
    public function viewAny(User $user)
    {
        if ($user->hasRole(['ADMIN', 'BOUTIQUIER'])) {
            return true;
        }
        ResponseHelper::sendForbidden('Ce profil n\'est pas autorisé à la liste des articles');
    }

    public function view(User $user, Article $article)
    {
        if ($user->hasRole(['ADMIN', 'BOUTIQUIER'])) {
            return true;
        }
        ResponseHelper::sendForbidden('Ce profil ne peut pas voir cet article');
    }

    public function create(User $user)
    {
        if ($user->hasRole(['ADMIN', 'BOUTIQUIER'])) {
            return true;
        }
        ResponseHelper::sendForbidden('Ce profil n\'est pas autorisé à la création d\'articles');
    }

    public function update(User $user, Article $article)
    {
        if ($user->hasRole(['ADMIN', 'BOUTIQUIER'])) {
            return true;
        }
        ResponseHelper::sendForbidden('Ce profil n\'est pas autorisé à mettre à jour cet article');
    }

    public function delete(User $user, Article $article)
    {
        if ($user->hasRole(['ADMIN', 'BOUTIQUIER'])) {
            return true;
        }
        ResponseHelper::sendForbidden('Ce profil n\'est pas autorisé à supprimer cet article');
    }

    public function restore(User $user, Article $article)
    {
        if ($user->hasRole(['ADMIN', 'BOUTIQUIER'])) {
            return true;
        }
        ResponseHelper::sendForbidden('Ce profil n\'est pas autorisé à restaurer cet article');
    }

    public function forceDelete(User $user, Article $article)
    {
        if ($user->hasRole('ADMIN')) {
            return true;
        }
        ResponseHelper::sendForbidden('Ce profil n\'est pas autorisé à supprimer définitivement cet article');
    }

    public function updateAny(User $user)
    {
        if ($user->hasRole(['ADMIN', 'BOUTIQUIER'])) {
            return true;
        }
        ResponseHelper::sendForbidden('Ce profil n\'est pas autorisé à mettre à jour plusieurs articles');
    }
}