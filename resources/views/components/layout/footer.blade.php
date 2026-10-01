@props(['width' => 'max-w-4xl'])

<footer class="border-t border-gray-200 bg-white">
    <div class="mx-auto {{ $width }} px-4 py-10">
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
                <p class="mt-4 text-sm text-gray-400">
                    Version {{ config('app.version.app') }}, released {{ config('app.version.date') }}
                </p>
            </div>
        </div>
    </div>
</footer>
