<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class PostController extends Controller
{
    public function create(): View
    {
        return view('posts.create');
    }

    public function show(Post $post): View
    {
        $post->load([
            'user' => fn ($query) => $query->withCount('followers'),
            'likes',
            'comments.user',
        ])->loadCount(['likes', 'comments']);

        return view('posts.show', [
            'post' => $post,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'caption' => ['required', 'string', 'max:500'],
            'media_type' => ['required', Rule::in(['video', 'photo'])],
            'media_url' => ['nullable', 'required_without:media_file', 'url', 'max:1000'],
            'media_file' => [
                'nullable',
                'file',
                'mimes:mp4,m4v,mov,qt,webm,ogv,ogg',
                'max:102400',
            ],
            'song_title' => ['nullable', 'string', 'max:120'],
        ]);

        if ($request->hasFile('media_file')) {
            $path = $this->storePlayableVideo($request->file('media_file'));

            $data['media_type'] = 'video';
            $data['media_url'] = '/media/'.$path;
        } elseif (! empty($data['media_url'])) {
            $data['media_type'] = $this->guessMediaType($data['media_url']) ?? $data['media_type'];
        }

        unset($data['media_file']);

        $request->user()->posts()->create($data);

        return redirect()->route('feed.fyp')->with('status', 'Posted to the feed.');
    }

    public function media(string $path): BinaryFileResponse
    {
        abort_if(str_contains($path, '..') || str_contains($path, '\\'), 404);

        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            Log::warning('Uploaded media file is missing from the public disk.', [
                'path' => $path,
                'public_disk_root' => config('filesystems.disks.public.root'),
            ]);

            abort(404);
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $contentType = match ($extension) {
            'mov', 'qt' => 'video/quicktime',
            'mp4', 'm4v' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg', 'ogv' => 'video/ogg',
            default => $disk->mimeType($path) ?: 'application/octet-stream',
        };

        return response()->file($disk->path($path), [
            'Accept-Ranges' => 'bytes',
            'Cache-Control' => 'public, max-age=31536000',
            'Content-Type' => $contentType,
        ]);
    }

    private function storePlayableVideo(UploadedFile $file): string
    {
        $ffmpeg = (new ExecutableFinder())->find('ffmpeg');

        if (! $ffmpeg) {
            return $file->store('posts', 'public');
        }

        $disk = Storage::disk('public');
        $path = 'posts/'.Str::uuid().'.mp4';
        $targetPath = $disk->path($path);

        if (! is_dir(dirname($targetPath))) {
            mkdir(dirname($targetPath), 0755, true);
        }

        $process = new Process([
            $ffmpeg,
            '-y',
            '-i',
            $file->getRealPath(),
            '-map',
            '0:v:0',
            '-map',
            '0:a?',
            '-vf',
            'scale=trunc(iw/2)*2:trunc(ih/2)*2',
            '-c:v',
            'libx264',
            '-preset',
            'veryfast',
            '-crf',
            '23',
            '-pix_fmt',
            'yuv420p',
            '-c:a',
            'aac',
            '-b:a',
            '128k',
            '-movflags',
            '+faststart',
            $targetPath,
        ]);
        $process->setTimeout(120);
        $process->run();

        if ($process->isSuccessful() && is_file($targetPath) && filesize($targetPath) > 0) {
            return $path;
        }

        if (is_file($targetPath)) {
            unlink($targetPath);
        }

        return $file->store('posts', 'public');
    }

    private function guessMediaType(string $url): ?string
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $host = str_starts_with($host, 'www.') ? substr($host, 4) : $host;

        if (in_array($host, ['youtube.com', 'm.youtube.com', 'youtu.be', 'vimeo.com'], true)) {
            return 'video';
        }

        $path = parse_url($url, PHP_URL_PATH) ?: $url;
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'mp4', 'm4v', 'mov', 'qt', 'webm', 'ogg', 'ogv' => 'video',
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg' => 'photo',
            default => null,
        };
    }

    public function like(Request $request, Post $post): RedirectResponse
    {
        $like = $post->likes()->where('user_id', $request->user()->id)->first();

        if ($like) {
            $like->delete();
        } else {
            $post->likes()->create(['user_id' => $request->user()->id]);
        }

        return back();
    }

    public function comment(Request $request, Post $post): RedirectResponse
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:500'],
        ]);

        $post->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        return back()->with('status', 'Comment posted.');
    }
}
