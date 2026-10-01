<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\FakesTheApi;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use FakesTheApi;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpApiPages();
    }

    public function test_the_sitemap_lists_only_the_public_landing_page(): void
    {
        $response = $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('<loc>'.route('welcome').'</loc>', false)
            ->assertDontSee('sign-in')
            ->assertDontSee('resource-types');

        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $response->getContent());
    }

    public function test_robots_txt_points_at_the_sitemap_and_blocks_the_private_areas(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Sitemap: '.route('sitemap'), false)
            ->assertSee('Disallow: /resource-types', false)
            ->assertSee('Disallow: /sign-in', false);
    }

    public function test_the_landing_page_is_indexable_with_a_canonical_url_and_social_tags(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<meta name="robots" content="index, follow">', false)
            ->assertSee('<link rel="canonical" href="'.url('/').'">', false)
            ->assertSee('<meta name="description"', false)
            ->assertSee('<meta property="og:title"', false)
            ->assertSee('<meta name="twitter:card" content="summary">', false);
    }

    public function test_the_sign_in_page_has_its_own_title_and_is_not_indexed(): void
    {
        $this->get(route('auth.sign-in'))
            ->assertOk()
            ->assertSee('<title>Sign in | ', false)
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false)
            ->assertDontSee('rel="canonical"', false);
    }

    public function test_signed_in_pages_are_not_indexed(): void
    {
        $this->signedIn()->get(route('dashboard', $this->resourceType))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }
}
