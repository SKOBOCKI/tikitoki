<?php

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the for you feed is visible', function () {
    $creator = User::factory()->create();

    Post::create([
        'user_id' => $creator->id,
        'caption' => 'First clip',
        'media_type' => 'video',
        'media_url' => 'https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4',
        'song_title' => 'test loop',
    ]);

    $response = $this->get('/fyp');

    $response->assertStatus(200);
    $response->assertSee('First clip');
});

test('users can register and see the following feed', function () {
    $response = $this->post('/register', [
        'name' => 'New Creator',
        'username' => 'newcreator',
        'email' => 'new@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertRedirect('/fyp');
    $this->assertAuthenticated();

    $this->get('/following')
        ->assertStatus(200)
        ->assertSee('No subscribed posts yet');
});
