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
        return $movieList->is_public
            || $user->isListOwner($movieList)
            || $user->sharedLists->contains($movieList);
    }

    public function delete(User $user, MovieList $movieList): bool
    {
        return $user->isListOwner($movieList);
    }

    public function editMovies(User $user, MovieList $movieList): bool
    {
        return $user->isListEditor($movieList);
    }

    public function update(User $user, MovieList $movieList): bool
    {
        return $user->isListAdmin($movieList);
    }
}
