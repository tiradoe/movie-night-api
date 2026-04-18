<?php

namespace Tests\Feature;

use App\Models\MovieList;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateCollaboratorRoleTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $editorRole;

    private Role $viewerRole;

    public function test_role_id_is_required(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $movieList = $this->makeList($owner);
        $movieList->collaborators()->attach($collaborator, ['role_id' => $this->viewerRole->getKey()]);

        $response = $this->actingAs($owner)
            ->patchJson("/api/movielists/{$movieList->getKey()}/collaborators/{$collaborator->getKey()}", []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['role_id']);
    }

    private function makeList(User $owner): MovieList
    {
        return MovieList::create([
            'name' => 'Test List',
            'owner' => $owner->getKey(),
            'slug' => 'test-list',
        ]);
    }

    public function test_role_id_must_exist_in_roles_table(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $movieList = $this->makeList($owner);
        $movieList->collaborators()->attach($collaborator, ['role_id' => $this->viewerRole->getKey()]);

        $response = $this->actingAs($owner)
            ->patchJson("/api/movielists/{$movieList->getKey()}/collaborators/{$collaborator->getKey()}", [
                'role_id' => 9999,
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['role_id']);
    }

    public function test_owner_can_update_collaborator_role(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $movieList = $this->makeList($owner);
        $movieList->collaborators()->attach($collaborator, ['role_id' => $this->viewerRole->getKey()]);

        $response = $this->actingAs($owner)
            ->patchJson("/api/movielists/{$movieList->getKey()}/collaborators/{$collaborator->getKey()}", [
                'role_id' => $this->editorRole->getKey(),
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('movie_list_user', [
            'movie_list_id' => $movieList->getKey(),
            'user_id' => $collaborator->getKey(),
            'role_id' => $this->editorRole->getKey(),
        ]);
    }

    public function test_admin_collaborator_can_update_collaborator_role(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $collaborator = User::factory()->create();
        $movieList = $this->makeList($owner);
        $movieList->collaborators()->attach($admin, ['role_id' => $this->adminRole->getKey()]);
        $movieList->collaborators()->attach($collaborator, ['role_id' => $this->viewerRole->getKey()]);

        $response = $this->actingAs($admin)
            ->patchJson("/api/movielists/{$movieList->getKey()}/collaborators/{$collaborator->getKey()}", [
                'role_id' => $this->editorRole->getKey(),
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('movie_list_user', [
            'movie_list_id' => $movieList->getKey(),
            'user_id' => $collaborator->getKey(),
            'role_id' => $this->editorRole->getKey(),
        ]);
    }

    public function test_non_admin_collaborator_cannot_update_collaborator_role(): void
    {
        $owner = User::factory()->create();
        $editor = User::factory()->create();
        $collaborator = User::factory()->create();
        $movieList = $this->makeList($owner);
        $movieList->collaborators()->attach($editor, ['role_id' => $this->editorRole->getKey()]);
        $movieList->collaborators()->attach($collaborator, ['role_id' => $this->viewerRole->getKey()]);

        $response = $this->actingAs($editor)
            ->patchJson("/api/movielists/{$movieList->getKey()}/collaborators/{$collaborator->getKey()}", [
                'role_id' => $this->editorRole->getKey(),
            ]);

        $response->assertForbidden();
    }

    public function test_admin_collaborator_cannot_update_own_role(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create();
        $movieList = $this->makeList($owner);
        $movieList->collaborators()->attach($admin, ['role_id' => $this->adminRole->getKey()]);

        $response = $this->actingAs($admin)
            ->patchJson("/api/movielists/{$movieList->getKey()}/collaborators/{$admin->getKey()}", [
                'role_id' => $this->editorRole->getKey(),
            ]);

        $response->assertUnprocessable();
        $this->assertDatabaseHas('movie_list_user', [
            'movie_list_id' => $movieList->getKey(),
            'user_id' => $admin->getKey(),
            'role_id' => $this->adminRole->getKey(),
        ]);
    }

    public function test_unrelated_user_cannot_update_collaborator_role(): void
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $stranger = User::factory()->create();
        $movieList = $this->makeList($owner);
        $movieList->collaborators()->attach($collaborator, ['role_id' => $this->viewerRole->getKey()]);

        $response = $this->actingAs($stranger)
            ->patchJson("/api/movielists/{$movieList->getKey()}/collaborators/{$collaborator->getKey()}", [
                'role_id' => $this->editorRole->getKey(),
            ]);

        $response->assertForbidden();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->adminRole = Role::where('name', 'ADMIN')->first();
        $this->editorRole = Role::where('name', 'EDITOR')->first();
        $this->viewerRole = Role::where('name', 'VIEWER')->first();
    }
}
