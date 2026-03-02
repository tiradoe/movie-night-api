<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @method static self create(array $attributes)
 */
class Movie extends Model
{
    protected $fillable = [
        'title',
        'imdb_id',
        'year',
        'director',
        'actors',
        'plot',
        'genre',
        'mpaa_rating',
        'critic_scores',
        'poster',
        'added_by',
    ];

    protected function casts(): array
    {
        return [
            'critic_scores' => 'array',
        ];
    }
}
