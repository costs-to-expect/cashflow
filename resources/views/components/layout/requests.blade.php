@php
    $requests = \App\Service\Api\Http::requests();
    $pools = \App\Service\Api\Http::pools();
    $summary = \App\Service\Api\Http::summary();
    $allSuccessful = collect($requests)->every(fn ($request) => $request['status'] < 300);
    $currentPool = null;
@endphp

@if (count($requests) > 0)
    <div class="mt-12 rounded-2xl bg-white p-4 text-sm shadow-sm ring-1 ring-gray-200">
        <div class="mb-3 grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-lg font-semibold text-gray-900">{{ $summary['requests'] }}</p>
                <p class="text-xs text-gray-500">API requests</p>
                @if ($summary['pooled'] > 0)
                    <p class="text-xs text-brand-700">{{ $summary['pooled'] }} pooled in {{ $summary['pools'] }} {{ \Illuminate\Support\Str::plural('pool', $summary['pools']) }}</p>
                @endif
            </div>
            <div>
                <p class="text-lg font-semibold {{ $allSuccessful ? 'text-green-600' : 'text-red-600' }}">{{ $allSuccessful ? 'All succeeded' : 'Some failed' }}</p>
                <p class="text-xs text-gray-500">Status</p>
            </div>
            <div>
                <p class="text-lg font-semibold text-gray-900">{{ $summary['time'] }}ms</p>
                <p class="text-xs text-gray-500">Total time</p>
                @if ($summary['saved'] > 0)
                    <p class="text-xs text-green-600">~{{ $summary['saved'] }}ms saved by pooling</p>
                @endif
            </div>
        </div>

        <ul class="divide-y divide-gray-100">
            @foreach ($requests as $request)
                @if ($request['pool'] !== null && $request['pool'] !== $currentPool)
                    <li class="flex items-center justify-between gap-3 border-l-2 border-l-brand-500 bg-brand-tint py-1.5 pl-3 text-xs font-medium text-brand-700">
                        <span>Pool {{ $request['pool'] }}</span>
                        <span class="flex-1 font-normal">{{ $pools[$request['pool']]['size'] }} requests in parallel</span>
                        <span class="w-14 shrink-0 text-right">{{ $pools[$request['pool']]['time'] }}ms</span>
                    </li>
                @endif
                @php $currentPool = $request['pool']; @endphp

                <li class="flex items-center justify-between gap-3 border-l-2 py-1.5 pl-3 {{ $request['pool'] !== null ? 'border-l-brand-500 bg-brand-tint' : 'border-l-transparent' }}">
                    <span class="inline-flex w-16 shrink-0 justify-center rounded bg-gray-100 px-1.5 py-0.5 text-xs font-medium text-gray-700">{{ $request['method'] }}</span>
                    <span class="flex-1 truncate text-xs text-gray-600">{{ $request['uri'] }}</span>
                    <span class="shrink-0 text-xs text-gray-400">{{ $request['shape'] }}</span>
                    <span class="shrink-0 rounded px-1.5 py-0.5 text-xs font-medium {{ $request['status'] < 300 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $request['status'] }}</span>
                    <span class="w-14 shrink-0 text-right text-xs text-gray-400">{{ $request['time'] }}ms</span>
                </li>
            @endforeach
        </ul>
    </div>
@endif
