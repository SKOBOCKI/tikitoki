<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
            $path = $request->file('media_file')->store('posts', 'public');

            $data['media_type'] = 'video';
            $data['media_url'] = '/storage/'.$path;
        }

        unset($data['media_file']);

        $request->user()->posts()->create($data);

        return redirect()->route('feed.fyp')->with('status', 'Posted to the feed.');
    }

    public function media(string $path): BinaryFileResponse
    {
        abort_if(str_contains($path, '..') || str_contains($path, '\\'), 404);

        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404);

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
