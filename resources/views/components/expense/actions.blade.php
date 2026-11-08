@props(['resource', 'item', 'canCopyTo' => false])

<a href="{{ route('expenses.edit', ['resourceType' => $currentResourceType, 'resource_id' => $resource['id'], 'item_id' => $item['id']]) }}" class="rounded-md px-2 py-1.5 font-medium text-brand-700 hover:bg-gray-100 hover:underline">Edit</a>
<a href="{{ route('expenses.copy', ['resourceType' => $currentResourceType, 'resource_id' => $resource['id'], 'item_id' => $item['id']]) }}" class="rounded-md px-2 py-1.5 font-medium text-brand-700 hover:bg-gray-100 hover:underline">Copy</a>
@if ($canCopyTo)
    {{-- Opens the page's one copy-to dialog (see x-expense.copy-to-dialog), which public/js/*/copy-to.js points at this expense. --}}
    <button type="button" data-copy-to data-name="{{ $item['name'] }}"
            data-copy-url="{{ route('expenses.copy-to', ['resourceType' => $currentResourceType, 'resource_id' => $resource['id'], 'item_id' => $item['id']]) }}"
            data-adjust-url="{{ route('expenses.copy', ['resourceType' => $currentResourceType, 'resource_id' => $resource['id'], 'item_id' => $item['id']]) }}"
            class="cursor-pointer rounded-md px-2 py-1.5 font-medium text-brand-700 hover:bg-gray-100 hover:underline">Copy to</button>
@endif
<form method="POST" action="{{ route('expenses.delete', ['resourceType' => $currentResourceType, 'resource_id' => $resource['id'], 'item_id' => $item['id']]) }}" class="inline" onsubmit="return confirm('Delete this expense?');">
    @csrf
    <button type="submit" class="cursor-pointer rounded-md px-2 py-1.5 font-medium text-red-600 hover:bg-red-50 hover:underline">Delete</button>
</form>
