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
                </div>
            </div>

            <a href="{{ route('auth.sign-out.action') }}" class="text-sm text-gray-500 hover:text-gray-900">Sign out</a>
        </div>
    </nav>

    <main class="mx-auto max-w-4xl px-4 py-8">
        <x-flash />

        {{ $slot }}
    </main>
</body>
</html>
