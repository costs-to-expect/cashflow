<?php

namespace Tests\Feature;

use App\Models\ResourceType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoriesSettingTest extends TestCase
{
    use RefreshDatabase;

    private function resourceType(string $name): ResourceType
    {
        return ResourceType::create([
            'user_id' => 'user-1',
            'name' => $name,
            'description' => $name,
            'item_type' => 'allocated-expense',
            'api_resource_type_id' => "api-{$name}",
            'api_item_type_id' => 'item-type',
            'item_subtype_id' => 'item-subtype',
        ]);
    }

    public function test_categories_are_on_until_turned_off(): void
    {
        $this->assertTrue($this->resourceType('Kids')->categoriesEnabled());
    }

    public function test_categories_can_be_turned_off_and_back_on(): void
    {
        $resourceType = $this->resourceType('Kids');

        $resourceType->setCategoriesEnabled(false);
        $this->assertFalse($resourceType->fresh()->categoriesEnabled());

        $resourceType->setCategoriesEnabled(true);
        $this->assertTrue($resourceType->fresh()->categoriesEnabled());
    }

    public function test_the_setting_is_scoped_to_its_resource_type(): void
    {
        $kids = $this->resourceType('Kids');
        $household = $this->resourceType('Household');

        $kids->setCategoriesEnabled(false);

        $this->assertFalse($kids->categoriesEnabled());
        $this->assertTrue($household->categoriesEnabled());
    }
}
