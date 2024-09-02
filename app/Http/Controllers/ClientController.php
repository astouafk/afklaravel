<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Enums\EtatEnum;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use App\Http\Resources\ClientResource;
use App\Http\Resources\ClientCollection;
use App\Http\Requests\StoreClientRequest;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    protected function authorizationFailed()
    {
        return ResponseHelper::sendForbidden('Permission refusée');
    }

    public function index(Request $request)
    {
        if (!$this->authorize('viewAny', Client::class)) {
            return $this->authorizationFailed();
        }

        $comptes = $request->query('comptes');
        $active = $request->query('active');

        $query = QueryBuilder::for(Client::class)
            ->allowedFilters(['surname'])
            ->allowedIncludes(['user']);

        if ($comptes !== null) {
            if ($comptes === 'oui') {
                $query->whereHas('user');
            } elseif ($comptes === 'non') {
                $query->whereDoesntHave('user');
            }
        }

        if ($active !== null) {
            $etat = $active === 'oui' ? EtatEnum::ACTIF->value : EtatEnum::INACTIF->value;
            $query->whereHas('user', function ($query) use ($etat) {
                $query->where('etat', $etat);
            });
        }

        $clients = $query->get();

        return ResponseHelper::sendOk(new ClientCollection($clients), 'Liste des clients récupérée avec succès');
    }

    public function store(StoreClientRequest $request)
    {
        if (!$this->authorize('create', Client::class)) {
            return $this->authorizationFailed();
        }

        try {
            DB::beginTransaction();

            $clientRequest = $request->only('surname', 'adresse', 'telephone');
            $client = Client::create($clientRequest);

            $path = null;
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('photos', 'public');
            }

            if ($request->has('user')) {
                $user = User::create([
                    'nom' => $request->input('user.nom'),
                    'prenom' => $request->input('user.prenom'),
                    'login' => $request->input('user.login'),
                    'password' => bcrypt($request->input('user.password')),
                    'role_id' => $request->input('user.role_id'),
                    'photo' => $path,
                    'etat' => $request->input('user.etat') ?? 'ACTIF',
                ]);

                $user->client()->save($client);
            }

            DB::commit();
            return ResponseHelper::sendCreated(new ClientResource($client), 'Client créé avec succès');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::sendServerError('Erreur lors de la création du client : ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $client = Client::findOrFail($id);
        if (!$this->authorize('view', $client)) {
            return $this->authorizationFailed();
        }

        return ResponseHelper::sendOk(new ClientResource($client), 'Client récupéré avec succès');
    }

    public function update(Request $request, Client $client)
    {
        if (!$this->authorize('update', $client)) {
            return $this->authorizationFailed();
        }

        // Logique de mise à jour du client
        $client->update($request->validated());

        return ResponseHelper::sendOk(new ClientResource($client), 'Client mis à jour avec succès');
    }

    public function destroy(Client $client)
    {
        if (!$this->authorize('delete', $client)) {
            return $this->authorizationFailed();
        }

        $client->delete();

        return ResponseHelper::sendOk(null, 'Client supprimé avec succès');
    }

    public function getByPhoneNumber(Request $request)
    {
        $request->validate([
            'telephone' => 'required|string',
        ]);

        try {
            $client = Client::where('telephone', $request->telephone)->firstOrFail();
            if (!$this->authorize('view', $client)) {
                return $this->authorizationFailed();
            }
            return ResponseHelper::sendOk(new ClientResource($client), 'Client récupéré avec succès');
        } catch (Exception $e) {
            return ResponseHelper::sendNotFound('Client non trouvé avec ce numéro de téléphone');
        }
    }

    public function addAccount(Request $request)
    {
        if (!$this->authorize('create', Client::class)) {
            return $this->authorizationFailed();
        }

        $request->validate([
            'surname' => 'required|string|exists:clients,surname',
            'user.nom' => 'required|string|max:255',
            'user.prenom' => 'required|string|max:255',
            'user.login' => 'required|string|unique:users,login|max:255',
            'user.password' => 'required|string|min:6|confirmed',
            'user.role_id' => 'required|exists:roles,id',
            'user.etat' => 'required|string|in:' . implode(',', array_map(fn($case) => $case->value, EtatEnum::cases())),
        ]);

        try {
            DB::beginTransaction();

            $client = Client::where('surname', $request->surname)->firstOrFail();

            if ($client->user()->exists()) {
                return ResponseHelper::sendBadRequest('Ce client a déjà un compte utilisateur associé.');
            }

            $user = User::create([
                'nom' => $request->user['nom'],
                'prenom' => $request->user['prenom'],
                'login' => $request->user['login'],
                'password' => Hash::make($request->user['password']),
                'role_id' => $request->user['role_id'],
                'etat' => $request->user['etat'],
            ]);

            $client->user()->associate($user);
            $client->save();

            DB::commit();
            return ResponseHelper::sendCreated(new ClientResource($client), 'Compte ajouté au client avec succès');
        } catch (Exception $e) {
            DB::rollBack();
            return ResponseHelper::sendServerError('Erreur lors de l\'ajout du compte au client : ' . $e->getMessage());
        }
    }
}