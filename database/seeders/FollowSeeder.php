<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class FollowSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $otherUsers = User::where('id', '!=', $user->id)->get();

            $randomFollowings = $otherUsers->random(min($otherUsers->count(), rand(3, 5)));

            foreach ($randomFollowings as $following) {
                $status = ($following->type === 'public') ? 'accepted' : 'requested';

                $user->following()->syncWithoutDetaching([
                    $following->id => [
                        'status' => $status,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
            }
        }
    }
}
