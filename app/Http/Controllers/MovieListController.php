<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMovieListRequest;
use App\Http\Requests\UpdateMovieListRequest;
use App\Interfaces\MovieDbInterface;
use App\Models\Movie;
use App\Models\MovieList;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MovieListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        return response()->json([
            'movie_lists' => $user->movieLists,
            'shared_lists' => $user->sharedLists,
        ], 200);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateMovieListRequest $request)
    {
        $this->authorize('create', MovieList::class);

        $validated = $request->validated();
        $movieList = MovieList::create([
            ...$validated,
            'owner' => auth()->id(),
            'slug' => Str::slug($validated['name']),
        ]);

        return response()->json($movieList, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MovieList $movieList)
    {
        $this->authorize('view', $movieList);
        try {
            return $movieList->load('movies');
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Movie list not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMovieListRequest $request, MovieList $movieList)
    {
        $validated = $request->validated();
        $movieList->update($validated);

        return response()->json($movieList, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MovieList $movieList)
    {
        $this->authorize('delete', $movieList);
        $movieList->delete();
    }

    public function addMovie(MovieDbInterface $movieDb, Request $request, MovieList $movieList)
    {
        $this->authorize('update', $movieList);
        $movieResult = $movieDb->find($request->input('movie')['imdbId'], ['type' => 'imdb']);
        $movie = Movie::where('imdb_id', $movieResult->imdbId)->first();

        $movieList->movies()->attach($movie);
        $movieList->load('movies');

        return response()->json($movieList);
    }

    public function removeMovie(MovieDbInterface $movieDb, Request $request, MovieList $movieList, Movie $movie)
    {
        $this->authorize('update', $movieList);

        $movieList->movies()->detach($movie);
        $movieList->load('movies');

        return response()->json($movieList);
    }
}
