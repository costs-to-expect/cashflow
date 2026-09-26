<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="min-h-screen bg-gray-50 font-sans antialiased">
    <nav class="border-b border-gray-200 bg-white">
        <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-3">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="text-base font-semibold text-gray-900">{{ config('app.name') }}</a>

                <div class="hidden gap-4 sm:flex">
                    @foreach ($navChildren as $navChild)
                        <a href="{{ route('children.show', $navChild['id']) }}"
                           class="text-sm text-gray-600 hover:text-gray-900 {{ request()->route('resource_id') === $navChild['id'] ? 'font-semibold text-indigo-600' : '' }}">
                            {{ $navChild['name'] }}
                        </a>
                    @endforeach

                    <a href="{{ route('recurring.index') }}" class="text-sm text-gray-600 hover:text-gray-900 {{ request()->routeIs('recurring.*') ? 'font-semibold text-indigo-600' : '' }}">
                        Recurring
                    </a>
                    <a href="{{ route('settings.index') }}" class="text-sm text-gray-600 hover:text-gray-900 {{ request()->routeIs('settings.*') ? 'font-semibold text-indigo-600' : '' }}">
                        Settings
                    </a>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <a href="{{ route('auth.sign-out.action') }}" class="hidden text-sm text-gray-500 hover:text-gray-900 sm:inline">Sign out</a>

                <button type="button" id="mobile-menu-toggle" aria-controls="mobile-menu" aria-expanded="false" class="inline-flex items-center rounded-md p-2 text-gray-500 hover:bg-gray-100 sm:hidden">
                    <svg id="mobile-menu-toggle-open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    <svg id="mobile-menu-toggle-close" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden border-t border-gray-200 sm:hidden">
            <div class="space-y-1 px-4 py-3">
                @foreach ($navChildren as $navChild)
                    <a href="{{ route('children.show', $navChild['id']) }}" class="block py-1 text-sm text-gray-600 {{ request()->route('resource_id') === $navChild['id'] ? 'font-semibold text-indigo-600' : '' }}">
                        {{ $navChild['name'] }}
                    </a>
                @endforeach

                <a href="{{ route('recurring.index') }}" class="block py-1 text-sm text-gray-600 {{ request()->routeIs('recurring.*') ? 'font-semibold text-indigo-600' : '' }}">Recurring</a>
                <a href="{{ route('settings.index') }}" class="block py-1 text-sm text-gray-600 {{ request()->routeIs('settings.*') ? 'font-semibold text-indigo-600' : '' }}">Settings</a>
                <a href="{{ route('auth.sign-out.action') }}" class="block py-1 text-sm text-gray-500">Sign out</a>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-4xl px-4 py-8">
        <x-flash />

        {{ $slot }}
    </main>

    <script src="{{ asset('js/nav.js') }}" defer></script>
</body>
</html>
