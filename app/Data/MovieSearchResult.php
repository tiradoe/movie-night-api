<?php

namespace App\Data;

readonly class MovieSearchResult
{
    public function __construct(
        public string $title,
        public int $year,
        public string $imdbId,
        public string $type,
        public string $poster,
    ) {}
}
