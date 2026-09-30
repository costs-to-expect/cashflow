<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class ExpenseDetailsComponentTest extends TestCase
{
    use RefreshDatabase;

    private array $categories = [
        ['id' => 'c1', 'name' => 'Food'],
        ['id' => 'c2', 'name' => 'Travel'],
    ];

    private array $subcategoriesByCategory = [
        'c1' => [['id' => 's1', 'name' => 'Groceries'], ['id' => 's2', 'name' => 'Takeaway']],
        'c2' => [['id' => 's3', 'name' => 'Train']],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        // Normally shared by the web middleware, which a bare render skips.
        view()->share('errors', new ViewErrorBag);
    }

    private function render(bool $enabled, array $extra = [])
    {
        return $this->blade(
            '<x-expense.details :categories-enabled="$enabled" :categories="$categories" :subcategories-by-category="$subcategoriesByCategory" :category-id="$categoryId" :subcategory-id="$subcategoryId" />',
            ['enabled' => $enabled, 'categories' => $this->categories, 'subcategoriesByCategory' => $this->subcategoriesByCategory, 'categoryId' => null, 'subcategoryId' => null, ...$extra],
        );
    }

    public function test_the_category_fields_are_removed_while_categories_are_off(): void
    {
        $this->render(false)
            ->assertSee('id="name"', false)
            ->assertDontSee('id="category_id"', false)
            ->assertDontSee('id="subcategory_id"', false);
    }

    public function test_both_fields_are_required_with_no_none_option_while_categories_are_on(): void
    {
        $view = $this->render(true);
        $html = (string) $view;

        // The select component spreads its attributes over several lines.
        $this->assertMatchesRegularExpression('/<select\s[^>]*id="category_id"[^>]*\srequired\b/', $html);
        $this->assertMatchesRegularExpression('/<select\s[^>]*id="subcategory_id"[^>]*\srequired\b/', $html);

        $view->assertDontSee('None');
    }

    public function test_the_first_category_and_its_subcategories_are_rendered_by_default(): void
    {
        $this->render(true)
            ->assertSeeInOrder(['value="c1"', 'value="c2"'], false)
            ->assertSee('value="s1"', false)
            ->assertSee('value="s2"', false)
            ->assertDontSee('value="s3"', false);
    }

    public function test_an_existing_categorisation_is_preselected(): void
    {
        $this->render(true, ['categoryId' => 'c2', 'subcategoryId' => 's3'])
            ->assertSee('<option value="c2" selected>', false)
            ->assertSee('<option value="s3" selected>', false)
            ->assertDontSee('value="s1"', false);
    }
}
