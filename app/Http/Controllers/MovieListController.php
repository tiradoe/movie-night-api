<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMovieListRequest;
use App\Http\Requests\UpdateMovieListRequest;
use App\Http\Resources\MovieListResource;
use App\Interfaces\MovieDbInterface;
use App\Models\Movie;
use App\Models\MovieList;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MovieListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
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
    public function show(MovieList $movieList): MovieListResource
    {
        $this->authorize('view', $movieList);

        return MovieListResource::make($movieList->load('movies', 'collaborators'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMovieListRequest $request, MovieList $movieList): MovieListResource
    {
        $validated = $request->validated();
        $movieList->update($validated);

        return MovieListResource::make($movieList->load('movies', 'collaborators'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MovieList $movieList): JsonResponse
    {
        $this->authorize('delete', $movieList);
        $movieList->delete();

        return response()->json(['message', 'Movie list deleted successfully'], 204);
    }

    public function addMovie(MovieDbInterface $movieDb, Request $request, MovieList $movieList): MovieListResource
    {
        $this->authorize('update', $movieList);
        $movieResult = $movieDb->find($request->input('movie')['imdbId'], ['type' => 'imdb']);
        $movie = Movie::where('imdb_id', $movieResult->imdbId)->first();

        $movieList->movies()->attach($movie);
        $movieList->load('movies');

        return MovieListResource::make($movieList->load('movies', 'collaborators'));
    }

    public function removeMovie(MovieList $movieList, Movie $movie): MovieListResource
    {
        $this->authorize('update', $movieList);

        $movieList->movies()->detach($movie);
        $movieList->load('movies');

        return MovieListResource::make($movieList->load('movies', 'collaborators'));
    }

    public function updateCollaboratorRole(Request $request, MovieList $movieList, User $collaborator): MovieListResource|JsonResponse
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $adminRole = Role::query()->where('name', 'ADMIN')->first()?->id;
        if (Auth::id() !== $movieList->owner && ! Auth::user()->hasRole($movieList, $adminRole)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $movieList->collaborators()->updateExistingPivot($collaborator->getKey(), [
            'role_id' => $request->input('role_id'),
        ]);

        return MovieListResource::make($movieList->load('movies', 'collaborators'));
    }
}
