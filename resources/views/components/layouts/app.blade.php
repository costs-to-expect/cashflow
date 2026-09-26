<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="icon" sizes="48x48" href="{{ asset('images/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/'.$version['css'].'/app.css') }}">
</head>
<body class="min-h-screen bg-gray-50 font-sans antialiased">
    <x-layout.api-status />

    <nav class="bg-brand-700">
        <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-3">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="text-base font-semibold text-white">{{ config('app.name') }}</a>

                <div class="hidden gap-4 sm:flex">
                    @foreach ($navChildren as $navChild)
                        <a href="{{ route('children.show', $navChild['id']) }}"
                           class="text-sm text-white/70 hover:text-white {{ request()->route('resource_id') === $navChild['id'] ? 'font-semibold text-white' : '' }}">
                            {{ $navChild['name'] }}
                        </a>
                    @endforeach

                    <a href="{{ route('recurring.index') }}" class="text-sm text-white/70 hover:text-white {{ request()->routeIs('recurring.*') ? 'font-semibold text-white' : '' }}">
                        Recurring
                    </a>
                    <a href="{{ route('settings.index') }}" class="text-sm text-white/70 hover:text-white {{ request()->routeIs('settings.*') ? 'font-semibold text-white' : '' }}">
                        Settings
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <a href="{{ route('auth.sign-out.action') }}" class="hidden text-sm text-white/70 hover:text-white sm:inline">Sign out</a>

                <button type="button" id="mobile-menu-toggle" aria-controls="mobile-menu" aria-expanded="false" class="inline-flex items-center rounded-md p-2 text-white/70 hover:bg-brand-600 hover:text-white sm:hidden">
                    <svg id="mobile-menu-toggle-open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    <svg id="mobile-menu-toggle-close" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden border-t border-brand-600 sm:hidden">
            <div class="space-y-1 px-4 py-3">
                @foreach ($navChildren as $navChild)
                    <a href="{{ route('children.show', $navChild['id']) }}" class="block py-1 text-sm text-white/70 {{ request()->route('resource_id') === $navChild['id'] ? 'font-semibold text-white' : '' }}">
                        {{ $navChild['name'] }}
                    </a>
                @endforeach

                <a href="{{ route('recurring.index') }}" class="block py-1 text-sm text-white/70 {{ request()->routeIs('recurring.*') ? 'font-semibold text-white' : '' }}">Recurring</a>
                <a href="{{ route('settings.index') }}" class="block py-1 text-sm text-white/70 {{ request()->routeIs('settings.*') ? 'font-semibold text-white' : '' }}">Settings</a>
                <a href="{{ route('auth.sign-out.action') }}" class="block py-1 text-sm text-white/70">Sign out</a>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-4xl px-4 py-8">
        <x-flash />
        <x-form-errors />

        {{ $slot }}

        <x-layout.requests />
    </main>

    <script src="{{ asset('js/'.$version['js'].'/nav.js') }}" defer></script>
</body>
</html>
