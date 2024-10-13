<?php

namespace Database\Seeders;

use App\Models\SystemAdmin;
use App\Models\Team;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $systemAdmin = User::factory()->create([
            'name' => 'System Admin',
            'email' => 'system_admin@example.com',
        ]);

        $systemAdmin->systemAdminship()->create([]);

        $team1 = Team::create(['name' => 'Team 1']);
        Team::create(['name' => 'Team 2']);

        $teamAdmin1 = User::factory()->create([
            'name' => 'Team Admin 1',
            'email' => 'team_admin1@example.com',
        ]);

        $teamMember1 = User::factory()->create([
            'name' => 'Team Member 1',
            'email' => 'team_member1@example.com',
        ]);

        $team1->admins()->attach($teamAdmin1->id);
        $team1->members()->sync([$teamMember1->id, $teamAdmin1->id]);
    }
}