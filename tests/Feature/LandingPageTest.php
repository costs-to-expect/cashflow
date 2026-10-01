<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\FakesTheApi;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use FakesTheApi;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpApiPages();
    }

    public function test_guests_are_offered_sign_in_and_a_way_to_ask_for_access(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Money in. Money out.')
            ->assertSee(route('auth.sign-in'), false)
            ->assertSee('Request alpha access')
            ->assertDontSee('Go to dashboard');
    }

    public function test_it_links_to_the_live_example_built_on_the_api(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Live example')
            ->assertSee('href="https://www.costs-to-expect.com"', false);
    }

    public function test_signed_in_users_are_offered_their_dashboard_instead(): void
    {
        $this->signedIn()->get('/')
            ->assertOk()
            ->assertSee('Go to dashboard')
            ->assertSee(route('resource-types.index'), false)
            ->assertDontSee('Request alpha access');
    }

    public function test_the_example_dashboard_is_dressed_as_a_demo_and_hidden_from_assistive_tech(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('using made-up figures')
            ->assertSee('aria-hidden="true" inert', false)
            // Built from the real dashboard components: its totals and period switcher show up.
            ->assertSee('6,418.50')
            ->assertSee('All time');
    }
}
