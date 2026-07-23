<?php

namespace Database\Seeders;

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
    \App\Models\User::factory()->create([
        'name' => 'Administrator DKP',
        'email' => 'admin@dkp.jatimprov.go.id',
        'password' => bcrypt('password123'),
        'role' => 'superadmin',
    ]);

    $this->call(ConversationLogSeeder::class);
    $this->call(UnansweredQuestionSeeder::class);
}
}
