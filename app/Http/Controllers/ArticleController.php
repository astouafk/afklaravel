<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Article;
use App\Enums\StateEnum;
use Illuminate\Http\Request;
use App\Traits\RestResponseTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Http\Requests\StoreArticleRequest;
use App\Http\Requests\UpdateArticleRequest;

class ArticleController extends Controller
{
    use RestResponseTrait;

    protected function authorizationFailed()
    {
        return $this->sendResponse(null, StateEnum::ECHEC, 'Permission refusée', 403);
    }

    // public function index()
    // {
        

    //     $articles = Article::all();
    //     return $this->sendResponse($articles, StateEnum::SUCCESS, 'Articles récupérés avec succès');
    // }

    public function index(Request $request)
    {
        if (!$this->authorize('viewAny', Article::class)) {
            return $this->authorizationFailed();
        }
        $disponible = $request->query('disponible');

        $query = Article::query();

        // Filtrer par disponibilité en stock
        if ($disponible !== null) {
            if ($disponible === 'oui') {
                $query->where('stock', '>', 0);
            } elseif ($disponible === 'non') {
                $query->where('stock', '=', 0);
            } else {
                return $this->sendResponse(null, StateEnum::ECHEC, 'Valeur de filtre "disponible" invalide', 422);
            }
        }

        $articles = $query->get();

        return $this->sendResponse($articles, StateEnum::SUCCESS, 'Articles récupérés avec succès');
    }

    public function getByLibelle(Request $request)
{
    if (!$this->authorize('viewAny', Article::class)) {
        return $this->authorizationFailed();
    }

    $libelle = $request->input('libelle');

    if (empty($libelle)) {
        return $this->sendResponse(null, StateEnum::ECHEC, 'Le libelle est requis', 422);
    }

    $article = Article::where('libelle', $libelle)->first();

    if (!$article) {
        return $this->sendResponse(null, StateEnum::ECHEC, 'Article non trouvé', 404);
    }

    // Now, authorize the specific article
    if (!$this->authorize('view', $article)) {
        return $this->authorizationFailed();
    }

    return $this->sendResponse($article, StateEnum::SUCCESS, 'Article récupéré avec succès');
}


    public function store(StoreArticleRequest $request)
    {
        if (!$this->authorize('create', Article::class)) {
            return $this->authorizationFailed();
        }

        $validatedData = $request->validated();

        if (empty($validatedData)) {
            return $this->sendResponse(null, StateEnum::ECHEC, 'Au moins un article est requis', 422);
        }

        $article = Article::create($validatedData);

        return $this->sendResponse($article, StateEnum::SUCCESS, 'Article créé avec succès', 201);
    }

    public function show(Article $article)
    {
        if (!$this->authorize('view', $article)) {
            return $this->authorizationFailed();
        }

        return $this->sendResponse($article, StateEnum::SUCCESS, 'Article récupéré avec succès');
    }

    public function update(UpdateArticleRequest $request, Article $article)
    {
        if (!$this->authorize('update', $article)) {
            return $this->authorizationFailed();
        }

        $validatedData = $request->validated();

        if (empty($validatedData)) {
            return $this->sendResponse(null, StateEnum::ECHEC, 'Au moins un champ d\'article est requis pour la mise à jour', 422);
        }

        if (isset($validatedData['stock'])) {
            $validatedData['stock'] = $article->stock + $validatedData['stock'];
        }

        $article->update($validatedData);

        return $this->sendResponse($article, StateEnum::SUCCESS, 'Article mis à jour avec succès');
    }

    public function destroy(Article $article)
    {
        if (!$this->authorize('delete', $article)) {
            return $this->authorizationFailed();
        }

        $article->delete();
        return $this->sendResponse(null, StateEnum::SUCCESS, 'Article supprimé avec succès');
    }

    public function trashed()
    {
        if (!$this->authorize('viewAny', Article::class)) {
            return $this->authorizationFailed();
        }

        $trashedArticles = Article::onlyTrashed()->get();
        return $this->sendResponse($trashedArticles, StateEnum::SUCCESS, 'Articles supprimés récupérés avec succès');
    }

