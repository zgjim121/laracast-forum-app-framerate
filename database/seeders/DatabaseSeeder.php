<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\Topic;
use App\Models\User;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(TopicSeeder::class);
        $topics = Topic::all();

        $users = User::factory(10)->create();


        $posts = Post::factory(200)
            ->withFixture()
            ->has(Comment::factory(15)->recycle($users))
            ->recycle([$users, $topics])
            ->create();

        $zgjim = User::factory()
            ->has(Post::factory(20)->recycle($topics)->withFixture())
            ->has(Comment::factory(100)->recycle($posts))
            ->has(Like::factory()->forEachSequence(
                ...$posts->random(100)->map(fn(Post $post) => ['likeable_id' => $post])
            ))
            ->create([
                'name' => 'zgjim',
                'email' => 'test@example.com',
            ]);
    }
}
