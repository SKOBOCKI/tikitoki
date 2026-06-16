<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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

    public function getLocalMediaPathAttribute(): ?string
    {
        if (! is_string($this->media_url) || $this->media_url === '') {
            return null;
        }

        $path = parse_url($this->media_url, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return null;
        }

        if (Str::startsWith($path, '/storage/')) {
            $localPath = Str::after($path, '/storage/');
        } elseif (Str::startsWith($path, '/media/')) {
            $localPath = Str::after($path, '/media/');
        } else {
            return null;
        }

        $localPath = ltrim($localPath, '/');

        if ($localPath === '' || str_contains($localPath, '..') || str_contains($localPath, '\\')) {
            return null;
        }

        return $localPath;
    }

    public function getMediaAvailableAttribute(): bool
    {
        $localPath = $this->local_media_path;

        return $localPath === null || Storage::disk('public')->exists($localPath);
    }

    public function getMediaSourceAttribute(): string
    {
        if ($this->local_media_path !== null) {
            return '/media/'.$this->local_media_path;
        }

        return $this->media_url ?? '';
    }

    public function likedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->likes->contains('user_id', $user->id);
    }

}
