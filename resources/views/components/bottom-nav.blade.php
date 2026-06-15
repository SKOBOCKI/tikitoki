@props(['active' => 'home'])

@php
    $profileHref = auth()->check() ? route('profile.show') : route('login');
    $chatHref = auth()->check() ? route('chats.index') : route('login');
    $createHref = auth()->check() ? route('posts.create') : route('login');
@endphp

<nav class="bottom-nav" aria-label="Primary navigation">
    <a @class(['active' => $active === 'home']) href="{{ route('feed.fyp') }}" aria-label="Home">
        <span class="app-icon icon-home bottom-nav-icon" aria-hidden="true"></span>
        <small>Home</small>
    </a>
    <a @class(['active' => $active === 'search']) href="{{ route('feed.search') }}" aria-label="Search">
        <span class="app-icon icon-search bottom-nav-icon" aria-hidden="true"></span>
        <small>Search</small>
    </a>
    <a @class(['create-tab', 'active' => $active === 'create']) href="{{ $createHref }}" aria-label="Create">
        <span class="create-icon-shell" aria-hidden="true">
            <span class="app-icon icon-plus bottom-nav-icon"></span>
        </span>
        <small>Create</small>
    </a>
    <a @class(['active' => $active === 'inbox']) href="{{ $chatHref }}" aria-label="Inbox">
        <span class="app-icon icon-mail bottom-nav-icon" aria-hidden="true"></span>
        <small>Inbox</small>
    </a>
    <a @class(['active' => $active === 'profile']) href="{{ $profileHref }}" aria-label="Profile">
        <span class="app-icon icon-user bottom-nav-icon" aria-hidden="true"></span>
        <small>Profile</small>
    </a>
</nav>
