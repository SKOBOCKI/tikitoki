<x-layouts.app title="Profile | TikiToki">
    @php
        $avatar = $user->avatar_url ?? 'https://api.dicebear.com/8.x/initials/svg?seed='.urlencode($user->name);
        $banner = $user->banner_drawing ?: $user->banner_url;
    @endphp

    <section class="profile-page creator-profile" style="--profile-accent: {{ $user->profile_accent ?? '#ff2d55' }}; --profile-bg: {{ $user->profile_background ?? '#0a0a0a' }};">
        <nav class="profile-nav" aria-label="Profile navigation">
            <a href="{{ route('feed.fyp') }}" class="brand-link">TikiToki</a>
            <div>
                <a href="{{ route('feed.fyp') }}">For You</a>
                <a href="{{ route('feed.search') }}">Search</a>
                <a href="{{ route('profile.studio') }}">Edit</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">Log out</button>
                </form>
            </div>
        </nav>

        @if (session('status'))
            <div class="status-toast">{{ session('status') }}</div>
        @endif

        <header class="creator-hero">
            <div class="creator-cover" @if ($banner) style="background-image: linear-gradient(180deg, transparent 45%, rgb(10 10 10 / 0.74)), url('{{ $banner }}')" @endif></div>

            <div class="creator-card">
                <img class="creator-avatar" src="{{ $avatar }}" alt="{{ $user->name }}">

                <div class="creator-copy">
                    <p>{{ '@'.$user->username }}</p>
                    <h1>{{ $user->name }}</h1>
                    <span>{{ $user->bio ?: 'No bio yet.' }}</span>
                </div>

                <div class="creator-actions">
                    <a class="studio-button primary-profile-action" href="{{ route('profile.studio') }}">Edit profile</a>
                    <a class="studio-button" href="{{ route('posts.create') }}">Add video</a>
                </div>
            </div>

            <div class="creator-stats" aria-label="Profile stats">
                <span><strong>{{ $user->posts_count }}</strong>Posts</span>
                <span><strong>{{ $user->followers_count }}</strong>Subscribers</span>
                <span><strong>{{ $user->following_count }}</strong>Following</span>
            </div>
        </header>

        <main class="creator-content">
            <section class="creator-section">
                <div class="creator-section-head">
                    <div class="panel-heading">
                        <p>Library</p>
                        <h2>Liked clips</h2>
                    </div>
                    <span>{{ $likedPosts->count() }} saved</span>
                </div>

                <div class="creator-video-grid">
                    @forelse ($likedPosts as $post)
                        <a class="creator-video-card" href="{{ route('posts.show', $post) }}">
                            <div class="creator-video-media">
                                @if ($post->media_type === 'video')
                                    <video muted playsinline preload="metadata">
                                        <source src="{{ $post->media_source }}" @if ($post->media_mime_type) type="{{ $post->media_mime_type }}" @endif>
                                    </video>
                                @else
                                    <img src="{{ $post->media_source }}" alt="{{ $post->caption }}">
                                @endif
                                <span>{{ ucfirst($post->media_type) }}</span>
                            </div>

                            <div class="creator-video-body">
                                <strong>{{ '@'.$post->user->username }}</strong>
                                <p>{{ Str::limit($post->caption, 72) }}</p>
                                <small>{{ $post->likes->count() }} likes · {{ $post->comments->count() }} comments</small>
                            </div>
                        </a>
                    @empty
                        <div class="profile-empty-state">
                            <h2>No liked clips yet</h2>
                            <p>Tap the heart on videos you want to keep close.</p>
                        </div>
                    @endforelse
                </div>
            </section>

            <aside class="creator-section creator-people">
                <div class="creator-section-head">
                    <div class="panel-heading">
                        <p>People</p>
                        <h2>Subscribed to</h2>
                    </div>
                </div>

                <div class="subscription-list">
                    @forelse ($subscriptions as $creator)
                        <article class="subscription-row">
                            <img src="{{ $creator->avatar_url ?? 'https://api.dicebear.com/8.x/initials/svg?seed='.urlencode($creator->name) }}" alt="{{ $creator->name }}">
                            <div>
                                <strong>{{ $creator->name }}</strong>
                                <p>{{ '@'.$creator->username }} · {{ $creator->followers_count }} subscribers · {{ $creator->posts_count }} posts</p>
                            </div>
                            <form method="POST" action="{{ route('users.subscribe', $creator) }}">
                                @csrf
                                <button type="submit">Subscribed</button>
                            </form>
                        </article>
                    @empty
                        <div class="profile-empty-state compact">
                            <h2>No subscriptions</h2>
                            <p>Creators you subscribe to will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </aside>
        </main>

        <x-bottom-nav active="profile" />
    </section>
</x-layouts.app>
