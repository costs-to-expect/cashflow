<?php

namespace Tests\Feature;

use App\Service\Api\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http as HttpFacade;
use Tests\Feature\Concerns\FakesTheApi;
use Tests\TestCase;

/**
 * The pages whose API calls are pooled: what goes in which pool, that each
 * response still ends up where it belongs, and that nothing is left to be
 * fetched sequentially after them (the nav included).
 */
class PooledPagesTest extends TestCase
{
    use FakesTheApi;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpApiPages();
    }

    /**
     * @return list<string>
     */
    private function allUris(): array
    {
        return array_column(Http::requests(), 'uri');
    }

    public function test_the_dashboard_fetches_everything_in_two_pools_after_the_sign_in_check(): void
    {
        $this->signedIn()->get('/resource-types/rt-1/dashboard')->assertOk();

        // The auth check first, on its own. Then the wave that needs no
        // resource ids - resources, permitted resource types (the nav) and
        // the three combined summaries - then each of the two resources'
        // recent items and three summaries. Nothing after, so the nav was
        // served from what the pools fetched.
        $this->assertSame([null, ...array_fill(0, 5, 1), ...array_fill(0, 8, 2)], array_column(Http::requests(), 'pool'));
        $this->assertSame([5, 8], array_column(Http::pools(), 'size'));

        $this->assertSame('/v3/auth/user', $this->allUris()[0]);
    }

    public function test_the_dashboard_puts_each_response_with_the_resource_it_belongs_to(): void
    {
        $this->signedIn()->get('/resource-types/rt-1/dashboard')
            ->assertOk()
            ->assertSee('Rent for Ada')
            ->assertSee('Rent for Ben')
            // Ada's 75 of the combined 100, Ben's 25.
            ->assertSee('75%')
            ->assertSee('25%')
            ->assertSee('100.00');
    }

    public function test_the_dashboard_asks_for_each_periods_current_window_and_all_time(): void
    {
        $this->signedIn()->get('/resource-types/rt-1/dashboard')->assertOk();

        $this->assertContains('/v3/summary/resource-types/rt-1/items?filter=effective_date%3A2026-04-06%3A2027-04-05', $this->allUris());
        $this->assertContains('/v3/summary/resource-types/rt-1/items?filter=effective_date%3A2026-01-01%3A2026-12-31', $this->allUris());
        $this->assertContains('/v3/summary/resource-types/rt-1/items', $this->allUris());
        $this->assertContains('/v3/summary/resource-types/rt-1/resources/r-2/items?filter=effective_date%3A2026-04-06%3A2027-04-05', $this->allUris());
        $this->assertContains('/v3/summary/resource-types/rt-1/resources/r-2/items', $this->allUris());
    }

    public function test_the_dashboard_has_no_second_wave_without_resources(): void
    {
        $this->fakeApi(['api.test/v3/resource-types/rt-1/resources?collection=true' => HttpFacade::response([], 200)]);

        $this->signedIn()->get('/resource-types/rt-1/dashboard')
            ->assertOk()
            ->assertSee('No children set up yet');

        $this->assertSame([null, ...array_fill(0, 5, 1)], array_column(Http::requests(), 'pool'));
    }

    public function test_the_dashboard_reports_an_api_error_rather_than_a_second_wave(): void
    {
        $this->fakeApi(['api.test/v3/resource-types/rt-1/resources?collection=true' => HttpFacade::response([], 500)]);

        $this->signedIn()->get('/resource-types/rt-1/dashboard')
            ->assertOk()
            ->assertSee('reach the Costs to Expect API');

        $this->assertSame([5], array_column(Http::pools(), 'size'));
    }

    public function test_the_resource_page_fetches_everything_in_one_pool_after_the_sign_in_check(): void
    {
        $this->signedIn()->get('/resource-types/rt-1/resources/r-1')->assertOk();

        // The resource, its items, three summaries and the nav's two.
        $this->assertSame([null, ...array_fill(0, 7, 1)], array_column(Http::requests(), 'pool'));
        $this->assertSame([7], array_column(Http::pools(), 'size'));
    }

    public function test_the_resource_page_renders_what_the_pool_returned(): void
    {
        $this->signedIn()->get('/resource-types/rt-1/resources/r-1')
            ->assertOk()
            ->assertSee('Ada')
            ->assertSee('The eldest')
            ->assertSee('Rent for Ada')
            ->assertDontSee('Rent for Ben')
            ->assertSee('75.00');
    }

    public function test_the_resource_page_asks_for_the_requested_page_of_items(): void
    {
        $this->signedIn()->get('/resource-types/rt-1/resources/r-1?page=3')->assertOk();

        HttpFacade::assertSent(fn (Request $request) => str_contains($request->url(), '/resources/r-1/items?')
            && str_contains($request->url(), 'limit=25')
            && str_contains($request->url(), 'offset=50'));
    }

    public function test_the_resource_page_is_a_404_for_a_resource_that_does_not_exist(): void
    {
        $this->fakeApi(['api.test/v3/resource-types/rt-1/resources/r-1' => HttpFacade::response(['message' => 'Not found'], 404)]);

        $this->signedIn()->get('/resource-types/rt-1/resources/r-1')->assertNotFound();
    }

    public function test_the_requests_panel_on_the_page_shows_the_pools(): void
    {
        $this->signedIn()->get('/resource-types/rt-1/dashboard')
            ->assertSee('Pool 1')
            ->assertSee('5 requests in parallel')
            ->assertSee('Pool 2')
            ->assertSee('8 requests in parallel')
            ->assertSee('13 pooled in 2 pools');
    }
}
