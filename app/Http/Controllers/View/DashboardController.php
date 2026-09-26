<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Service\Api\ApiService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly ApiService $api) {}

    public function index(): View
    {
        $resources = $this->api->resources();
        $children = $resources['status'] === 200 ? $resources['content'] : [];

        $recentByChild = [];

        foreach ($children as $child) {
            $items = $this->api->items($child['id'], ['sort' => 'effective_date:desc', 'limit' => 5]);
            $recentByChild[$child['id']] = $items['status'] === 200 ? $items['content'] : [];
        }

        return view('dashboard.index', [
            'children' => $children,
            'recentByChild' => $recentByChild,
            'apiError' => $resources['status'] !== 200,
        ]);
    }
}
