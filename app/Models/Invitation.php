<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invitation extends Model
{
    const EXPIRATION_DAYS = 7;

    protected $fillable = [
        'email',
        'token',
        'movie_list_id',
        'status',
        'expires_at',
    ];
}
