<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\DefaultSplitAllocation;
use Illuminate\Support\Facades\DB;

class SaveDefaultSplit
{
    /**
     * @param  array<int, array{resource_id: string, percentage: int}>  $allocations
     */
    public function __invoke(array $allocations): void
    {
        DB::transaction(function () use ($allocations) {
            DefaultSplitAllocation::query()->delete();

            foreach ($allocations as $index => $allocation) {
                DefaultSplitAllocation::create([
                    'resource_id' => $allocation['resource_id'],
                    'percentage' => $allocation['percentage'],
                    'sort_order' => $index,
                ]);
            }
        });
    }
}
