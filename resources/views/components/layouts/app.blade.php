<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <x-layout.seo :title="$title ?? config('app.name')" />
    <link rel="icon" sizes="48x48" href="{{ asset('images/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/'.$version['css'].'/app.css') }}">
</head>
<body class="min-h-screen bg-gray-50 font-sans antialiased">
    <x-layout.api-status />

    <nav class="bg-brand-700">
        <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
            <div class="flex items-center gap-6">
                <a href="{{ $currentResourceType ? route('dashboard', $currentResourceType) : route('resource-types.index') }}" class="text-lg font-semibold text-white">{{ config('app.name') }}</a>

                @if ($currentResourceType)
                    <div class="hidden gap-4 sm:flex">
                        @foreach ($navResources as $navResource)
                            <a href="{{ route('resources.show', [$currentResourceType, $navResource['id']]) }}"
                               class="text-sm text-white/70 hover:text-white {{ request()->route('resource_id') === $navResource['id'] ? 'font-semibold text-white' : '' }}">
                                {{ $navResource['name'] }}
                            </a>
                        @endforeach

                        <a href="{{ route('recurring.index', $currentResourceType) }}" class="text-sm text-white/70 hover:text-white {{ request()->routeIs('recurring.*') ? 'font-semibold text-white' : '' }}">
                            Recurring
                        </a>
                        <a href="{{ route('settings.index', $currentResourceType) }}" class="text-sm text-white/70 hover:text-white {{ request()->routeIs('settings.*') ? 'font-semibold text-white' : '' }}">
                            Settings
                        </a>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                @if ($allResourceTypes->count() > 1)
                    <select onchange="window.location = this.value" class="hidden rounded-md border-0 bg-brand-600 py-1 pl-2 pr-7 text-sm text-white focus:ring-2 focus:ring-white/50 sm:inline-block">
                        @foreach ($allResourceTypes as $resourceType)
                            <option value="{{ route('dashboard', $resourceType) }}" @selected($currentResourceType?->id === $resourceType->id)>{{ $resourceType->name }}</option>
                        @endforeach
                    </select>
                @elseif ($currentResourceType)
                    <a href="{{ route('resource-types.index') }}" class="hidden text-sm text-white/70 hover:text-white sm:inline">{{ $currentResourceType->name }}</a>
                @endif

                @if ($currentResourceType)
                    <a href="{{ route('resource-types.create') }}" class="hidden h-10 items-center justify-center rounded-xl border border-white/30 px-4 text-base font-medium text-white hover:bg-brand-600 sm:inline-flex" title="New resource type">+ New</a>
                @endif

                <a href="{{ route('auth.sign-out.action') }}" class="hidden h-10 items-center justify-center rounded-xl border border-white/30 px-4 text-base font-medium text-white hover:bg-brand-600 sm:inline-flex">Sign out</a>

                <button type="button" id="mobile-menu-toggle" aria-controls="mobile-menu" aria-expanded="false" class="inline-flex items-center rounded-md p-2 text-white/70 hover:bg-brand-600 hover:text-white sm:hidden">
                    <svg id="mobile-menu-toggle-open" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                    <svg id="mobile-menu-toggle-close" class="hidden h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        <div id="mobile-menu" class="hidden border-t border-brand-600 sm:hidden">
            <div class="space-y-1 px-4 py-3">
                @if ($currentResourceType)
                    @foreach ($navResources as $navResource)
                        <a href="{{ route('resources.show', [$currentResourceType, $navResource['id']]) }}" class="block py-1 text-sm text-white/70 {{ request()->route('resource_id') === $navResource['id'] ? 'font-semibold text-white' : '' }}">
                            {{ $navResource['name'] }}
                        </a>
                    @endforeach

                    <a href="{{ route('recurring.index', $currentResourceType) }}" class="block py-1 text-sm text-white/70 {{ request()->routeIs('recurring.*') ? 'font-semibold text-white' : '' }}">Recurring</a>
                    <a href="{{ route('settings.index', $currentResourceType) }}" class="block py-1 text-sm text-white/70 {{ request()->routeIs('settings.*') ? 'font-semibold text-white' : '' }}">Settings</a>
                @endif

                <a href="{{ route('resource-types.index') }}" class="block py-1 text-sm text-white/70">{{ $currentResourceType->name ?? 'Resource types' }}</a>
                @if ($currentResourceType)
                    <a href="{{ route('resource-types.create') }}" class="block py-1 text-sm text-white/70">+ New resource type</a>
                @endif
                <a href="{{ route('auth.sign-out.action') }}" class="block py-1 text-sm text-white/70">Sign out</a>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-5xl px-4 py-8">
        <x-flash />
        <x-form-errors />

        {{ $slot }}

        <x-layout.requests />
    </main>

    <script src="{{ asset('js/'.$version['js'].'/nav.js') }}" defer></script>
</body>
</html>
