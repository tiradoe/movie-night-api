<?php

use App\Http\Controllers\MovieController;
use App\Http\Controllers\MovieListController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// MOVIES
Route::get('/movies/search', [MovieController::class, 'search'])->name('movies.search');

// MOVIE LISTS
Route::get('/movielists/{movieList}', [MovieListController::class, 'show'])->name('movielists.show');
Route::post('/movielists', [MovieListController::class, 'store'])->name('movielists.store');
Route::delete('/movielists/{movieList}', [MovieListController::class, 'destroy'])->name('movielists.destroy');
