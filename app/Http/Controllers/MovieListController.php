<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMovieListRequest;
use App\Models\MovieList;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MovieListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return MovieList::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateMovieListRequest $request)
    {
        $validated = $request->validated();
        $movieList = MovieList::create([
            ...$validated,
            'owner' => 1, // auth()->id(),
            'slug' => Str::slug($validated['name']),
        ]);

        return response()->json($movieList, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MovieList $movieList)
    {
        try {
            return $movieList->load('movies');
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Movie list not found'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MovieList $movieList)
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
        $movieList->delete();
    }
}
