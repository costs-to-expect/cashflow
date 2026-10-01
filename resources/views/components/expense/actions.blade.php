@props(['resource', 'item'])

<a href="{{ route('expenses.edit', ['resourceType' => $currentResourceType, 'resource_id' => $resource['id'], 'item_id' => $item['id']]) }}" class="rounded-md px-2 py-1.5 font-medium text-brand-700 hover:bg-gray-100 hover:underline">Edit</a>
<form method="POST" action="{{ route('expenses.delete', ['resourceType' => $currentResourceType, 'resource_id' => $resource['id'], 'item_id' => $item['id']]) }}" class="inline" onsubmit="return confirm('Delete this expense?');">
    @csrf
    <button type="submit" class="cursor-pointer rounded-md px-2 py-1.5 font-medium text-red-600 hover:bg-red-50 hover:underline">Delete</button>
</form>
