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
        $mediaUrl = (string) $this->media_url;
        $path = parse_url($mediaUrl, PHP_URL_PATH);

        if (is_string($path)) {
            if (Str::startsWith($path, '/storage/')) {
                return '/media/'.ltrim(Str::after($path, '/storage/'), '/');
            }

            if (Str::startsWith($path, '/media/')) {
                return $path;
            }
        }

        if (Str::startsWith($mediaUrl, '/storage/')) {
            return '/media/'.ltrim(Str::after($mediaUrl, '/storage/'), '/');
        }

        if (Str::startsWith($mediaUrl, '/media/')) {
            return $mediaUrl;
        }

        return $mediaUrl;
    }

    public function getMediaMimeTypeAttribute(): ?string
    {
        $path = parse_url($this->media_source, PHP_URL_PATH) ?: $this->media_source;
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'mp4', 'm4v' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg', 'ogv' => 'video/ogg',
            'mov', 'qt' => 'video/quicktime',
            default => null,
        };
    }

    public function likedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->likes->contains('user_id', $user->id);
    }

}
