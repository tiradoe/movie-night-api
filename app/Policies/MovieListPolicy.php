<?php

namespace App\Policies;

use App\Models\MovieList;
use App\Models\User;

class MovieListPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, MovieList $movieList): bool
    {
        if ($movieList->owner === $user->getKey() || $movieList->isPublic) {
            return true;
        }

        return false;
    }

    public function update(User $user, MovieList $movieList): bool
    {
        if ($movieList->owner === $user->getKey()) {
            return true;
        }

        return false;
    }

    public function delete(User $user, MovieList $movieList): bool
    {
        if ($movieList->owner === $user->getKey()) {
            return true;
        }

        return false;
    }
}
