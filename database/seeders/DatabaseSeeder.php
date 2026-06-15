<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $creators = collect([
            [
                'name' => 'Mara Lens',
                'username' => 'maralens',
                'email' => 'mara@example.com',
                'bio' => 'Tiny travel edits and city light photos.',
                'avatar_url' => 'https://api.dicebear.com/8.x/initials/svg?seed=Mara%20Lens',
            ],
            [
                'name' => 'Radu Beats',
                'username' => 'radubeats',
                'email' => 'radu@example.com',
                'bio' => 'Loops, coffee, and dance breaks.',
                'avatar_url' => 'https://api.dicebear.com/8.x/initials/svg?seed=Radu%20Beats',
            ],
            [
                'name' => 'Ana Plates',
                'username' => 'anaplates',
                'email' => 'ana@example.com',
                'bio' => 'Fast recipes that still look good.',
                'avatar_url' => 'https://api.dicebear.com/8.x/initials/svg?seed=Ana%20Plates',
            ],
        ])->map(fn (array $creator) => User::factory()->create([
            ...$creator,
            'password' => Hash::make('password'),
        ]));

        $testUser = User::factory()->create([
            'name' => 'Test User',
            'username' => 'tester',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'bio' => 'Demo account. Password: password.',
            'avatar_url' => 'https://api.dicebear.com/8.x/initials/svg?seed=Test%20User',
        ]);

        $posts = [
            [
                'user_id' => $creators[0]->id,
                'caption' => 'A quiet flower clip for the first scroll.',
                'media_type' => 'video',
                'media_url' => 'https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4',
                'song_title' => 'soft morning loop',
            ],
            [
                'user_id' => $creators[1]->id,
                'caption' => 'Practice session, one clean take.',
                'media_type' => 'video',
                'media_url' => 'https://www.w3schools.com/html/mov_bbb.mp4',
                'song_title' => 'studio bounce',
            ],
            [
                'user_id' => $creators[2]->id,
                'caption' => 'Dinner colors in one frame.',
                'media_type' => 'photo',
                'media_url' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=900&q=80',
                'song_title' => 'kitchen timer remix',
            ],
            [
                'user_id' => $creators[0]->id,
                'caption' => 'Cloudy walk, cinematic mood.',
                'media_type' => 'photo',
                'media_url' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80',
                'song_title' => 'slow steps',
            ],
        ];

        collect($posts)->each(function (array $post) use ($testUser): void {
            $created = Post::create($post);
            $created->likes()->create(['user_id' => $testUser->id]);
        });

        $testUser->following()->attach([$creators[0]->id, $creators[2]->id]);
    }
}
