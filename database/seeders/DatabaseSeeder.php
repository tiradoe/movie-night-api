<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (config('app.env') === 'local') {
            User::factory()->create([
                'username' => 'testy_mctestface',
                'email' => 'test@example.com',
            ]);
        }

        Role::factory()->createMany([
            [
                'name' => 'ADMIN',
                'display_name' => 'Administrator',
                'description' => 'Can make any changes to the list including deleting it. Can also invite other users to collaborate on this list.'],
            [
                'name' => 'EDITOR',
                'display_name' => 'Editor',
                'description' => 'Can edit list details and can add/remove movies from the list.'],
            [
                'name' => 'VIEWER',
                'display_name' => 'Viewer',
                'description' => 'Can view the list, but cannot make any changes.',
            ],
        ]);
    }
}
