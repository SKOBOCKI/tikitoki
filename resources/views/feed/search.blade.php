<x-layouts.app title="Search | TikiToki">
    @php
        $resultCount = $posts->count();
    @endphp

    <section class="search-page">
        <nav class="profile-nav" aria-label="Search navigation">
            <a href="{{ route('feed.fyp') }}" class="brand-link">TikiToki</a>
            <div>
                <a href="{{ route('feed.fyp') }}">For You</a>
                @auth
                    <a href="{{ route('posts.create') }}">Create</a>
                    <a href="{{ route('profile.show') }}">Profile</a>
                @else
                    <a href="{{ route('login') }}">Log in</a>
                @endauth
            </div>
        </nav>

        <main class="search-layout">
            <section class="search-command" aria-label="Search videos">
                <div class="panel-heading">
                    <p>Explore</p>
                    <h1>Search TikiToki</h1>
                </div>

                <form class="search-hero" role="search" action="{{ route('feed.search') }}" method="GET">
                    <label>
                        <span class="sr-only">Search</span>
                        <div class="search-field">
                            <span class="app-icon icon-search" aria-hidden="true"></span>
                            <input type="search" name="q" value="{{ $query }}" placeholder="Creators, captions, sounds" autocomplete="off" autofocus>
                            <button type="submit">Search</button>
                        </div>
                    </label>
                </form>

                <div class="search-summary" aria-live="polite">
                    @if ($query === '')
                        <span>Ready</span>
                        <strong>Find clips, creators, and sounds</strong>
                    @else
                        <span>{{ $resultCount }} {{ Str::plural('result', $resultCount) }}</span>
                        <strong>{{ '"'.$query.'"' }}</strong>
                    @endif
                </div>
            </section>

            <section class="search-results" aria-label="Search results">
                <div class="search-results-head">
                    <div class="panel-heading">
                        <p>Results</p>
                        <h2>{{ $query === '' ? 'Start searching' : 'For "'.$query.'"' }}</h2>
                    </div>

                    @if ($query !== '')
                        <span>{{ $resultCount }} found</span>
                    @endif
                </div>

                <div class="search-grid">
                    @forelse ($posts as $post)
                        @php
                            $avatar = $post->user->avatar_url ?? 'https://api.dicebear.com/8.x/initials/svg?seed='.urlencode($post->user->name);
                            $mediaType = $post->display_media_type;
                        @endphp

                        <a class="search-card" href="{{ route('posts.show', $post) }}">
                            <div class="search-card-media">
                                @if ($mediaType === 'video')
                                    @if ($post->media_embed_url)
                                        <iframe src="{{ $post->media_embed_url }}" title="{{ '@'.$post->user->username.' video' }}" loading="lazy" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                    @else
                                        <video muted playsinline preload="metadata">
                                            <source src="{{ $post->media_source }}" @if ($post->media_mime_type) type="{{ $post->media_mime_type }}" @endif>
                                        </video>
                                    @endif
                                @else
                                    <img src="{{ $post->media_source }}" alt="{{ $post->caption }}">
                                @endif
                                <span>{{ ucfirst($mediaType) }}</span>
                            </div>

                            <div class="search-card-body">
                                <div class="search-card-creator">
                                    <img src="{{ $avatar }}" alt="{{ $post->user->name }}">
                                    <span>
                                        <strong>{{ '@'.$post->user->username }}</strong>
                                        <small>{{ $post->user->followers_count }} subscribers</small>
                                    </span>
                                </div>
                                <p>{{ Str::limit($post->caption, 90) }}</p>
                                <div class="search-card-meta">
                                    <span>{{ $post->likes_count }} likes</span>
                                    <span>{{ $post->comments_count }} comments</span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="empty-search">
                            <span class="app-icon icon-search" aria-hidden="true"></span>
                            <h2>{{ $query === '' ? 'Search for something' : 'No results found' }}</h2>
                            <p>{{ $query === '' ? 'Try a creator username, caption, or sound title.' : 'Try another word or check the spelling.' }}</p>
                        </div>
                    @endforelse
                </div>
            </section>
        </main>

        <x-bottom-nav active="search" />
    </section>
</x-layouts.app>
