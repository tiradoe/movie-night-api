<?php

namespace App\Http\Resources;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'collaborators' => $this->whenLoaded('collaborators', fn () => $this->collaborators->map(fn ($user) => [
                'username' => $user->username,
                'role' => $user->pivot->role,
            ])),
            'movies' => $this->whenLoaded('movies'),
        ];
    }

    private function getRole(int $owner_id, int $user_id): ?string
    {
        if ($owner_id === $user_id) {
            return 'owner';
        }

        return $this->getUserRole($user_id);
    }
}
