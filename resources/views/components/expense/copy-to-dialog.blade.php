@props(['resources'])

{{--
    The "Copy to" pop-up, once per page rather than once per expense: public/js/*/copy-to.js fills in
    the expense it was opened for (its name, where "Copy" posts and where "Adjust" goes) and opens it.

    resources  list of {id, name}: the resources an expense can go to, i.e. not the one whose page it is
--}}
<dialog id="copy-to-dialog" aria-labelledby="copy-to-heading"
        class="m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border-0 bg-white p-0 text-gray-900 shadow-xl backdrop:bg-gray-900/40">
    <form method="POST" action="" id="copy-to-form" class="space-y-5 p-5 sm:p-6">
        @csrf

        <div>
            <h2 id="copy-to-heading" class="text-base font-semibold text-gray-900">Copy to</h2>
            <p class="mt-0.5 text-sm text-gray-500"><span id="copy-to-name" class="font-medium text-gray-700"></span> is copied exactly as it is. Adjust it first to change anything.</p>
        </div>

        <x-helper.form.field.select name="target_resource_id" :title="$resourceTermSingular" required
            :options="collect($resources)->mapWithKeys(fn ($resource) => [$resource['id'] => $resource['name']])"
            :value="$resources[0]['id']" />

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <x-button type="button" variant="secondary" data-copy-to-close>Cancel</x-button>
            <x-button variant="secondary" href="#" id="copy-to-adjust">Adjust</x-button>
            <x-button id="copy-to-submit">Copy</x-button>
        </div>
    </form>
</dialog>
