<?php

namespace App\Services;

use App\Data\MovieResult;
use App\Data\MovieSearchResult;
use App\Exceptions\MovieDatabaseException;
use App\Exceptions\MovieNotFoundException;
use App\Interfaces\MovieDbInterface;
use App\Models\Movie;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class OmdbMovieService implements MovieDbInterface
{
    private string $url;

    private string $apiKey;

    /**
     * Initialize the OMDB service with API credentials
     *
     * @throws MovieDatabaseException If API URL or key is not configured
     */
    public function __construct()
    {
        $apiUrl = config('services.omdb.api_url');
        $apiKey = config('services.omdb.api_key');

        if (! $apiUrl || ! $apiKey) {
            throw new MovieDatabaseException('Could not authenticate with external movie database.');
        }

        $this->url = $apiUrl;
        $this->apiKey = $apiKey;
    }

    /**
     * {@inheritDoc}
     *
     * @throws ConnectionException If connection to OMDB fails
     */
    public function find(string $query, array $options = []): MovieResult
    {
        $searchType = $options['type'] ?? 'title';

        return match ($searchType) {
            'imdb' => $this->findByImdbId($query),
            'title' => $this->findByTitle($query),
            default => $this->findByTitle($query),
        };
    }

    /**
     * Find a movie by IMDB ID, checking the local database first then fetching from OMDB
     *
     * @param  string  $imdbId  The IMDB ID to search for
     * @return MovieResult The found movie details
     *
     * @throws ConnectionException If connection to OMDB fails
     * @throws MovieDatabaseException If OMDB API returns an error
     * @throws MovieNotFoundException If the movie is not found
     */
    private function findByImdbId(string $imdbId): MovieResult
    {
        $movie = Movie::query()->where('imdb_id', $imdbId)->first();

        if ($movie) {
            return MovieResult::fromModel($movie);
        }

        $result = $this->makeOmdbRequest(['apikey' => $this->apiKey, 'i' => $imdbId, 'type' => 'movie']);
        $movieDetails = $this->mapToMovieResult($result);

        Movie::create([
            'title' => $movieDetails->title,
            'imdb_id' => $movieDetails->imdbId,
            'year' => $movieDetails->year,
            'director' => $movieDetails->director,
            'actors' => $movieDetails->actors,
            'plot' => $movieDetails->plot,
            'genre' => $movieDetails->genre,
            'mpaa_rating' => $movieDetails->mpaaRating,
            'critic_scores' => $movieDetails->criticScores,
            'poster' => $movieDetails->poster,
            'added_by' => auth()->id(),
        ]);

        return $movieDetails;
    }

    /**
     * Make an HTTP request to the OMDB API
     *
     * @param  array<string, mixed>  $params  Query parameters for the OMDB API request
     * @return array<string, mixed> The JSON response from OMDB
     *
     * @throws ConnectionException If connection to OMDB fails or times out
     * @throws MovieDatabaseException If OMDB API returns an error response
     * @throws MovieNotFoundException If OMDB indicates the movie was not found
     */
    private function makeOmdbRequest(array $params): array
    {
        try {
            $result = Http::get($this->url, $params)
                ->onError(fn () => throw new MovieDatabaseException('Could not fetch movie details from external database.'))
                ->json();
        } catch (ConnectionException) {
            throw new MovieDatabaseException('Could not connect to external movie database.');
        }

        if ($result['Response'] !== 'True') {
            throw new MovieNotFoundException;
        }

        return $result;
    }

    /**
     * Map OMDB API response to MovieResult data object
     *
     * @param  array<string, mixed>  $result  The OMDB API response data
     * @return MovieResult The mapped movie result object
     */
    private function mapToMovieResult(array $result): MovieResult
    {
        return new MovieResult(
            imdbId: $result['imdbID'],
            title: $result['Title'],
            year: $result['Year'],
            director: $result['Director'],
            actors: $result['Actors'],
            plot: $result['Plot'],
            genre: $result['Genre'],
            mpaaRating: $result['Rated'],
            criticScores: $result['Ratings'],
            poster: $result['Poster'],
        );
    }

    /**
     * Find a movie by title, checking the local database first, then fetching from OMDB
     *
     * @param  string  $title  The movie title to search for
     * @return MovieResult The found movie details
     *
     * @throws ConnectionException If connection to OMDB fails
     * @throws MovieDatabaseException If OMDB API returns an error
     * @throws MovieNotFoundException If the movie is not found
     */
    private function findByTitle(string $title): MovieResult
    {
        $movie = Movie::query()->where('title', $title)->first();

        if ($movie) {
            return MovieResult::fromModel($movie);
        }

        $movieResult = $this->makeOmdbRequest(['apikey' => $this->apiKey, 't' => $title]);
        $movieDetails = $this->mapToMovieResult($movieResult);

        Movie::create([
            'title' => $movieDetails->title,
            'imdb_id' => $movieDetails->imdbId,
            'year' => $movieDetails->year,
            'director' => $movieDetails->director,
            'actors' => $movieDetails->actors,
            'plot' => $movieDetails->plot,
            'genre' => $movieDetails->genre,
            'mpaa_rating' => $movieDetails->mpaaRating,
            'critic_scores' => $movieDetails->criticScores,
            'poster' => $movieDetails->poster,
            'added_by' => auth()->id(),
        ]);

        return $movieDetails;
    }

    /**
     * {@inheritDoc}
     *
     * @throws ConnectionException If connection to OMDB fails
     */
    public function search(string $query): Collection
    {
        return $this->searchByTitle($query);
    }

    /**
     * Search for movies by title from OMDB API
     *
     * @param  string  $title  The movie title to search for
     * @return Collection<int, MovieSearchResult> Collection of matching movie search results
     *
     * @throws ConnectionException If connection to OMDB fails
     * @throws MovieDatabaseException If OMDB API returns an error
     * @throws MovieNotFoundException If no movies are found
     */
    private function searchByTitle(string $title): Collection
    {
        $searchResults = $this->makeOmdbRequest(['apikey' => $this->apiKey, 's' => $title, 'type' => 'movie']);

        return collect($searchResults['Search'] ?? [])
            ->map(fn ($movie) => new MovieSearchResult(
                title: $movie['Title'],
                year: $movie['Year'],
                imdbId: $movie['imdbID'],
                type: $movie['Type'],
                poster: $movie['Poster']
            ));
    }
}
