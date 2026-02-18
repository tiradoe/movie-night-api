<?php

namespace App\Data;

use App\Models\Movie;

readonly class MovieResult
{
    public function __construct(
        public string $imdbId,
        public string $title,
        public ?int $year,
        public ?string $director,
        public ?string $actors,
        public ?string $plot,
        public ?string $genre,
        public ?string $mpaaRating,
        public ?array $criticScores,
        public ?string $poster,
    ) {}

    public static function fromModel(Movie $movie): self
    {
        return new self(
            imdbId: $movie->imdb_id,
            title: $movie->title,
            year: $movie->year,
            director: $movie->director,
            actors: $movie->actors,
            plot: $movie->plot,
            genre: $movie->genre,
            mpaaRating: $movie->mpaa_rating,
            criticScores: $movie->critic_scores,
            poster: $movie->poster,
        );
    }
}
