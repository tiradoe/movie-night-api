<?php

namespace App\Console\Commands;

use App\Models\Movie;
use App\Models\MovieList;
use App\Models\Schedule;
use App\Models\Showing;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class django_import extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mn:django_import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports data from Django version of Movie Night from exported table CSVs. Nobody else should need this.';

    private $csvPath = 'dbbackups/';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->wipeTables();

        $this->importMovies();
        $this->importMovielists();
        $this->importMovieListMovies();
        $this->importSchedules();
        $this->importShowings();
    }

    private function wipeTables()
    {
        $this->info('Truncating tables...');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('movie_movie_list')->truncate();
        DB::table('movies')->truncate();
        DB::table('movie_lists')->truncate();
        DB::table('schedules')->truncate();
        DB::table('showings')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    private function importMovies($fileName = 'moviemanager_movies.csv'): void
    {
        $this->info('Importing movies...');

        $file = fopen(storage_path($this->csvPath.$fileName), 'r');
        if (! $file) {
            $this->error('File not found: '.$fileName);

            return;
        }

        // Skip the header row
        fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            Movie::forceCreate([
                'id' => $row[0],
                'title' => $row[1],
                'imdb_id' => $row[2],
                'year' => $row[3],
                'director' => $row[4],
                'actors' => $row[5],
                'plot' => $row[6],
                'genre' => $row[7],
                'mpaa_rating' => $row[8],
                'critic_scores' => $this->parsePythonList($row[9]),
                'poster' => $row[10],
                'added_by' => $row[11],
            ]);
        }

        fclose($file);

    }

    /**
     * Convert a Python-style list string (single-quoted) to a PHP array.
     *
     * @return array<int, array<string, string>>
     */
    private function parsePythonList(string $value): array
    {
        if (empty($value) || $value === '[]') {
            return [];
        }

        $json = str_replace("'", '"', $value);
        if (str_starts_with($json, '{')) {
            // Fixes incorrect key for Source in some older data
            $json = str_replace('Score', 'Source', $json);
            $json = '['.$json.']';

        }
        $decoded = json_decode($json, true);

        if (is_array($decoded)) {
            return $decoded;
        } elseif (is_string($decoded)) {
            return [$decoded];
        }

        return [];
    }

    private function importMovielists($fileName = 'moviemanager_movielist.csv'): void
    {
        $this->info('Importing Movie Lists...');

        $file = fopen(storage_path($this->csvPath.$fileName), 'r');
        if (! $file) {
            $this->error('File not found: '.$fileName);

            return;
        }

        // Skip the header row
        fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            MovieList::create([
                'name' => $row[1],
                'is_public' => $row[2] === 't' ? true : false,
                'slug' => Str::slug($row[1]),
                'owner' => $row[3],
            ]);
        }

        fclose($file);
    }

    private function importMovieListMovies($fileName = 'moviemanager_movielist_movies.csv'): void
    {
        $this->info('Importing movie_list_movies...');
        $file = fopen(storage_path($this->csvPath.$fileName), 'r');
        if (! $file) {
            $this->error('File not found: '.$fileName);

            return;
        }

        // Skip the header row
        fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            $movieList = MovieList::find($row[1]);
            $movie = Movie::find($row[2]);

            if ($movieList && $movie) {
                $movieList->movies()->attach($movie);
            } else {
                $this->error('Movie or MovieList not found.  Movie ID: '.$row[2].', MovieList ID: '.$row[1]);
            }
        }

        fclose($file);
    }

    private function importSchedules($fileName = 'moviemanager_schedule.csv'): void
    {
        $this->info('Importing schedules...');
        $file = fopen(storage_path($this->csvPath.$fileName), 'r');
        if (! $file) {
            $this->error('File not found: '.$fileName);

            return;
        }

        // Skip the header row
        fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            Schedule::create([
                'name' => $row[1],
                'is_public' => $row[2] === 't' ? true : false,
                'slug' => $row[3],
                'owner' => $row[4],
            ]);
        }

        fclose($file);
    }

    private function importShowings($fileName = 'moviemanager_showing.csv'): void
    {
        $this->info('Importing showings...');
        $file = fopen(storage_path($this->csvPath.$fileName), 'r');
        if (! $file) {
            $this->error('File not found: '.$fileName);

            return;
        }

        // Skip the header row
        fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            Showing::create([
                'is_public' => $row[1] === 't' ? true : false,
                'showtime' => Carbon::parse($row[2]),
                'movie_id' => $row[3],
                'owner_id' => $row[4],
                'schedule_id' => $row[5],
            ]);
        }

        fclose($file);
    }
}
