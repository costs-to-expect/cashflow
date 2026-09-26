<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Models\DefaultSplitAllocation;
use App\Service\Api\ApiService;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private readonly ApiService $api) {}

    public function defaultSplit(): View
    {
        $resources = $this->api->resources();

        return view('settings.default-split', [
            'children' => $resources['status'] === 200 ? $resources['content'] : [],
            'allocations' => DefaultSplitAllocation::query()->orderBy('sort_order')->get(['resource_id', 'percentage'])->toArray(),
        ]);
    }
}
