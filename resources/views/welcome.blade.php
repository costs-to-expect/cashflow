<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Track allocated expenses or general transactions for anything you're managing - children, projects, side hustles, a small business - with independent resource types, percentage splitting, and recurring costs, built on the Costs to Expect API.">
    <title>{{ config('app.name') }}</title>
    <link rel="icon" sizes="48x48" href="{{ asset('images/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/'.$version['css'].'/app.css') }}">
</head>
<body class="flex min-h-screen flex-col bg-gray-50 font-sans antialiased">
    <x-layout.api-status />
    <x-layout.navbar />

    <div class="mx-auto max-w-4xl flex-1 px-4 py-16 sm:py-24">
        <div class="text-center">
            <span class="inline-flex items-center rounded-full bg-brand-50/10 px-3 py-1 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-500/20">
                Alpha
            </span>

            <h1 class="mt-4 text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl">
                {{ config('app.name') }}
            </h1>

            <p class="mx-auto mt-6 max-w-2xl text-xl text-gray-600">
                Set up independent resource types for whatever you're tracking &mdash; allocated expenses
                or general transactions &mdash; record each resource's costs once, split by percentage
                across as many resources as needed, and let recurring costs post themselves.
            </p>

            <p class="mx-auto mt-3 max-w-2xl text-lg text-gray-500">
                A resource can be anything you're tracking costs for &mdash; children, a project,
                a side hustle, a small business, whatever fits.
            </p>

            <div class="mt-10">
                @auth
                    <a href="{{ route('resource-types.index') }}" class="inline-block rounded-xl bg-brand-500 px-6 py-3 text-lg font-medium text-white hover:bg-brand-700">Go to dashboard</a>
                @else
                    <a href="{{ route('auth.sign-in') }}" class="inline-block rounded-xl bg-brand-500 px-6 py-3 text-lg font-medium text-white hover:bg-brand-700">Sign in</a>
                @endauth
            </div>
        </div>

        <dl class="mt-20 grid grid-cols-1 gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <dt class="text-xl font-semibold text-gray-900">Multiple resource types</dt>
                <dd class="mt-2 text-lg text-gray-600">
                    Run as many independent resource types as you need, each choosing expense
                    tracking or transaction tracking, switchable in a click.
                </dd>
            </div>

            <div>
                <dt class="text-xl font-semibold text-gray-900">Split by percentage</dt>
                <dd class="mt-2 text-lg text-gray-600">
                    Add one expense and share it across resources however it actually breaks down &mdash; no
                    need to enter it more than once.
                </dd>
            </div>

            <div>
                <dt class="text-xl font-semibold text-gray-900">Recurring expenses</dt>
                <dd class="mt-2 text-lg text-gray-600">
                    Set up a monthly cost once and it posts itself on the due date, split the same way every time.
                </dd>
            </div>

            <div>
                <dt class="text-xl font-semibold text-gray-900">Reporting periods</dt>
                <dd class="mt-2 text-lg text-gray-600">
                    See running totals for the periods that matter to you &mdash; a financial year, a school
                    term, whatever fits.
                </dd>
            </div>
        </dl>

        <p class="mt-20 text-center text-base text-gray-400">
            Currently in alpha and being tested privately &mdash; will join the rest of the
            <a href="https://api.costs-to-expect.com" class="text-gray-500 underline hover:text-gray-700">Costs to Expect</a>
            services once it's ready.
        </p>
    </div>

    <x-layout.footer />
</body>
</html>
