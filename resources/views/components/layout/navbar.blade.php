<nav class="bg-brand-700">
    <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-4">
        <a href="{{ route('welcome') }}" class="text-lg font-semibold text-white">{{ config('app.name') }}</a>

        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('resource-types.index') }}" class="inline-flex h-10 items-center justify-center rounded-md bg-white px-4 text-base font-medium text-brand-700 shadow-sm hover:bg-gray-100">Dashboard</a>
                <a href="{{ route('auth.sign-out.action') }}" class="inline-flex h-10 items-center justify-center rounded-md border border-white/30 px-4 text-base font-medium text-white hover:bg-brand-600">Sign out</a>
            @else
                <a href="{{ route('auth.sign-in') }}" class="inline-flex h-10 items-center justify-center rounded-md bg-white px-4 text-base font-medium text-brand-700 shadow-sm hover:bg-gray-100">Sign in</a>
            @endauth
        </div>
    </div>
</nav>
