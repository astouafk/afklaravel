<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Ce sont mes routes d'authentification accessibles sans authentification
Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Mes routes protégées par auth:api et blacklisted
    Route::middleware(['auth:api', 'blacklisted'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', function (Request $request) {
            return $request->user();
        });
        Route::apiResource('/users', UserController::class);

        Route::post('/clients/telephone', [ClientController::class, 'getByPhoneNumber']);
        Route::apiResource('/clients', ClientController::class)->only(['index', 'store', 'show']);
        Route::post('/clients/register', [ClientController::class, 'addAccount']);

        Route::apiResource('/articles', ArticleController::class);
        Route::prefix('/articles')->group(function () {
            Route::get('/trashed', [ArticleController::class, 'trashed']);
            Route::patch('/{id}/restore', [ArticleController::class, 'restore']);
            Route::post('/libelle', [ArticleController::class, 'getByLibelle']);
            Route::delete('/{id}/force-delete', [ArticleController::class, 'forceDelete']);
            Route::post('/stock', [ArticleController::class, 'updateMultiple']);
        });
    });
});