<?php

namespace Tests\Feature\Concerns;

use App\Models\ReportingPeriod;
use App\Models\ResourceType;
use App\Service\Api\Http;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http as HttpFacade;

/**
 * Scaffolding for the tests of pages that read from the API: a signed-in
 * user, a resource type (rt-1, with a tax year and a calendar year as its
 * reporting periods) and a fake of every endpoint those pages use. Call
 * setUpApiPages() from setUp().
 *
 * The fake API's resource type has two resources - Ada (r-1, three quarters
 * of the spending, with the expense i-1 categorised as School > Trips) and Ben
 * (r-2) - and two categories, Food (c-1, with the subcategory Lunch) and
 * School (c-2, with Trips).
 */
trait FakesTheApi
{
    protected ResourceType $resourceType;

    private bool $faked = false;

    protected function setUpApiPages(): void
    {
        Carbon::setTestNow('2026-09-30');
        Http::reset();

        config([
            'app.api.base_url' => 'http://api.test',
            'app.api.pool_concurrency' => 6,
            'app.api.allowed_emails' => ['dean@example.com'],
        ]);

        $this->resourceType = ResourceType::create([
            'user_id' => 'u-1',
            'name' => 'Kids',
            'description' => 'Kids',
            'item_type' => 'allocated-expense',
            'api_resource_type_id' => 'rt-1',
            'api_item_type_id' => 'item-type',
            'item_subtype_id' => 'item-subtype',
        ]);

        // A tax year and a calendar year, so two periods plus all time.
        ReportingPeriod::create(['resource_type_id' => $this->resourceType->id, 'name' => 'Tax year', 'start_month' => 4, 'start_day' => 6, 'end_month' => 4, 'end_day' => 5, 'sort_order' => 1]);
        ReportingPeriod::create(['resource_type_id' => $this->resourceType->id, 'name' => 'Calendar year', 'start_month' => 1, 'start_day' => 1, 'end_month' => 12, 'end_day' => 31, 'sort_order' => 2]);
    }

    /**
     * $overrides go first so they win over the defaults. Faking is additive,
     * first match wins, so a test that wants overrides has to be the one to
     * call this, before anything else does (signedIn() fakes the defaults
     * if nothing has). The order of the defaults matters for the same
     * reason: the more specific pattern first.
     *
     * @param  array<string, mixed>  $overrides
     */
    protected function fakeApi(array $overrides = []): void
    {
        $this->faked = true;

        $summary = fn (string $subtotal) => HttpFacade::response([['currency' => ['code' => 'GBP'], 'subtotal' => $subtotal, 'count' => 1]], 200);

        $items = fn (string $id, string $name) => HttpFacade::response([[
            'id' => $id,
            'name' => $name,
            'description' => null,
            'effective_date' => '2026-09-01',
            'currency' => ['code' => 'GBP'],
            'actualised_total' => '50.00',
            'total' => '50.00',
            'percentage' => 100,
        ]], 200);

        $rt = 'api.test/v3/resource-types/rt-1';

        HttpFacade::fake($overrides + [
            'api.test/v3/auth/user' => HttpFacade::response(['id' => 'u-1', 'name' => 'Dean', 'email' => 'dean@example.com'], 200),
            'api.test/v3/auth/user/permitted-resource-types' => HttpFacade::response([['id' => 'rt-1']], 200),
            'api.test/v3/currencies?collection=true' => HttpFacade::response([
                ['id' => 'usd', 'code' => 'USD', 'name' => 'US dollar'],
                ['id' => 'gbp', 'code' => 'GBP', 'name' => 'Pound sterling'],
            ], 200),

            "{$rt}/resources?collection=true" => HttpFacade::response([
                ['id' => 'r-1', 'name' => 'Ada', 'description' => 'The eldest'],
                ['id' => 'r-2', 'name' => 'Ben', 'description' => null],
            ], 200),
            "{$rt}/resources/r-1" => HttpFacade::response(['id' => 'r-1', 'name' => 'Ada', 'description' => 'The eldest'], 200),

            "{$rt}/categories?collection=true" => HttpFacade::response([
                ['id' => 'c-1', 'name' => 'Food', 'description' => 'Meals'],
                ['id' => 'c-2', 'name' => 'School', 'description' => 'Learning'],
            ], 200),
            "{$rt}/categories/c-1/subcategories?collection=true" => HttpFacade::response([['id' => 's-1', 'name' => 'Lunch', 'description' => 'Packed']], 200),
            "{$rt}/categories/c-2/subcategories?collection=true" => HttpFacade::response([['id' => 's-2', 'name' => 'Trips', 'description' => 'Days out']], 200),

            "{$rt}/resources/r-1/items/i-1/categories/ic-1/subcategories" => HttpFacade::response([['id' => 'is-1', 'subcategory' => ['id' => 's-2', 'name' => 'Trips']]], 200),
            "{$rt}/resources/r-1/items/i-1/categories" => HttpFacade::response([['id' => 'ic-1', 'category' => ['id' => 'c-2', 'name' => 'School']]], 200),
            "{$rt}/resources/r-1/items/i-1" => HttpFacade::response([
                'id' => 'i-1',
                'name' => 'Rent for Ada',
                'description' => 'Monthly',
                'effective_date' => '2026-09-01',
                'currency' => ['id' => 'gbp', 'code' => 'GBP'],
                'total' => '50.00',
                'percentage' => 100,
            ], 200),
            "{$rt}/resources/r-1/items*" => $items('i-1', 'Rent for Ada'),
            "{$rt}/resources/r-2/items*" => $items('i-2', 'Rent for Ben'),

            'api.test/v3/summary/resource-types/rt-1/items*' => $summary('100.00'),
            'api.test/v3/summary/resource-types/rt-1/resources/r-1/items*' => $summary('75.00'),
            'api.test/v3/summary/resource-types/rt-1/resources/r-2/items*' => $summary('25.00'),
        ]);
    }

    protected function signedIn(): static
    {
        if (! $this->faked) {
            $this->fakeApi();
        }

        return $this
            ->withCookie(config('app.api.cookie_user'), 'u-1')
            ->withCookie(config('app.api.cookie_bearer'), 'token');
    }

    /**
     * The URIs requested so far, in order - just those sent in the given
     * pool, or (with null) those sent on their own.
     *
     * @return list<string>
     */
    protected function uris(?int $pool = null): array
    {
        return array_values(array_column(
            array_filter(Http::requests(), fn (array $request) => $request['pool'] === $pool),
            'uri'
        ));
    }
}
