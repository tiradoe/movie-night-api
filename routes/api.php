<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\MovieController;
use App\Http\Controllers\MovieListController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

// Public auth routes
Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');
Route::get('/invitations/{token}/accept', [InvitationController::class, 'accept'])->name('invitations.accept');
Route::get('/invitations/{token}/decline', [InvitationController::class, 'decline'])->name('invitations.decline');

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset-password');

    // Invitations
    Route::post('/invitations', [InvitationController::class, 'store'])->name('invitations.store');

    // Movies
    Route::get('/movies/search/{query}', [MovieController::class, 'search'])->name('movies.search');

    // Movie Lists
    Route::get('/movielists', [MovieListController::class, 'index'])->name('movielists.index');
    Route::put('/movielists/{movieList}', [MovieListController::class, 'update'])->name('movielists.update');
    Route::get('/movielists/{movieList}', [MovieListController::class, 'show'])->name('movielists.show');
    Route::post('/movielists', [MovieListController::class, 'store'])->name('movielists.store');
    Route::post('/movielists/{movieList}/movies', [MovieListController::class, 'addMovie'])->name('movielists.addMovie');
    Route::delete('/movielists/{movieList}/movies/{movie}', [MovieListController::class, 'removeMovie'])->name('movielists.removeMovie');
    Route::delete('/movielists/{movieList}', [MovieListController::class, 'destroy'])->name('movielists.destroy');
    Route::patch('/movielists/{movieList}/collaborators/{collaborator}', [MovieListController::class, 'updateCollaboratorRole'])->name('movielists.updateCollaboratorRole');

    // Roles
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
});
