<?php

namespace App\Interfaces;

use App\Data\MovieResult;
use App\Data\MovieSearchResult;
use App\Exceptions\MovieDatabaseException;
use App\Exceptions\MovieNotFoundException;
use Illuminate\Support\Collection;

interface MovieDbInterface
{
    /**
     * Search for movies matching the given query.
     *
     * @param  string  $query  The search term (e.g., a movie title)
     * @return Collection<int, MovieSearchResult>
     *
     * @throws MovieNotFoundException If no movies match the query
     * @throws MovieDatabaseException If the external movie database is unreachable or returns an error
     */
    public function search(string $query): Collection;

    /**
     * Find a specific movie by title or external ID.
     *
     * @param  string  $query  The search value (a movie title or IMDB ID)
     * @param  array<string, mixed>  $options  Search options (e.g., ['type' => 'imdb'] to search by IMDB ID)
     *
     * @throws MovieNotFoundException If the movie cannot be found
     * @throws MovieDatabaseException If the external movie database is unreachable or returns an error
     */
    public function find(string $query, array $options): MovieResult;
}
