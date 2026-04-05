<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MovieList extends Model
{
    protected $fillable = [
        'name',
        'is_public',
        'owner',
        'slug',
    ];

    protected $with = ['listOwner'];

    public function listOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner');
    }

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class);
    }

    public function getUserRole($userId)
    {
        return $this->collaborators()
            ->where('user_id', $userId)
            ->first()
            ?->pivot
            ->role;
    }

    public function collaborators(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'movie_list_user')
            ->withPivot('role');
    }
}
