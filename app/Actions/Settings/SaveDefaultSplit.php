<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\DefaultSplitAllocation;
use App\Models\ResourceType;
use Illuminate\Support\Facades\DB;

class SaveDefaultSplit
{
    /**
     * @param  array<int, array{resource_id: string, percentage: int}>  $allocations
     */
    public function __invoke(ResourceType $resourceType, array $allocations): void
    {
        DB::transaction(function () use ($resourceType, $allocations) {
            DefaultSplitAllocation::query()->where('resource_type_id', $resourceType->id)->delete();

            foreach ($allocations as $index => $allocation) {
                DefaultSplitAllocation::create([
                    'resource_type_id' => $resourceType->id,
                    'resource_id' => $allocation['resource_id'],
                    'percentage' => $allocation['percentage'],
                    'sort_order' => $index,
                ]);
            }
        });
    }
}
