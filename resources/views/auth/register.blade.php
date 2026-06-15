<x-layouts.app title="Register | TikiToki">
    <section class="auth-screen">
        <a href="{{ route('feed.fyp') }}" class="brand-link">TikiToki</a>

        <form method="POST" action="{{ route('register') }}" class="auth-panel">
            @csrf
            <div>
                <p class="eyebrow">Start creating</p>
                <h1>Create account</h1>
            </div>

            @if ($errors->any())
                <div class="form-error">{{ $errors->first() }}</div>
            @endif

            <label>
                Name
                <input type="text" name="name" value="{{ old('name') }}" required autofocus>
            </label>

            <label>
                Username
                <input type="text" name="username" value="{{ old('username') }}" required>
            </label>

            <label>
                Email
                <input type="email" name="email" value="{{ old('email') }}" required>
            </label>

            <label>
                Password
                <input type="password" name="password" required>
            </label>

            <label>
                Confirm password
                <input type="password" name="password_confirmation" required>
            </label>

            <button type="submit" class="primary-button">Create account</button>
            <p class="muted">Already joined? <a href="{{ route('login') }}">Log in</a></p>
        </form>
    </section>
</x-layouts.app>
