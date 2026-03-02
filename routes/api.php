<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\MovieListController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn (Request $request) => $request->user());

    // Movies
    Route::get('/movies/search/{query}', [MovieController::class, 'search'])->name('movies.search');

    // Movie Lists
    Route::get('/movielists', [MovieListController::class, 'index'])->name('movielists.index');
    Route::put('/movielists/', [MovieListController::class, 'index'])->name('movielists.index');
    Route::get('/movielists/{movieList}', [MovieListController::class, 'show'])->name('movielists.show');
    Route::post('/movielists', [MovieListController::class, 'store'])->name('movielists.store');
    Route::post('/movielists/{movieList}/movies', [MovieListController::class, 'addMovie'])->name('movielists.addMovie');
    Route::delete('/movielists/{movieList}/movies/{movie}', [MovieListController::class, 'removeMovie'])->name('movielists.removeMovie');
    Route::delete('/movielists/{movieList}', [MovieListController::class, 'destroy'])->name('movielists.destroy');
});
