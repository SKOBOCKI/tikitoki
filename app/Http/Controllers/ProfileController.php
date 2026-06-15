<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->loadCount(['followers', 'following', 'posts']);

        $likedPosts = $user->likes()
            ->with(['post.user', 'post.likes', 'post.comments.user'])
            ->latest()
            ->get()
            ->pluck('post')
            ->filter();

        $subscriptions = $user->following()
            ->withCount(['followers', 'posts'])
            ->latest('subscriptions.created_at')
            ->get();

        return view('profile.show', [
            'user' => $user,
            'likedPosts' => $likedPosts,
            'subscriptions' => $subscriptions,
        ]);
    }

    public function studio(Request $request): View
    {
        return view('profile.studio', [
            'user' => $request->user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'alpha_dash', 'max:40', Rule::unique('users', 'username')->ignore($user->id)],
            'bio' => ['nullable', 'string', 'max:500'],
            'avatar_url' => ['nullable', 'url', 'max:1000'],
            'banner_url' => ['nullable', 'url', 'max:1000'],
            'banner_drawing' => ['nullable', 'string', 'max:1500000'],
            'profile_accent' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'profile_background' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'banner_mode' => ['required', Rule::in(['image', 'drawing'])],
        ]);

        if ($data['banner_mode'] === 'image') {
            $data['banner_drawing'] = null;
        } else {
            $data['banner_url'] = null;
        }

        unset($data['banner_mode']);

        $user->update($data);

        return back()->with('status', 'Profile updated.');
    }
}
