<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Showing extends Model
{
    protected $fillable = [
        'is_public',
        'showtime',
        'movie_id',
        'owner_id',
        'schedule_id',
    ];
}
