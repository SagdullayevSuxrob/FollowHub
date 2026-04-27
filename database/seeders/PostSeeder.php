<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        if ($users->isEmpty()) {
            $this->command->info("Avval user yarating");
            return;
        }

        foreach ($users as $user) {
            $posts = Post::factory()->count(3)->create([
                'user_id' => $user->id,
            ]);

            foreach ($posts as $post) {
                for ($i = 0; $i < rand(1, 3); $i++) {
                    $post->media()->create([
                        'file_name' => 'test_file_' . $i . '.jpg',
                        'media_path' => 'uploads/posts/sample_' . rand(1, 10),
                    ]);
                }
            }
        }
    }
}
