<x-layouts.app title="Naming">
    @php
        $description = 'What a "'.strtolower($resourceTermSingular).'" is called throughout the app, e.g. Child/Children or Product/Products.';
    @endphp

    <div class="mx-auto max-w-2xl">
        <x-hero :compact="true" :back="route('settings.index', $currentResourceType)" back-label="Settings" eyebrow="Settings" title="Naming" :description="$description" />

        <form method="POST" action="{{ route('settings.resource-naming.action', $currentResourceType) }}" class="mt-6 space-y-6 rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-200 sm:p-6">
            @csrf

            <div class="grid gap-4 sm:grid-cols-2">
                <x-helper.form.field.text name="singular" title="Singular" required :value="$singular" placeholder="e.g. Child" hint="Used when there's just one." />
                <x-helper.form.field.text name="plural" title="Plural" required :value="$plural" placeholder="e.g. Children" hint="Used when there are several." />
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-gray-100 pt-5 sm:flex-row sm:justify-end">
                <x-button variant="secondary" :href="route('settings.index', $currentResourceType)">Cancel</x-button>
                <x-button>Save naming</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
