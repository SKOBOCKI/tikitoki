<x-layouts.app title="Login | TikiToki">
    <section class="auth-screen">
        <a href="{{ route('feed.fyp') }}" class="brand-link">TikiToki</a>

        <form method="POST" action="{{ route('login') }}" class="auth-panel">
            @csrf
            <div>
                <p class="eyebrow">Welcome back</p>
                <h1>Log in</h1>
            </div>

            @if ($errors->any())
                <div class="form-error">{{ $errors->first() }}</div>
            @endif

            <label>
                Email
                <input type="email" name="email" value="{{ old('email') }}" required autofocus>
            </label>

            <label>
                Password
                <input type="password" name="password" required>
            </label>

            <label class="checkbox-row">
                <input type="hidden" name="remember" value="0">
                <input type="checkbox" name="remember" value="1" @checked(old('remember', '1'))>
                Keep me logged in
            </label>

            <button type="submit" class="primary-button">Log in</button>
            <p class="muted">No account yet? <a href="{{ route('register') }}">Create one</a></p>
        </form>
    </section>
</x-layouts.app>
