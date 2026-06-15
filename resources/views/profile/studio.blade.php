<x-layouts.app title="Profile Studio | TikiToki">
    @php
        $avatar = $user->avatar_url ?? 'https://api.dicebear.com/8.x/initials/svg?seed='.urlencode($user->name);
        $banner = $user->banner_drawing ?: $user->banner_url;
    @endphp

    <section class="profile-page" style="--profile-accent: {{ $user->profile_accent ?? '#ff2d55' }}; --profile-bg: {{ $user->profile_background ?? '#0a0a0a' }};">
        <nav class="profile-nav" aria-label="Profile navigation">
            <a href="{{ route('feed.fyp') }}" class="brand-link">TikiToki</a>
            <div>
                <a href="{{ route('profile.show') }}">Profile</a>
                <a href="{{ route('feed.fyp') }}">For You</a>
            </div>
        </nav>

        @if (session('status'))
            <div class="status-toast">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('profile.update') }}" class="profile-editor studio-editor" data-profile-editor>
            @csrf
            @method('PATCH')

            <div class="panel-heading">
                <p>Customize</p>
                <h2>Profile studio</h2>
            </div>

            @if ($errors->any())
                <div class="form-error">{{ $errors->first() }}</div>
            @endif

            <div class="editor-preview">
                <div class="mini-banner" data-banner-preview @if ($banner) style="background-image: url('{{ $banner }}')" @endif></div>
                <img data-avatar-preview src="{{ $avatar }}" alt="">
            </div>

            <div class="studio-fields">
                <label>
                    Display name
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
                </label>

                <label>
                    Username
                    <input type="text" name="username" value="{{ old('username', $user->username) }}" required>
                </label>

                <label>
                    Profile picture URL
                    <input data-avatar-input type="url" name="avatar_url" value="{{ old('avatar_url', $user->avatar_url) }}" placeholder="https://...">
                </label>

                <label>
                    Bio
                    <textarea name="bio" maxlength="500" rows="4">{{ old('bio', $user->bio) }}</textarea>
                </label>
            </div>

            <div class="color-controls">
                <label>
                    Accent
                    <input type="color" name="profile_accent" value="{{ old('profile_accent', $user->profile_accent ?? '#ff2d55') }}">
                </label>
                <label>
                    Background
                    <input type="color" name="profile_background" value="{{ old('profile_background', $user->profile_background ?? '#0a0a0a') }}">
                </label>
            </div>

            <fieldset class="banner-options">
                <legend>Banner</legend>
                <label class="radio-row">
                    <input data-banner-mode type="radio" name="banner_mode" value="image" @checked(! $user->banner_drawing)>
                    Image URL
                </label>
                <label class="radio-row">
                    <input data-banner-mode type="radio" name="banner_mode" value="drawing" @checked((bool) $user->banner_drawing)>
                    Drawing canvas
                </label>
            </fieldset>

            <label data-banner-url-wrap>
                Banner image URL
                <input data-banner-input type="url" name="banner_url" value="{{ old('banner_url', $user->banner_url) }}" placeholder="https://...">
            </label>

            <div class="drawing-studio" data-drawing-studio>
                <canvas data-banner-canvas width="960" height="320" data-existing-banner="{{ old('banner_drawing', $user->banner_drawing) }}"></canvas>
                <input data-banner-drawing type="hidden" name="banner_drawing" value="{{ old('banner_drawing', $user->banner_drawing) }}">
                <div class="draw-tools">
                    <label>
                        Color
                        <input data-draw-color type="color" value="{{ old('profile_accent', $user->profile_accent ?? '#ff2d55') }}">
                    </label>
                    <label>
                        Size
                        <input data-draw-size type="range" min="2" max="40" value="12">
                    </label>
                    <button data-clear-canvas type="button">Clear</button>
                </div>
            </div>

            <button type="submit" class="primary-button">Save profile</button>
        </form>

        <x-bottom-nav active="create" />
    </section>
</x-layouts.app>
