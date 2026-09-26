@env(['staging', 'local'])
    <div class="bg-gray-800 py-3">
        <p class="text-sm text-center text-white">
            <strong class="font-semibold">API:</strong>
            {{ config('app.api.base_url') }} / {{ app()->environment() }}
        </p>
    </div>
@endenv
