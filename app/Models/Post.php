<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

#[Fillable(['user_id', 'caption', 'media_type', 'media_url', 'song_title'])]
    class Post extends Model
    {
        use HasFactory;

        public function user()
        {
            return $this->belongsTo(User::class);
        }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function getMediaSourceAttribute(): string
    {
        if (Str::startsWith($this->media_url, '/storage/')) {
            return '/media/'.Str::after($this->media_url, '/storage/');
        }

        $path = parse_url($this->media_url, PHP_URL_PATH);

        if (is_string($path) && Str::startsWith($path, '/storage/')) {
            return '/media/'.Str::after($path, '/storage/');
        }

        return $this->media_url;
    }

    public function likedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->likes->contains('user_id', $user->id);
    }

}
