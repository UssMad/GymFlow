<!DOCTYPE html>
<html lang="en" data-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="color-scheme" content="light dark">
        <title>{{ $title ?? 'GymFlow' }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="app-shell {{ auth()->check() ? '' : 'app-shell-auth' }}">
            @auth
                <aside class="sidebar" aria-label="Main navigation">
                    <a class="brand" href="{{ route('dashboard') }}" aria-label="GymFlow dashboard">
                        <span class="brand-mark">GF</span>
                        <span>GymFlow</span>
                    </a>

                    <div class="sidebar-label">{{ ucfirst(auth()->user()->role) }} workspace</div>
                    <nav class="nav-list">
                        <a class="nav-link {{ request()->routeIs('*dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}">
                            <span aria-hidden="true">Overview</span>
                        </a>
                    </nav>

                    <div class="sidebar-footer">
                        <div class="user-chip">
                            <span class="avatar">{{ strtoupper(substr(auth()->user()->prenom, 0, 1)) }}{{ strtoupper(substr(auth()->user()->nom, 0, 1)) }}</span>
                            <span>
                                <strong>{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</strong>
                                <small>{{ auth()->user()->email }}</small>
                            </span>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="text-button" type="submit">Sign out</button>
                        </form>
                    </div>
                </aside>
            @endauth

            <main class="main-content {{ auth()->check() ? '' : 'auth-main' }}">
                @auth
                    <header class="topbar">
                        <div>
                            <p class="eyebrow">GymFlow / {{ ucfirst(auth()->user()->role) }}</p>
                            <h1>{{ $heading ?? 'Dashboard' }}</h1>
                        </div>
                        <button class="theme-toggle" type="button" data-theme-toggle aria-label="Switch color theme" title="Switch color theme">
                            <span data-theme-icon aria-hidden="true">Light</span>
                        </button>
                    </header>
                @endauth

                @if (session('status'))
                    <div class="notice notice-success" role="status">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="notice notice-error" role="alert">
                        <strong>Check the form.</strong>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </body>
</html>
