<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedController extends Controller
{
    private function feedQuery()
    {
        return Post::query()
            ->with(['user' => fn ($query) => $query->withCount('followers'), 'likes', 'comments.user'])
            ->withCount(['likes', 'comments']);
    }

    public function fyp(Request $request): View
    {
        $followingCreatorIds = $request->user()
            ? $request->user()->following()->pluck('users.id')->all()
            : [];

        $posts = $this->feedQuery()
            ->latest()
            ->get();

        return view('feed.index', [
            'activeFeed' => 'fyp',
            'followingCreatorIds' => $followingCreatorIds,
            'posts' => $posts,
        ]);
    }

    public function following(Request $request): View
    {
        $followingCreatorIds = $request->user()->following()->pluck('users.id')->all();

        $posts = $this->feedQuery()
            ->whereIn('user_id', $followingCreatorIds)
            ->latest()
            ->get();

        return view('feed.index', [
            'activeFeed' => 'following',
            'followingCreatorIds' => $followingCreatorIds,
            'posts' => $posts,
        ]);
    }

    public function search(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));

        $posts = $query === ''
            ? collect()
            : $this->feedQuery()
                ->where(function ($builder) use ($query) {
                    $builder
                        ->where('caption', 'like', "%{$query}%")
                        ->orWhere('song_title', 'like', "%{$query}%")
                        ->orWhereHas('user', function ($userQuery) use ($query) {
                            $userQuery
                                ->where('name', 'like', "%{$query}%")
                                ->orWhere('username', 'like', "%{$query}%");
                        });
                })
                ->latest()
                ->get();

        return view('feed.search', [
            'posts' => $posts,
            'query' => $query,
        ]);
    }
}