    public function restore($id)
    {
        $article = Article::withTrashed()->findOrFail($id);
        
        if (!$this->authorize('restore', $article)) {
            return $this->authorizationFailed();
        }

        if (!$article->trashed()) {
            return $this->sendResponse(null, StateEnum::ECHEC, 'Cet article n\'est pas dans la corbeille', 400);
        }
        $article->restore();
        return $this->sendResponse($article, StateEnum::SUCCESS, 'Article restauré avec succès');
    }

    public function forceDelete($id)
    {
        $article = Article::withTrashed()->findOrFail($id);
        
        if (!$this->authorize('forceDelete', $article)) {
            return $this->authorizationFailed();
        }

        if (!$article->trashed()) {
            return $this->sendResponse(null, StateEnum::ECHEC, 'Vous ne pouvez pas supprimer définitivement un article qui n\'est pas dans la corbeille', 400);
        }
        $article->forceDelete();
        return $this->sendResponse(null, StateEnum::SUCCESS, 'Article supprimé définitivement');
    }

    public function updateMultiple(Request $request)
    {
        if (!$this->authorize('updateAny', Article::class)) {
            return $this->authorizationFailed();
        }

        $articlesToUpdate = $request->articles;

        if (empty($articlesToUpdate)) {
            return $this->sendResponse(null, StateEnum::ECHEC, 'Au moins un article est requis pour la mise à jour multiple', 422);
        }

        $ids = array_column($articlesToUpdate, 'id');
        $duplicateIds = array_filter(array_count_values($ids), function($count) {
            return $count > 1;
        });

        if (!empty($duplicateIds)) {
            $duplicateMessage = "Les ID suivants apparaissent plusieurs fois : " . implode(', ', array_keys($duplicateIds));
            return $this->sendResponse(['duplicate_ids' => $duplicateIds], StateEnum::ECHEC, $duplicateMessage, 422);
        }

        $updatedArticles = [];
        $failedUpdates = [];

        DB::beginTransaction();

        try {
            foreach ($articlesToUpdate as $articleData) {
                try {
                    if (!isset($articleData['id'])) {
                        throw new \Exception("L'ID de l'article est manquant");
                    }

                    $article = Article::find($articleData['id']);
                    if (!$article) {
                        throw new \Exception("Article avec l'ID {$articleData['id']} introuvable");
                    }

                    if (!$this->authorize('update', $article)) {
                        throw new \Exception("Non autorisé à mettre à jour l'article avec l'ID {$articleData['id']}");
                    }

                    $updateRequest = new UpdateArticleRequest();
                    $updateRequest->replace($articleData);
                    $validatedData = $updateRequest->validate($updateRequest->rules());

                    if (empty($validatedData)) {
                        throw new \Exception("Au moins un champ d'article est requis pour la mise à jour");
                    }

                    if (isset($validatedData['stock'])) {
                        $newStock = $article->stock + $validatedData['stock'];
                        if ($newStock < 0) {
                            throw new \Exception("Le stock ne peut pas être négatif");
                        }
                        $validatedData['stock'] = $newStock;
                    }

                    // Mise à jour de l'article valide
                    $article->fill($validatedData);
                    $updatedArticles[] = $article;
                } catch (\Exception $e) {
                    // Enregistrer l'échec de mise à jour
                    $failedUpdates[] = [
                        'article_data' => $articleData,
                        'error_message' => $e->getMessage()
                    ];
                }
            }

            // Enregistrer tous les articles valides
            foreach ($updatedArticles as $article) {
                $article->save();
            }

            DB::commit();

            $status = count($failedUpdates) > 0 ? StateEnum::ECHEC : StateEnum::SUCCESS;
            $message = count($failedUpdates) > 0
                ? 'Certaines mises à jour ont échoué, mais les articles valides ont été mis à jour'
                : 'Toutes les mises à jour ont réussi';
            $httpStatus = count($failedUpdates) > 0 ? 422 : 200;

            return $this->sendResponse([
                'updated_articles' => $updatedArticles,
                'failed_updates' => $failedUpdates
            ], $status, $message, $httpStatus);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendResponse(null, StateEnum::ECHEC, 'Erreur lors de la mise à jour multiple : ' . $e->getMessage(), 500);
        }
    }
}