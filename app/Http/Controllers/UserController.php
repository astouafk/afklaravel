<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Enums\EtatEnum;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected function authorizationFailed()
    {
        return ResponseHelper::sendForbidden('Permission refusée');
    }

    public function index(Request $request)
    {
        if (!$this->authorize('viewAny', User::class)) {
            return $this->authorizationFailed();
        }

        $active = $request->query('active');
        $role = $request->query('role');

        $query = User::query();

        if ($active !== null) {
            $etat = $active === 'oui' ? EtatEnum::ACTIF->value : EtatEnum::INACTIF->value;
            $query->where('etat', $etat);
        }

        if ($role !== null) {
            $roleExists = Role::where('name', $role)->exists();

            if (!$roleExists) {
                return ResponseHelper::sendOk(null, 'Le rôle spécifié n\'existe pas');
            }

            $query->whereHas('role', function ($query) use ($role) {
                $query->where('name', $role);
            });
        }

        $users = $query->get();

        return ResponseHelper::sendOk($users, 'Liste des utilisateurs récupérée avec succès');
    }

    public function store(Request $request)
    {
        if (!$this->authorize('create', User::class)) {
            return $this->authorizationFailed();
        }

        $request->validate([
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'login' => 'required|string|unique:users|max:255',
            'password' => 'required|string|min:6',
            'role_id' => 'required|numeric|exists:roles,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'etat' => 'required|string|in:' . implode(',', array_map(fn($case) => $case->value, EtatEnum::cases())),
        ]);

        $path = null;
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
        }

        try {
            $user = User::create([
                'nom' => $request->nom,
                'prenom' => $request->prenom,
                'login' => $request->login,
                'password' => Hash::make($request->password),
                'role_id' => $request->role_id,
                'photo' => $path,
                'etat' => $request->etat ?? 'ACTIF',
            ]);
        } catch (\Exception $e) {
            return ResponseHelper::sendServerError('Erreur lors de la création de l\'utilisateur: ' . $e->getMessage());
        }

        return ResponseHelper::sendCreated($user, 'Utilisateur créé avec succès');
    }

    public function show(User $user)
    {
        if (!$this->authorize('view', $user)) {
            return $this->authorizationFailed();
        }
        return ResponseHelper::sendOk($user, 'Utilisateur récupéré avec succès');
    }

    public function update(Request $request, User $user)
    {
        if (!$this->authorize('update', $user)) {
            return $this->authorizationFailed();
        }

        $request->validate([
            'nom' => 'string|max:255',
            'prenom' => 'string|max:255',
            'login' => 'string|unique:users,login,' . $user->id . '|max:255',
            'password' => 'string|min:6',
            'role_id' => 'numeric|exists:roles,id',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
            $user->photo = $path;
        }

        if ($request->filled('password')) {
            $request->merge(['password' => Hash::make($request->password)]);
        }

        $user->update($request->except('photo'));

        return ResponseHelper::sendOk($user, 'Utilisateur mis à jour avec succès');
    }

    public function destroy(User $user)
    {
        if (!$this->authorize('delete', $user)) {
            return $this->authorizationFailed();
        }

        $user->delete();
        return ResponseHelper::sendOk(null, 'Utilisateur supprimé avec succès');
    }
}