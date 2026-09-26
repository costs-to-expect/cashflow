@php
    $requests = \App\Service\Api\Http::requests();
    $totalTime = array_sum(array_column($requests, 'time'));
    $allSuccessful = collect($requests)->every(fn ($request) => $request['status'] < 300);
@endphp

@if (count($requests) > 0)
    <div class="mt-12 rounded-lg border border-gray-200 bg-white p-4 text-sm">
        <div class="mb-3 grid grid-cols-3 gap-4 text-center">
            <div>
                <p class="text-lg font-semibold text-gray-900">{{ count($requests) }}</p>
                <p class="text-xs text-gray-500">API requests</p>
            </div>
            <div>
                <p class="text-lg font-semibold {{ $allSuccessful ? 'text-green-600' : 'text-red-600' }}">{{ $allSuccessful ? 'All succeeded' : 'Some failed' }}</p>
                <p class="text-xs text-gray-500">Status</p>
            </div>
            <div>
                <p class="text-lg font-semibold text-gray-900">{{ $totalTime }}ms</p>
                <p class="text-xs text-gray-500">Total time</p>
            </div>
        </div>

        <ul class="divide-y divide-gray-100">
            @foreach ($requests as $request)
                <li class="flex items-center justify-between gap-3 py-1.5">
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
