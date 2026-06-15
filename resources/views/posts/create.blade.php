<x-layouts.app title="Create | TikiToki">
    <section class="create-page">
        <nav class="profile-nav" aria-label="Create navigation">
            <a href="{{ route('feed.fyp') }}" class="brand-link">TikiToki</a>
            <div>
                <a href="{{ route('feed.fyp') }}">For You</a>
                <a href="{{ route('feed.search') }}">Search</a>
                <a href="{{ route('profile.show') }}">Profile</a>
            </div>
        </nav>

        <main class="create-layout">
            <section class="create-preview" aria-label="Upload preview">
                <div>
                    <span class="app-icon icon-plus" aria-hidden="true"></span>
                    <strong>9:16</strong>
                </div>
            </section>

            <form method="POST" action="{{ route('posts.store') }}" class="create-panel">
                @csrf

                <div class="panel-heading">
                    <p>Create</p>
                    <h1>Add video</h1>
                </div>

                @if ($errors->any())
                    <div class="form-error">{{ $errors->first() }}</div>
                @endif

                <label>
                    Caption
                    <textarea name="caption" placeholder="Write a short caption" required maxlength="500" rows="5">{{ old('caption') }}</textarea>
                </label>

                <label>
                    Media URL
                    <input type="url" name="media_url" value="{{ old('media_url') }}" placeholder="https://... video or image URL" required>
                </label>

                <div class="form-grid">
                    <label>
                        Type
                        <select name="media_type" required>
                            <option value="video" @selected(old('media_type', 'video') === 'video')>Video</option>
                            <option value="photo" @selected(old('media_type') === 'photo')>Photo</option>
                        </select>
                    </label>

                    <label>
                        Sound
                        <input type="text" name="song_title" value="{{ old('song_title') }}" placeholder="Optional">
                    </label>
                </div>

                <button type="submit" class="primary-button">Publish</button>
            </form>
        </main>

        <x-bottom-nav active="create" />
    </section>
</x-layouts.app>
