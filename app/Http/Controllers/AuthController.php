<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\StateEnum;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\TokenRepository;
use Laravel\Passport\RefreshTokenRepository;
use Laravel\Passport\HasApiTokens;
use App\Models\BlacklistedToken;


class AuthController extends Controller
{
    use HasApiTokens;
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required',
            'password' => 'required',
        ]);

        
        if (!Auth::attempt($request->only('login', 'password'))) {
            
            return $this->sendResponse(null, StateEnum::ECHEC, 'Les identifiants sont incorrects', 401);
        }


        $user = User::where('login', $request->login)->firstOrFail();
        
        
        $tokens = $this->generateTokens($user);



        return $this->sendResponse($tokens, StateEnum::SUCCESS, 'Connexion réussie');
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'required',
        ]);
    
        $user = User::where('refresh_token', $request->refresh_token)->first();
        if (!$user) {
            return $this->sendResponse(null, StateEnum::ECHEC, 'Refresh token invalide', 401);
        }
    
        // Ajouter l'ancien refresh token à la liste noire
        BlacklistedToken::create(['token' => $request->refresh_token, 'type' => 'refresh']);
    
        $this->revokeTokens($user);
        $tokens = $this->generateTokens($user);
        return $this->sendResponse($tokens, StateEnum::SUCCESS, 'Token rafraîchi avec succès');
    }

    public function logout(Request $request)
{
    $token = $request->bearerToken();
    $type = 'access'; // ou 'refresh', selon votre logique

    // Vérifier si le token existe déjà dans la table `blacklisted_tokens`
    $existingToken = BlacklistedToken::where('token', $token)->first();

    if (!$existingToken) {
        // Si le token n'existe pas, l'ajouter à la table des tokens mis sur liste noire
        BlacklistedToken::create([
            'token' => $token,
            'type' => $type,
            'revoked_at' => now(),
        ]);
    } else {
        // Optionnel : Vous pouvez mettre à jour le token existant si nécessaire
        $existingToken->update([
            'revoked_at' => now(),
        ]);
    }

    // Révoquer le token d'accès de l'utilisateur
    $request->user()->token()->revoke();

    return response()->json(['message' => 'Déconnexion réussie'], 200);
}


    private function generateTokens($user)
    {
        $accessToken = $user->createToken('auth_token')->accessToken;
        $refreshToken = Str::random(60);

        $user->update(['refresh_token' => $refreshToken]);

        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
        ];
    }

    private function revokeTokens($user)
    {
        $tokenRepository = app(TokenRepository::class);
        $refreshTokenRepository = app(RefreshTokenRepository::class);
    
        // Ajouter l'access token à la liste noire
        $this->addTokenToBlacklist($user->token()->id, 'access');
    
        // Ajouter le refresh token à la liste noire
        if ($user->refresh_token) {
            $this->addTokenToBlacklist($user->refresh_token, 'refresh');
        }
    
        // Révoquer les tokens avec Passport
        $tokenRepository->revokeAccessToken($user->token()->id);
        $refreshTokenRepository->revokeRefreshTokensByAccessTokenId($user->token()->id);
    }
    

    private function addTokenToBlacklist($token, $type)
    {
        BlacklistedToken::create([
            'token' => $token,
            'type' => $type,
            'revoked_at' => now(),
        ]);
    }


    public function sendResponse($data, $status, $message, $httpStatus = 200)
    {
        return response()->json([
            'data'    => $data,
            'status'  => $status,
            'message' => $message,
        ], $httpStatus);
    }
}
