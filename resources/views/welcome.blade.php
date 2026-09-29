<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Track allocated expenses for anything you're managing - children, projects, side hustles, a small business - with percentage splitting and recurring costs, built on the Costs to Expect API.">
    <title>{{ config('app.name') }}</title>
    <link rel="icon" sizes="48x48" href="{{ asset('images/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/'.$version['css'].'/app.css') }}">
</head>
<body class="flex min-h-screen flex-col bg-gray-50 font-sans antialiased">
    <nav class="bg-brand-700">
        <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-3">
            <span class="text-base font-semibold text-white">{{ config('app.name') }}</span>

            @auth
                <a href="{{ route('resource-types.index') }}" class="text-sm text-white/70 hover:text-white">Go to dashboard</a>
            @else
                <a href="{{ route('auth.sign-in') }}" class="text-sm text-white/70 hover:text-white">Sign in</a>
            @endauth
        </div>
    </nav>

    <div class="mx-auto max-w-4xl flex-1 px-4 py-16 sm:py-24">
        <div class="text-center">
            <span class="inline-flex items-center rounded-full bg-brand-50/10 px-3 py-1 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-500/20">
                Alpha
            </span>

            <h1 class="mt-4 text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                {{ config('app.name') }}
            </h1>

            <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600">
                Record each resource's expenses once, split by percentage across as many resources as needed,
                and let recurring costs post themselves &mdash; all backed by the Costs to Expect API.
            </p>

            <p class="mx-auto mt-2 max-w-2xl text-sm text-gray-500">
                A resource can be anything you're tracking allocated costs for &mdash; children, a project,
                a side hustle, a small business, whatever fits.
            </p>

            <div class="mt-8">
                @auth
                    <a href="{{ route('resource-types.index') }}" class="inline-block rounded-md bg-brand-500 px-4 py-2 text-base font-medium text-white hover:bg-brand-700">Go to dashboard</a>
                @else
                    <a href="{{ route('auth.sign-in') }}" class="inline-block rounded-md bg-brand-500 px-4 py-2 text-base font-medium text-white hover:bg-brand-700">Sign in</a>
                @endauth
            </div>
        </div>

        <dl class="mt-20 grid grid-cols-1 gap-x-8 gap-y-10 sm:grid-cols-3">
            <div>
                <dt class="text-base font-semibold text-gray-900">Split by percentage</dt>
                <dd class="mt-2 text-sm text-gray-600">
                    Add one expense and share it across resources however it actually breaks down &mdash; no
                    need to enter it more than once.
                </dd>
            </div>

            <div>
                <dt class="text-base font-semibold text-gray-900">Recurring expenses</dt>
                <dd class="mt-2 text-sm text-gray-600">
                    Set up a monthly cost once and it posts itself on the due date, split the same way every time.
                </dd>
            </div>

            <div>
                <dt class="text-base font-semibold text-gray-900">Reporting periods</dt>
                <dd class="mt-2 text-sm text-gray-600">
                    See running totals for the periods that matter to you &mdash; a financial year, a school
                    term, whatever fits.
                </dd>
            </div>
        </dl>

        <p class="mt-20 text-center text-sm text-gray-400">
            Currently in alpha and being tested privately &mdash; will join the rest of the
            <a href="https://api.costs-to-expect.com" class="text-gray-500 underline hover:text-gray-700">Costs to Expect</a>
            services once it's ready.
        </p>
    </div>

    <footer class="border-t border-gray-200 bg-white">
        <div class="mx-auto max-w-4xl px-4 py-10">
            <div class="grid grid-cols-2 gap-8 sm:grid-cols-3">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Costs to Expect</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="https://www.costs-to-expect.com" class="text-gray-500 hover:text-gray-700">Costs to Expect</a></li>
                        <li><a href="https://api.costs-to-expect.com" class="text-gray-500 hover:text-gray-700">API</a></li>
                        <li><a href="https://budget-pro.costs-to-expect.com" class="text-gray-500 hover:text-gray-700">Budget Pro</a></li>
                        <li><a href="https://budget.costs-to-expect.com" class="text-gray-500 hover:text-gray-700">Budget</a></li>
                        <li><a href="https://status.costs-to-expect.com" class="text-gray-500 hover:text-gray-700">Service Status</a></li>
                    </ul>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-900">{{ config('app.name') }}</h2>
                    <ul class="mt-3 space-y-2 text-sm">
                        @auth
                            <li><a href="{{ route('resource-types.index') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a></li>
                        @else
                            <li><a href="{{ route('auth.sign-in') }}" class="text-gray-500 hover:text-gray-700">Sign in</a></li>
                        @endauth
                    </ul>
                </div>

                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Contact</h2>
                    <p class="mt-3 text-sm text-gray-500">
                        <a href="mailto:support@costs-to-expect.com" class="hover:text-gray-700">support@costs-to-expect.com</a>
                    </p>
                    <p class="mt-4 text-sm text-gray-400">
                        <a href="https://www.deanblackborough.com" class="hover:text-gray-600">Dean Blackborough</a> &copy; 2026
                    </p>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
