<?php

namespace Tests\Unit;

use App\Actions\Expense\UpdateExpense;
use App\Service\Api\ApiService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class UpdateExpenseTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private function payload(array $extra = []): array
    {
        return [
            'name' => 'Rent',
            'description' => null,
            'effective_date' => '2026-09-01',
            'currency_id' => 'gbp',
            'total' => '100',
            'percentage' => 100,
            ...$extra,
        ];
    }

    private function apiThatAcceptsTheUpdate(): Mockery\MockInterface
    {
        $api = Mockery::mock(ApiService::class);
        $api->shouldReceive('updateItem')->once()->andReturn(['status' => 204, 'content' => null, 'fields' => []]);

        return $api;
    }

    public function test_leaves_the_categorisation_alone_when_the_payload_has_no_category_keys(): void
    {
        // Categories turned off: the form doesn't send them, and an existing
        // assignment on the API must survive the edit.
        $api = $this->apiThatAcceptsTheUpdate();
        $api->shouldNotReceive('itemCategories');
        $api->shouldNotReceive('assignItemCategory');
        $api->shouldNotReceive('deleteItemCategory');

        $result = (new UpdateExpense($api))('resource-1', 'item-1', $this->payload());

        $this->assertTrue($result->ok);
    }

    public function test_assigns_the_category_and_subcategory_when_the_payload_carries_them(): void
    {
        $api = $this->apiThatAcceptsTheUpdate();
        $api->shouldReceive('itemCategories')->once()->andReturn(['status' => 200, 'content' => []]);
        $api->shouldReceive('assignItemCategory')->once()->with('resource-1', 'item-1', 'category-1')
            ->andReturn(['status' => 201, 'content' => ['id' => 'item-category-1']]);
        $api->shouldReceive('assignItemSubcategory')->once()->with('resource-1', 'item-1', 'item-category-1', 'subcategory-1')
            ->andReturn(['status' => 201, 'content' => []]);

        $result = (new UpdateExpense($api))('resource-1', 'item-1', $this->payload([
            'category_id' => 'category-1',
            'subcategory_id' => 'subcategory-1',
        ]));

        $this->assertTrue($result->ok);
    }

    public function test_an_explicit_null_category_still_clears_the_existing_assignment(): void
    {
        $api = $this->apiThatAcceptsTheUpdate();
        $api->shouldReceive('itemCategories')->once()->andReturn(['status' => 200, 'content' => [
            ['id' => 'item-category-1', 'category' => ['id' => 'category-1']],
        ]]);
        $api->shouldReceive('itemSubcategories')->once()->andReturn(['status' => 200, 'content' => []]);
        $api->shouldReceive('deleteItemCategory')->once()->with('resource-1', 'item-1', 'item-category-1')
            ->andReturn(['status' => 204, 'content' => null]);

        $result = (new UpdateExpense($api))('resource-1', 'item-1', $this->payload([
            'category_id' => null,
            'subcategory_id' => null,
        ]));

        $this->assertTrue($result->ok);
    }
}
