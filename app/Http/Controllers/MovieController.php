<?php

namespace App\Http\Controllers;

use App\Exceptions\MovieDatabaseException;
use App\Exceptions\MovieNotFoundException;
use App\Interfaces\MovieDbInterface;
use App\Models\Movie;
use Illuminate\Http\Request;

class MovieController extends Controller
{
    public function __construct(private MovieDbInterface $movieDb) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Movie $movie)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Movie $movie)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Movie $movie)
    {
        //
    }

    /**
     * @throws MovieNotFoundException
     * @throws MovieDatabaseException
     */
    public function search(MovieDbInterface $movieDb, Request $request, string $query)
    {
        $movies = $movieDb->search($query, $request->input('options', []));

        return response()->json(['results' => $movies]);
    }
}
