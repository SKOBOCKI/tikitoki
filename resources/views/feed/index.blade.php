<x-layouts.app title="TikiToki">
    <div class="app-shell">
        <header class="top-bar">
            <a href="{{ route('feed.fyp') }}" class="logo" aria-label="TikiToki home">TikiToki</a>

            <nav class="feed-tabs" aria-label="Feed tabs">
                <a @class(['active' => $activeFeed === 'fyp']) href="{{ route('feed.fyp') }}">For You</a>
                @auth
                    <a @class(['active' => $activeFeed === 'following']) href="{{ route('feed.following') }}">Following</a>
                @else
                    <a href="{{ route('login') }}">Following</a>
                @endauth
            </nav>

            <a class="top-search top-search-link" id="search" href="{{ route('feed.search') }}" aria-label="Open search">
                <span class="app-icon icon-search" aria-hidden="true"></span>
                <strong>Search</strong>
            </a>

            <div class="account-actions">
                @auth
                    <a href="{{ route('profile.show') }}">{{ '@'.auth()->user()->username }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" aria-label="Log out">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Log in</a>
                    <a href="{{ route('register') }}" class="join-link">Join</a>
                @endauth
            </div>
        </header>

        @if (session('status'))
            <div class="status-toast">{{ session('status') }}</div>
        @endif

        <section class="feed-stack" aria-label="{{ $activeFeed === 'fyp' ? 'For You' : 'Following' }} feed">
            @forelse ($posts as $post)
                @php
                    $isFollowing = in_array($post->user_id, $followingCreatorIds ?? [], true);
                    $isOwner = auth()->check() && auth()->user()->is($post->user);
                    $postUrl = route('posts.show', $post);
                    $avatar = $post->user->avatar_url ?? 'https://api.dicebear.com/8.x/initials/svg?seed='.urlencode($post->user->name);
                    $mediaType = $post->display_media_type;
                @endphp

                <article class="feed-card" id="post-{{ $post->id }}">
                    <div class="media-stage">
                        <div class="media-frame">
                            @if ($mediaType === 'video')
                                @if ($post->media_embed_url)
                                    <iframe class="feed-media" src="{{ $post->media_embed_url }}" title="{{ '@'.$post->user->username.' video' }}" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                @else
                                    <video class="feed-media" controls autoplay loop muted playsinline preload="metadata" controlsList="nodownload nofullscreen noplaybackrate" disablepictureinpicture>
                                        <source src="{{ $post->media_source }}" @if ($post->media_mime_type) type="{{ $post->media_mime_type }}" @endif>
                                    </video>
                                    <button class="media-toggle" type="button" aria-label="Play or pause video">
                                        <span aria-hidden="true">&#9654;</span>
                                    </button>
                                    <div class="shorts-volume">
                                        <button type="button" data-volume-button aria-label="Control volume">
                                            <span aria-hidden="true">&#9835;</span>
                                        </button>
                                        <input type="range" data-volume-slider min="0" max="1" step="0.05" value="0" aria-label="Volume">
                                    </div>
                                    <div class="shorts-timeline" data-timeline aria-label="Video timeline">
                                        <div class="shorts-progress" data-progress></div>
                                        <input class="shorts-seek" type="range" data-seek min="0" max="0" step="0.01" value="0" aria-label="Seek video">
                                    </div>
                                    <p class="media-error" data-media-error hidden>Video cannot be played by this browser.</p>
                                @endif
                            @else
                                <img class="feed-media" src="{{ $post->media_source }}" alt="{{ $post->caption }}">
                            @endif
                        </div>
                    </div>

                    <aside class="action-rail" aria-label="Post actions">
                        <a class="avatar-ring" href="{{ route('posts.show', $post) }}" aria-label="Open post by {{ '@'.$post->user->username }}">
                            <img class="avatar" src="{{ $avatar }}" alt="{{ $post->user->name }}">
                        </a>

                        @auth
                            @unless ($isOwner)
                                <form method="POST" action="{{ route('users.subscribe', $post->user) }}">
                                    @csrf
                                    <button @class(['round-action', 'followed' => $isFollowing]) type="submit" title="{{ $isFollowing ? 'Unsubscribe' : 'Subscribe' }}" aria-label="{{ $isFollowing ? 'Unsubscribe' : 'Subscribe' }}">
                                        @if ($isFollowing)
                                            <span aria-hidden="true">&#10003;</span>
                                        @else
                                            <span class="app-icon icon-plus" aria-hidden="true"></span>
                                        @endif
                                    </button>
                                </form>
                            @endunless

                            <form method="POST" action="{{ route('posts.like', $post) }}">
                                @csrf
                                <button @class(['round-action', 'liked' => $post->likedBy(auth()->user())]) type="submit" title="Like" aria-label="Like">
                                    <span class="app-icon icon-heart" aria-hidden="true"></span>
                                </button>
                            </form>
                            <span class="metric">{{ $post->likes_count }}</span>

                            <button class="round-action comment-toggle" type="button" data-comments-target="comments-{{ $post->id }}" title="Comments" aria-label="Open comments" aria-expanded="false">
                                <span class="app-icon icon-comment" aria-hidden="true"></span>
                            </button>
                            <span class="metric">{{ $post->comments_count }}</span>

                            <button class="round-action" type="button" data-share-url="{{ $postUrl }}" data-share-title="{{ '@'.$post->user->username.' on TikiToki' }}" title="Share" aria-label="Share">
                                <span class="app-icon icon-share" aria-hidden="true"></span>
                                <span class="sr-only" data-action-label>Share</span>
                            </button>
                        @else
                            <a class="round-action" href="{{ route('login') }}" title="Log in to subscribe" aria-label="Log in to subscribe"><span class="app-icon icon-plus" aria-hidden="true"></span></a>
                            <a class="round-action" href="{{ route('login') }}" title="Log in to like" aria-label="Log in to like"><span class="app-icon icon-heart" aria-hidden="true"></span></a>
                            <span class="metric">{{ $post->likes_count }}</span>
                            <button class="round-action comment-toggle" type="button" data-comments-target="comments-{{ $post->id }}" title="Comments" aria-label="Open comments" aria-expanded="false"><span class="app-icon icon-comment" aria-hidden="true"></span></button>
                            <span class="metric">{{ $post->comments_count }}</span>
                            <button class="round-action" type="button" data-share-url="{{ $postUrl }}" data-share-title="{{ '@'.$post->user->username.' on TikiToki' }}" title="Share" aria-label="Share">
                                <span class="app-icon icon-share" aria-hidden="true"></span>
                                <span class="sr-only" data-action-label>Share</span>
                            </button>
                        @endauth
                    </aside>

                    <div class="post-info">
                        <div class="creator-line">
                            <strong>{{ '@'.$post->user->username }}</strong>
                            <span>{{ $post->user->followers_count }} subscribers</span>
                        </div>
                        <p>{{ $post->caption }}</p>
                        @if ($post->song_title)
                            <div class="sound-line"><span aria-hidden="true">&#9835;</span> {{ $post->song_title }}</div>
                        @endif
                    </div>

                    <aside class="comments-panel" id="comments-{{ $post->id }}" aria-label="Comments for {{ '@'.$post->user->username }}" aria-hidden="true">
                        <div class="comments-header">
                            <div>
                                <strong>Comments</strong>
                                <span>{{ $post->comments_count }} {{ Str::plural('comment', $post->comments_count) }}</span>
                            </div>
                            <button class="comments-close" type="button" aria-label="Close comments">&times;</button>
                        </div>

                        <div class="comment-list">
                            @forelse ($post->comments as $comment)
                                <div class="comment-row">
                                    <img src="{{ $comment->user->avatar_url ?? 'https://api.dicebear.com/8.x/initials/svg?seed='.urlencode($comment->user->name) }}" alt="{{ $comment->user->name }}">
                                    <div>
                                        <strong>{{ '@'.$comment->user->username }}</strong>
                                        <p>{{ $comment->body }}</p>
                                    </div>
                                </div>
                            @empty
                                <p class="muted">No comments yet.</p>
                            @endforelse
                        </div>

                        @auth
                            <form class="comment-form" method="POST" action="{{ route('posts.comments.store', $post) }}">
                                @csrf
                                <input type="text" name="body" placeholder="Add a comment" maxlength="500" required>
                                <button type="submit">Post</button>
                            </form>
                        @else
                            <a class="comment-login" href="{{ route('login') }}">Log in to comment</a>
                        @endauth
                    </aside>
                </article>
            @empty
                <article class="empty-feed">
                    @if ($activeFeed === 'following')
                        <h1>No subscribed posts yet</h1>
                        <p>Subscribe to creators from For You and their posts will appear here.</p>
                        <a href="{{ route('feed.fyp') }}" class="primary-button">Explore For You</a>
                    @else
                        <h1>The feed is warming up</h1>
                        @auth
                            <p>Be the first to share a clip or photo.</p>
                            <a href="{{ route('posts.create') }}" class="primary-button">Post video</a>
                        @else
                            <p>Create an account and publish the first clip or photo.</p>
                            <a href="{{ route('register') }}" class="primary-button">Join TikiToki</a>
                        @endauth
                    @endif
                </article>
            @endforelse
        </section>

        <x-bottom-nav active="home" />
    </div>
</x-layouts.app>
