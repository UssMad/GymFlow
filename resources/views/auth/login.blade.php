<x-layouts.app title="Sign in | GymFlow">
    <section class="login-frame">
        <div class="login-intro">
            <a class="brand brand-on-dark" href="{{ route('home') }}" aria-label="GymFlow">
                <span class="brand-mark">GF</span>
                <span>GymFlow</span>
            </a>
            <div class="login-intro-copy">
                <p class="eyebrow">Training operations, in rhythm</p>
                <h1>Every member deserves a programme that keeps moving.</h1>
                <p class="login-copy">One focused workspace for gym operations, coaching decisions, and every completed session.</p>
            </div>
            <div class="login-rhythm" aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span>
            </div>
        </div>

        <div class="login-panel">
            <div>
                <p class="eyebrow">Welcome back</p>
                <h2>Sign in to GymFlow</h2>
                <p>Use the account assigned by your gym administrator.</p>
            </div>
            <form method="POST" action="{{ route('login.store') }}" class="form-stack">
                @csrf
                <label>
                    <span>Email address</span>
                    <input type="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
                </label>
                <label>
                    <span>Password</span>
                    <input type="password" name="password" autocomplete="current-password" required>
                </label>
                <label class="check-field">
                    <input type="checkbox" name="remember" value="1">
                    <span>Keep me signed in</span>
                </label>
                <button class="button button-primary" type="submit">Sign in</button>
            </form>
        </div>
    </section>
</x-layouts.app>
