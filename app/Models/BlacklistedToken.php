<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlacklistedToken extends Model
{
    use HasFactory;

    // Le nom de la table associée au modèle
    protected $table = 'blacklisted_tokens';

    // Les attributs qui peuvent être assignés en masse
    protected $fillable = ['token', 'type'];

    // Désactive les timestamps si vous n'utilisez pas les colonnes created_at et updated_at
    public $timestamps = true;

    /**
     * Indique si le modèle doit utiliser un format de clé unique.
     *
     * @var bool
     */
    protected $primaryKey = 'id';
}