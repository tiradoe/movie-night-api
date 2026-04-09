<?php

namespace App\Http\Resources;

use App\Models\User;
use Auth;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $name
 * @property bool $is_public
 * @property int $owner
 * @property User $listOwner
 * @property Collection $collaborators
 * @property Collection $movies
 *
 * @method string|null getUserRole(int $user_id)
 */
class MovieListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user_id = Auth::id();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'is_public' => $this->is_public,
            'owner' => $this->listOwner->username,
            'role' => $this->getRole($this->owner, $user_id),
            'collaborators' => $this->whenLoaded('collaborators', fn () => $this->collaborators->map(fn (User $user) => [
                'id' => $user->getKey(),
                'username' => $user->username,
                'role' => $user->pivot->getAttribute('role_id'),
            ])),
            'movies' => $this->whenLoaded('movies'),
        ];
    }

    private function getRole(int $owner_id, int $user_id): ?string
    {
        if ($owner_id === $user_id) {
            return 'OWNER';
        }

        return $this->getUserRole($user_id);
    }
}
