<?php

namespace Tests\Feature;

use App\Service\Api\ApiService;
use App\Service\Api\Http;
use App\Service\Api\PoolRequest;
use App\Service\Api\RequestPool;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http as HttpFacade;
use ReflectionClass;
use Tests\TestCase;

class HttpPoolTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.api.base_url' => 'http://api.test', 'app.api.pool_concurrency' => 6]);

        Http::reset();

        HttpFacade::fake([
            'api.test/v3/currencies*' => HttpFacade::response([['id' => 'gbp']], 200),
            'api.test/v3/resource-types/rt-1/resources*' => HttpFacade::response([['id' => 'r-1'], ['id' => 'r-2']], 200),
            'api.test/v3/auth/user/permitted-resource-types' => HttpFacade::response([['id' => 'rt-1']], 200),
            'api.test/v3/missing' => HttpFacade::response(['message' => 'Not found'], 404),
            '*' => HttpFacade::response([], 200),
        ]);
    }

    public function test_pooled_responses_come_back_normalised_under_their_keys(): void
    {
        $responses = (new Http('token'))->pool([
            'currencies' => PoolRequest::get('/v3/currencies?collection=true'),
            'resources' => PoolRequest::get('/v3/resource-types/rt-1/resources?collection=true'),
            'missing' => PoolRequest::get('/v3/missing'),
        ]);

        $this->assertSame(['currencies', 'resources', 'missing'], array_keys($responses));
        $this->assertSame(['status' => 200, 'content' => [['id' => 'gbp']], 'fields' => []], $responses['currencies']);
        $this->assertCount(2, $responses['resources']['content']);

        // An error status is a response like any other, not an exception.
        $this->assertSame(404, $responses['missing']['status']);
    }

    public function test_pooled_requests_get_the_same_setup_as_individual_ones(): void
    {
        (new Http('token'))->pool([
            'a' => PoolRequest::get('/v3/currencies'),
            'b' => PoolRequest::get('/v3/item-types'),
        ]);

        HttpFacade::assertSentCount(2);
        HttpFacade::assertSent(fn (Request $request) => str_starts_with($request->url(), 'http://api.test/v3/')
            && $request->hasHeader('Authorization', 'Bearer token')
            && $request->hasHeader('Accept', 'application/json'));
    }

    public function test_head_requests_can_be_pooled(): void
    {
        $responses = (new Http('token'))->pool([
            'a' => PoolRequest::head('/v3/currencies'),
            'b' => PoolRequest::get('/v3/item-types'),
        ]);

        $this->assertSame(200, $responses['a']['status']);
        HttpFacade::assertSent(fn (Request $request) => $request->method() === 'HEAD');
        HttpFacade::assertSent(fn (Request $request) => $request->method() === 'GET');
    }

    public function test_only_get_and_head_requests_can_be_built_for_a_pool(): void
    {
        $constructor = (new ReflectionClass(PoolRequest::class))->getConstructor();

        $this->assertTrue($constructor->isPrivate());
        $this->assertSame(['get', 'head'], array_values(array_map(
            fn ($method) => $method->getName(),
            (new ReflectionClass(PoolRequest::class))->getMethods(\ReflectionMethod::IS_STATIC)
        )));
    }

    public function test_a_pool_is_logged_with_its_own_id_and_its_requests_point_at_it(): void
    {
        $http = new Http('token');

        $http->get('/v3/currencies');
        $http->pool([
            'a' => PoolRequest::get('/v3/currencies'),
            'b' => PoolRequest::get('/v3/item-types'),
            'c' => PoolRequest::get('/v3/item-types'),
        ]);
        $http->pool([
            'd' => PoolRequest::get('/v3/currencies'),
            'e' => PoolRequest::get('/v3/item-types'),
        ]);

        $this->assertSame([null, 1, 1, 1, 2, 2], array_column(Http::requests(), 'pool'));
        $this->assertSame([1, 2], array_keys(Http::pools()));
        $this->assertSame([3, 2], array_column(Http::pools(), 'size'));
    }

    public function test_the_summary_counts_pooled_requests_and_never_reports_negative_savings(): void
    {
        $http = new Http('token');

        $http->get('/v3/currencies');
        $http->pool([
            'a' => PoolRequest::get('/v3/currencies'),
            'b' => PoolRequest::get('/v3/item-types'),
            'c' => PoolRequest::get('/v3/item-types'),
        ]);

        $summary = Http::summary();

        $this->assertSame(4, $summary['requests']);
        $this->assertSame(3, $summary['pooled']);
        $this->assertSame(1, $summary['pools']);
        $this->assertGreaterThanOrEqual(0, $summary['saved']);
    }

    public function test_a_pool_of_one_is_just_a_request(): void
    {
        $responses = (new Http('token'))->pool(['a' => PoolRequest::get('/v3/currencies')]);

        $this->assertSame(200, $responses['a']['status']);
        $this->assertSame([null], array_column(Http::requests(), 'pool'));
        $this->assertSame([], Http::pools());
    }

    public function test_an_empty_pool_sends_nothing(): void
    {
        $this->assertSame([], (new Http('token'))->pool([]));

        HttpFacade::assertNothingSent();
    }

    public function test_a_request_that_could_not_be_made_throws(): void
    {
        HttpFacade::fake([
            'api.test/v3/down' => HttpFacade::failedConnection(),
            '*' => HttpFacade::response([], 200),
        ]);

        $this->expectException(ConnectionException::class);

        (new Http('token'))->pool([
            'a' => PoolRequest::get('/v3/currencies'),
            'b' => PoolRequest::get('/v3/down'),
        ]);
    }

    public function test_the_api_service_pool_memoizes_what_it_fetches_for_the_calls_that_follow(): void
    {
        $api = new ApiService('token', 'rt-1');

        $responses = $api->pool(fn (RequestPool $pool) => $pool->resources()->permittedResourceTypes());

        $this->assertSame(['resources', 'permittedResourceTypes'], array_keys($responses));
        $this->assertSame([1, 1], array_column(Http::requests(), 'pool'));

        $this->assertCount(2, $api->resources()['content']);
        $this->assertSame([['id' => 'rt-1']], $api->permittedResourceTypes()['content']);

        HttpFacade::assertSentCount(2);
    }

    public function test_the_api_service_pool_does_not_fetch_what_is_already_memoized(): void
    {
        $api = new ApiService('token', 'rt-1');
        $api->resources();

        $responses = $api->pool(fn (RequestPool $pool) => $pool->resources()->permittedResourceTypes());

        $this->assertCount(2, $responses['resources']['content']);
        $this->assertSame(200, $responses['permittedResourceTypes']['status']);

        // One for the resources() call, one for the permitted resource types
        // - and as that left a pool of one, it was sent as an ordinary request.
        HttpFacade::assertSentCount(2);
        $this->assertSame([null, null], array_column(Http::requests(), 'pool'));
    }

    public function test_the_requests_panel_shows_which_requests_were_pooled(): void
    {
        $http = new Http('token');

        $http->get('/v3/item-types');
        $http->pool([
            'a' => PoolRequest::get('/v3/currencies'),
            'b' => PoolRequest::get('/v3/resource-types/rt-1/resources?collection=true'),
        ]);

        $this->blade('<x-layout.requests />')
            ->assertSeeInOrder(['GET', '/v3/item-types', 'Pool 1', '2 requests in parallel', '/v3/currencies'], false)
            ->assertSee('2 pooled in 1 pool');
    }

    public function test_the_requests_panel_has_no_pool_markup_when_nothing_was_pooled(): void
    {
        (new Http('token'))->get('/v3/item-types');

        $this->blade('<x-layout.requests />')
            ->assertSee('/v3/item-types')
            ->assertDontSee('Pool')
            ->assertDontSee('pooled');
    }
}
