<?php

namespace Tests\Unit;

use App\Support\ResourceColours;
use PHPUnit\Framework\TestCase;

class ResourceColoursTest extends TestCase
{
    public function test_each_resource_gets_a_colour_keyed_by_its_id(): void
    {
        $colours = ResourceColours::forResources([['id' => 'a'], ['id' => 'b']]);

        $this->assertSame(['a', 'b'], array_keys($colours));
        $this->assertNotSame($colours['a'], $colours['b']);
        $this->assertArrayHasKey('bar', $colours['a']);
        $this->assertArrayHasKey('hex', $colours['a']);
    }

    public function test_colours_are_assigned_in_display_order_and_wrap_around(): void
    {
        $resources = array_map(fn (int $id) => ['id' => $id], range(1, 8));

        $colours = ResourceColours::forResources($resources);

        // Six colours in the palette, so the 7th and 8th resources reuse the 1st and 2nd.
        $this->assertSame($colours[1], $colours[7]);
        $this->assertSame($colours[2], $colours[8]);
        $this->assertNotSame($colours[1], $colours[2]);
    }

    public function test_no_resources_means_no_colours(): void
    {
        $this->assertSame([], ResourceColours::forResources([]));
    }
}
