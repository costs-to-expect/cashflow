@if ($errors->any())
    <div role="alert" class="mb-6 rounded-xl border border-red-200 bg-red-50 p-4">
        <p class="text-sm font-medium text-red-800">Please fix the following:</p>
        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700">
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif
