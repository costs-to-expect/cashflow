@php
    $description = 'Track expenses and money in, split each one by percentage across projects, properties, clients or anything else you manage, and see running totals for every reporting period that matters. Built on the Costs to Expect API.';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="{{ $description }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Costs to Expect">
    <meta property="og:title" content="{{ config('app.name') }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ url('/') }}">
    <title>{{ config('app.name') }} | Track money in and out</title>
    <link rel="icon" sizes="48x48" href="{{ asset('images/favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/'.$version['css'].'/app.css') }}">
</head>
<body class="flex min-h-screen flex-col bg-gray-50 font-sans antialiased">
    <x-layout.api-status />
    <x-layout.navbar width="max-w-5xl" />

    <main class="flex-1">
        {{-- Hero --}}
        <section class="bg-linear-to-b from-brand-tint to-gray-50">
            <div class="mx-auto max-w-5xl px-4 pt-14 sm:pt-20">
                <div class="mx-auto max-w-4xl text-center">
                    <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-500/20">
                        Alpha
                    </span>

                    <h1 class="mt-5 text-4xl font-bold tracking-tight text-balance text-gray-900 sm:text-5xl lg:text-6xl">
                        Money in. Money out.
                        <span class="block text-brand-700">Allocated where it belongs.</span>
                    </h1>

                    <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-balance text-gray-600 sm:text-xl">
                        Cashflow tracks the money you spend and the money you receive. Record each expense
                        or transaction once, split it by percentage across projects, properties, clients or
                        anything else you're managing, and see running totals for every reporting period
                        that matters.
                    </p>

                    <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                        @auth
                            <x-button size="lg" :href="route('resource-types.index')">Go to dashboard</x-button>
                        @else
                            <x-button size="lg" :href="route('auth.sign-in')">Sign in</x-button>
                            <x-button size="lg" variant="secondary" href="mailto:support@costs-to-expect.com?subject=Cashflow%20alpha%20access">Request alpha access</x-button>
                        @endauth
                    </div>

                    @guest
                        <p class="mt-4 text-sm text-gray-500">Sign in with your Costs to Expect account.</p>
                    @endguest
                </div>

                <x-landing.dashboard-preview class="mt-14 sm:mt-16" />
            </div>
        </section>

        {{-- Features --}}
        <section class="mx-auto max-w-5xl px-4 pb-16 pt-20 sm:pb-24">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-brand-700">What it does</h2>
                <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Built for money that needs sharing out</p>
                <p class="mt-4 text-lg leading-relaxed text-balance text-gray-600">
                    A handful of projects, a few properties or one small business, Cashflow keeps the sums straight.
                </p>
            </div>

            <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <x-landing.feature title="Expenses and transactions" icon="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5">
                    Record what you spend and what comes in. Each tracker you create does expense tracking
                    or transaction tracking, so money in and money out both have a home.
                </x-landing.feature>

                <x-landing.feature title="Separate trackers for separate things" icon="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z">
                    Create a resource type for each thing you're tracking, like Projects, Properties or a side
                    hustle. Each has its own resources, categories, reporting periods and default split.
                </x-landing.feature>

                <x-landing.feature title="Split by percentage" icon="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6ZM13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z">
                    Add one expense or transaction and share it across your resources however it actually
                    breaks down. Each resource gets its own colour and a bar showing its share of the whole.
                </x-landing.feature>

                <x-landing.feature title="Reporting periods that fit" icon="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5">
                    Define the windows that matter, like a financial year or a school term, then flip every
                    total between them or look at everything to date.
                </x-landing.feature>

                <x-landing.feature title="More than one currency" icon="M14.121 7.629A3 3 0 0 0 9.017 9.43c-.023.212-.002.425.028.636l.506 3.541a4.5 4.5 0 0 1-.43 2.65L9 16.5l1.539-.513a2.25 2.25 0 0 1 1.422 0l.655.218a2.25 2.25 0 0 0 1.718-.122L15 15.75M8.25 12H12m9 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z">
                    Work in more than one currency and each one gets its own total. Nothing is converted
                    or added together behind your back.
                </x-landing.feature>

                <x-landing.feature title="Make it fit" icon="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75">
                    Tag expenses with categories and subcategories, or switch them off for a quicker form.
                    Call your resources whatever suits: projects, properties, clients.
                </x-landing.feature>
            </div>
        </section>

        {{-- How it works --}}
        <section class="border-y border-gray-200 bg-white">
            <div class="mx-auto max-w-5xl px-4 py-16 sm:py-20">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-brand-700">How it works</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Up and running in minutes</p>
                </div>

                <ol class="mt-12 grid gap-10 md:grid-cols-3 md:gap-8">
                    <li>
                        <span class="grid size-9 place-items-center rounded-full bg-brand-700 text-sm font-semibold text-white">1</span>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900">Set up a tracker</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-gray-600">
                            Name it, choose expenses or transactions, then add the resources you're tracking. A
                            resource can be a project, a property, a client or a side hustle.
                        </p>
                    </li>
                    <li>
                        <span class="grid size-9 place-items-center rounded-full bg-brand-700 text-sm font-semibold text-white">2</span>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900">Add entries once</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-gray-600">
                            Enter each expense or transaction a single time and choose how it splits. Cashflow
                            works out each resource's share.
                        </p>
                    </li>
                    <li>
                        <span class="grid size-9 place-items-center rounded-full bg-brand-700 text-sm font-semibold text-white">3</span>
                        <h3 class="mt-4 text-lg font-semibold text-gray-900">Watch the totals</h3>
                        <p class="mt-1.5 text-sm leading-relaxed text-gray-600">
                            Switch reporting periods to see combined and per-resource totals, and what each one
                            has cost so far.
                        </p>
                    </li>
                </ol>
            </div>
        </section>

        {{-- A live example of the API in use, the alpha notice and the call to action --}}
        <section class="mx-auto max-w-5xl px-4 py-16 sm:py-20">
            <a href="https://www.costs-to-expect.com" class="group mx-auto flex max-w-3xl items-start gap-4 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 transition hover:ring-2 hover:ring-brand-700 sm:p-6">
                <span class="grid size-11 shrink-0 place-items-center rounded-xl bg-brand-tint text-brand-700">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" /></svg>
                </span>

                <span class="min-w-0 flex-1">
                    <span class="block text-xs font-semibold uppercase tracking-wide text-brand-700">Live example</span>
                    <span class="mt-1 block text-lg font-semibold text-gray-900 group-hover:text-brand-700">See it working at www.costs-to-expect.com</span>
                    <span class="mt-1 block text-sm leading-relaxed text-gray-600">
                        The Costs to Expect API behind Cashflow also powers a long-term experiment tracking what
                        it really costs to raise children to adulthood in the UK, a live, public example of this
                        kind of tracking.
                    </span>
                </span>

                <span class="mt-1 text-gray-400 transition-colors group-hover:text-brand-700" aria-hidden="true">&rarr;</span>
            </a>

            <div class="mx-auto mt-6 max-w-3xl rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200 sm:p-8">
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center rounded-full bg-brand-tint px-3 py-1 text-xs font-semibold text-brand-700 ring-1 ring-inset ring-brand-500/20">Alpha</span>
                    <h2 class="text-xl font-semibold text-gray-900">An early release, still growing</h2>
                </div>

                <p class="mt-3 text-sm leading-relaxed text-gray-600">
                    Cashflow is in alpha, so expect rough edges and changes as it develops, and some
                    features, like recurring expenses, are still being finished.
                </p>

                <p class="mt-4 text-sm leading-relaxed text-gray-600">
                    Spotted something, or want something added? Get in touch at
                    <a href="mailto:support@costs-to-expect.com" class="font-medium text-brand-700 hover:underline">support@costs-to-expect.com</a>.
                </p>
            </div>

            <x-hero class="mt-16" title="Give the alpha a go" description="Sign in with your Costs to Expect account and start tracking, or get in touch if you'd like access.">
                <div class="mt-7 flex flex-wrap gap-3">
                    @auth
                        <x-button variant="hero" size="lg" :href="route('resource-types.index')">Go to dashboard</x-button>
                    @else
                        <x-button variant="hero" size="lg" :href="route('auth.sign-in')">Sign in</x-button>
                        <x-button variant="ghost" size="lg" href="mailto:support@costs-to-expect.com?subject=Cashflow%20alpha%20access">Request alpha access</x-button>
                    @endauth
                </div>
            </x-hero>

            <p class="mt-10 text-center text-sm text-gray-500">
                Cashflow is built on the
                <a href="https://api.costs-to-expect.com" class="underline hover:text-gray-700">Costs to Expect API</a>,
                the same service behind
                <a href="https://budget-pro.costs-to-expect.com" class="underline hover:text-gray-700">Budget Pro</a>
                and
                <a href="https://budget.costs-to-expect.com" class="underline hover:text-gray-700">Budget</a>.
            </p>
        </section>
    </main>

    <x-layout.footer width="max-w-5xl" />
</body>
</html>
