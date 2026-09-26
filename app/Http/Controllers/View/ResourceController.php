<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Service\Api\ApiService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResourceController extends Controller
{
    public function __construct(private readonly ApiService $api) {}

    public function create(): View
    {
        return view('children.create');
    }

    public function show(Request $request, string $resource_id): View
    {
        $resource = $this->api->resource($resource_id);

        abort_if($resource['status'] !== 200, 404, 'That child could not be found.');

        $page = max(1, (int) $request->query('page', 1));
        $perPage = 25;

        $items = $this->api->items($resource_id, [
            'sort' => 'effective_date:desc',
            'limit' => $perPage,
            'offset' => ($page - 1) * $perPage,
            'include-categories' => 'true',
            'include-subcategories' => 'true',
        ]);

        return view('children.show', [
            'child' => $resource['content'],
            'items' => $items['status'] === 200 ? $items['content'] : [],
            'page' => $page,
            'hasMore' => $items['status'] === 200 && count($items['content']) === $perPage,
        ]);
    }
}
