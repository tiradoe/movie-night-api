<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    private static $adminRoleId = null;

    private static $editorRoleId = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function movieLists(): HasMany
    {
        return $this->hasMany(MovieList::class, 'owner');
    }

    public function isListEditor(MovieList $movieList): bool
    {
        self::$editorRoleId = Role::query()
            ->where('name', 'EDITOR')
            ->value('id');

        return $this->isListAdmin($movieList) || $this->hasRole($movieList->getKey(), self::$editorRoleId);
    }

    public function isListAdmin(MovieList $movieList): bool
    {
        self::$adminRoleId = Role::query()
            ->where('name', 'ADMIN')
            ->value('id');

        return $this->isListOwner($movieList) || $this->hasRole($movieList->getKey(), self::$adminRoleId);
    }

    public function isListOwner(MovieList $movieList): bool
    {
        return $this->getKey() === $movieList->owner;
    }

    public function hasRole(int $movieListId, int $role): bool
    {
        return $this->sharedLists()
            ->wherePivot('movie_list_id', $movieListId)
            ->wherePivot('role_id', $role)
            ->exists();
    }

    public function sharedLists(): BelongsToMany
    {
        return $this->belongsToMany(MovieList::class)
            ->withPivot('role_id')
            ->withTimestamps();
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'movie_list_user')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
