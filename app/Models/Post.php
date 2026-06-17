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
        $host = parse_url($mediaUrl, PHP_URL_HOST);

        if ($host === null && is_string($path)) {
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

    public function getMediaEmbedUrlAttribute(): ?string
    {
        $mediaUrl = (string) $this->media_url;
        $host = parse_url($mediaUrl, PHP_URL_HOST);

        if (! is_string($host)) {
            return null;
        }

        $host = Str::lower(Str::replaceStart('www.', '', $host));
        $path = trim((string) parse_url($mediaUrl, PHP_URL_PATH), '/');
        parse_str((string) parse_url($mediaUrl, PHP_URL_QUERY), $query);

        if (in_array($host, ['youtube.com', 'm.youtube.com'], true)) {
            $videoId = $query['v'] ?? null;

            if (! $videoId && Str::startsWith($path, 'shorts/')) {
                $videoId = Str::after($path, 'shorts/');
            }

            if (! $videoId && Str::startsWith($path, 'embed/')) {
                $videoId = Str::after($path, 'embed/');
            }

            if (is_string($videoId) && $videoId !== '') {
                return 'https://www.youtube-nocookie.com/embed/'.urlencode(Str::before($videoId, '/')).'?rel=0&playsinline=1';
            }
        }

        if ($host === 'youtu.be' && $path !== '') {
            return 'https://www.youtube-nocookie.com/embed/'.urlencode(Str::before($path, '/')).'?rel=0&playsinline=1';
        }

        if ($host === 'vimeo.com' && preg_match('/^\d+$/', $path) === 1) {
            return 'https://player.vimeo.com/video/'.$path;
        }

        return null;
    }

    public function getDetectedMediaTypeAttribute(): ?string
    {
        if ($this->media_embed_url) {
            return 'video';
        }

        $path = parse_url((string) $this->media_url, PHP_URL_PATH) ?: (string) $this->media_url;
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'mp4', 'm4v', 'mov', 'qt', 'webm', 'ogg', 'ogv' => 'video',
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg' => 'photo',
            default => null,
        };
    }

    public function getDisplayMediaTypeAttribute(): string
    {
        return $this->detected_media_type ?? $this->media_type;
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
