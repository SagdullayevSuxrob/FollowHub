<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class BlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $targets = $users->where('id', '!=', $user->id)
                ->random(rand(1, 2));

            foreach ($targets as $target) {
                $user->blockedUsers()->syncWithoutDetaching([$target->id]);
            }
        }
    }
}
