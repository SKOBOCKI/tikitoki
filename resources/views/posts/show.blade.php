<x-layouts.app title="Clip | TikiToki">
    @php
        $postUrl = route('posts.show', $post);
        $mediaAvailable = $post->media_available;
    @endphp

    <section class="watch-page">
        <nav class="profile-nav" aria-label="Clip navigation">
            <a href="{{ route('feed.fyp') }}" class="brand-link">TikiToki</a>
            <div>
                @auth
                    <a href="{{ route('profile.show') }}">Profile</a>
                @else
                    <a href="{{ route('login') }}">Log in</a>
                @endauth
                <a href="{{ route('feed.fyp') }}">For You</a>
            </div>
        </nav>

        @if (session('status'))
            <div class="status-toast">{{ session('status') }}</div>
        @endif

        <article class="watch-layout">
            <div class="watch-media">
                @if (! $mediaAvailable)
                    <div class="media-unavailable">
                        <span class="media-unavailable-mark" aria-hidden="true">!</span>
                        <strong>Media unavailable</strong>
                    </div>
                @elseif ($post->media_type === 'video')
                    <video src="{{ $post->media_source }}" controls autoplay playsinline></video>
                @else
                    <img src="{{ $post->media_source }}" alt="{{ $post->caption }}">
                @endif
            </div>

            <aside class="watch-details">
                <div class="watch-creator">
                    <img src="{{ $post->user->avatar_url ?? 'https://api.dicebear.com/8.x/initials/svg?seed='.urlencode($post->user->name) }}" alt="{{ $post->user->name }}">
                    <div>
                        <strong>{{ '@'.$post->user->username }}</strong>
                        <span>{{ $post->user->followers_count }} subscribers</span>
                    </div>
                </div>

                <p>{{ $post->caption }}</p>

                @if ($post->song_title)
                    <div class="sound-line"><span aria-hidden="true">&#9835;</span> {{ $post->song_title }}</div>
                @endif

                <div class="watch-actions">
                    @auth
                        <form method="POST" action="{{ route('posts.like', $post) }}">
                            @csrf
                            <button @class(['watch-action', 'liked' => $post->likedBy(auth()->user())]) type="submit"><span aria-hidden="true">&#9829;</span> {{ $post->likes_count }}</button>
                        </form>
                    @else
                        <a class="watch-action" href="{{ route('login') }}"><span aria-hidden="true">&#9829;</span> {{ $post->likes_count }}</a>
                    @endauth

                    <button class="watch-action" type="button" data-share-url="{{ $postUrl }}" data-share-title="{{ '@'.$post->user->username.' on TikiToki' }}"><span aria-hidden="true">&#8599;</span> <span data-action-label>Share</span></button>
                </div>

            </aside>
        </article>

        <x-bottom-nav active="home" />
    </section>
</x-layouts.app>
