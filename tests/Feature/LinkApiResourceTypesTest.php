<?php

namespace Tests\Feature;

use App\Models\ResourceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http as HttpFacade;
use Tests\Feature\Concerns\FakesTheApi;
use Tests\TestCase;

/**
 * Signing in shows whatever the API already permits the user to see, even if
 * this app has no local row for it yet (made elsewhere, or a reset database).
 */
class LinkApiResourceTypesTest extends TestCase
{
    use FakesTheApi;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpApiPages();
    }

    /**
     * @param  list<array<string, mixed>>  $permitted
     */
    private function apiPermits(array $permitted, int $subtypesStatus = 200): void
    {
        $this->fakeApi([
            'api.test/v3/auth/user/permitted-resource-types' => HttpFacade::response($permitted, 200),
            'api.test/v3/item-types/it-1/item-subtypes*' => HttpFacade::response($subtypesStatus === 200 ? [['id' => 'st-1']] : [], $subtypesStatus),
        ]);
    }

    private function apiResourceType(string $id, string $name, string $itemType = 'allocated-expense'): array
    {
        return [
            'id' => $id,
            'name' => $name,
            'description' => "About {$name}",
            'item_type' => ['id' => 'it-1', 'name' => $itemType],
        ];
    }

    public function test_a_permitted_resource_type_with_no_local_row_is_linked_and_shown(): void
    {
        ResourceType::query()->delete();
        $this->apiPermits([$this->apiResourceType('rt-9', 'Kids')]);

        $resourceType = fn () => ResourceType::where('api_resource_type_id', 'rt-9')->first();

        // Their only resource type, so straight to its dashboard rather than "create".
        $this->signedIn()->get('/resource-types')->assertRedirect('/resource-types/rt-9/dashboard');

        $this->assertSame('Kids', $resourceType()->name);
        $this->assertSame('About Kids', $resourceType()->description);
        $this->assertSame('allocated-expense', $resourceType()->item_type);
        $this->assertSame('it-1', $resourceType()->api_item_type_id);
        $this->assertSame('st-1', $resourceType()->item_subtype_id);
    }

    public function test_every_permitted_resource_type_is_linked_and_shown_in_the_picker(): void
    {
        ResourceType::query()->delete();
        $this->apiPermits([
            $this->apiResourceType('rt-8', 'Household'),
            $this->apiResourceType('rt-9', 'Kids', 'allocated-transaction'),
        ]);

        $this->signedIn()->get('/resource-types')
            ->assertOk()
            ->assertSee('Household')
            ->assertSee('Kids');

        $this->assertSame(['rt-8', 'rt-9'], ResourceType::orderBy('sort_order')->pluck('api_resource_type_id')->all());
        $this->assertSame('allocated-transaction', ResourceType::where('api_resource_type_id', 'rt-9')->value('item_type'));
    }

    public function test_an_existing_local_row_is_left_alone_and_not_duplicated(): void
    {
        $this->apiPermits([$this->apiResourceType('rt-1', 'Renamed on the API')]);

        $this->signedIn()->get('/resource-types')->assertRedirect('/resource-types/rt-1/dashboard');

        $this->assertSame(1, ResourceType::count());
        $this->assertSame('Kids', ResourceType::first()->name);
        $this->assertNotContains('/v3/item-types/it-1/item-subtypes', $this->uris());
    }

    public function test_item_types_this_app_does_not_handle_are_not_linked(): void
    {
        ResourceType::query()->delete();
        $this->apiPermits([$this->apiResourceType('rt-9', 'Dice', 'game')]);

        $this->signedIn()->get('/resource-types')->assertRedirect('/resource-types/create');

        $this->assertSame(0, ResourceType::count());
    }

    public function test_nothing_is_linked_when_the_subtype_cannot_be_looked_up(): void
    {
        ResourceType::query()->delete();
        $this->apiPermits([$this->apiResourceType('rt-9', 'Kids')], subtypesStatus: 500);

        $this->signedIn()->get('/resource-types')->assertRedirect('/resource-types/create');

        $this->assertSame(0, ResourceType::count());
    }
}
