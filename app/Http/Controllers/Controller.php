<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\ApiActionResult;
use App\Models\ResourceType;
use App\Service\Api\RequestPool;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\MessageBag;

abstract class Controller
{
    /**
     * Turn an Action's result (see App\Actions\ApiActionResult) into a
     * redirect: validation errors go back to the form (native Laravel
     * $errors bag + old input), anything else that isn't a success aborts
     * with a flashed danger message, success flashes a status message and
     * redirects on.
     */
    protected function redirectForApiResult(
        ApiActionResult $result,
        string $successRoute,
        array $successParams = [],
        string $successMessage = 'Done.',
    ): RedirectResponse {
        if ($result->ok) {
            return redirect()
                ->route($successRoute, $successParams)
                ->with('status', $successMessage);
        }

        if ($result->fieldErrors !== []) {
            $bag = new MessageBag;

            foreach ($result->fieldErrors as $field => $messages) {
                // The API nests each field's messages under an "errors" key
                // (e.g. {"name": {"errors": ["..."]}}) rather than a flat list.
                $messages = $messages['errors'] ?? $messages;

                foreach ((array) $messages as $message) {
                    $bag->add($field, $message);
                }
            }

            return back()->withErrors($bag)->withInput();
        }

        report(new \RuntimeException(
            'Costs to Expect API request failed unexpectedly: '.$result->message
        ));

        return back()->with('danger', 'Something went wrong talking to the API, please try again.')->withInput();
    }

    /**
     * Validation rules for the category/subcategory pair on the expense forms:
     * both required while the resource type has categories turned on, and no
     * rules at all while it's off - the fields aren't on the form then, so
     * anything posted for them is left out of the validated data.
     */
    protected function categoryRules(ResourceType $resourceType): array
    {
        if (! $resourceType->categoriesEnabled()) {
            return [];
        }

        return [
            'category_id' => ['required', 'string'],
            'subcategory_id' => ['required', 'string'],
        ];
    }

    /**
     * GBP first, then whatever order the API returned the rest in.
     */
    protected function sortCurrenciesGbpFirst(array $currencies): array
    {
        usort($currencies, fn (array $a, array $b) => $this->currencyRank($a) <=> $this->currencyRank($b));

        return $currencies;
    }

    private function currencyRank(array $currency): int
    {
        return ($currency['code'] ?? '') === 'GBP' ? 0 : 1;
    }

    /**
     * The configured default currency if there is one, otherwise the first
     * of the (GBP-first sorted) currencies passed in.
     */
    protected function resolveDefaultCurrencyId(array $sortedCurrencies): ?string
    {
        return config('app.api.default_currency_id') ?: ($sortedCurrencies[0]['id'] ?? null);
    }

    /**
     * What a pooled read returned, or nothing if it failed (or was never
     * asked for) - a page shows an empty list rather than an error for a
     * read that didn't come back.
     *
     * @return array<int|string, mixed>
     */
    protected function content(?array $response): array
    {
        return $response !== null && $response['status'] === 200 ? $response['content'] : [];
    }

    /**
     * The first wave of the expense and recurring-expense forms: everything
     * they need that doesn't depend on another response - the nav, the
     * currencies and, while categories are turned on, the categories.
     */
    protected function poolFormOptions(RequestPool $pool, ResourceType $resourceType): void
    {
        $pool->navigation()->currencies();

        if ($resourceType->categoriesEnabled()) {
            $pool->categories();
        }
    }

    /**
     * The second wave of the forms, which needs the categories: each one's
     * subcategories (see subcategoriesByCategory() for reading them back).
     *
     * @param  array<int, array<string, mixed>>  $categories
     */
    protected function poolSubcategories(RequestPool $pool, array $categories): void
    {
        foreach ($categories as $category) {
            $pool->subcategories('subcategories.'.$category['id'], $category['id']);
        }
    }

    /**
     * Each category's subcategories, as the id/name pairs the forms' selects
     * need, keyed by category id.
     *
     * @param  array<int, array<string, mixed>>  $categories
     * @param  array<string, array>  $responses  the pool poolSubcategories() was added to
     * @return array<string, list<array{id: string, name: string}>>
     */
    protected function subcategoriesByCategory(array $categories, array $responses): array
    {
        $map = [];

        foreach ($categories as $category) {
            $map[$category['id']] = collect($this->content($responses['subcategories.'.$category['id']]))
                ->map(fn ($subcategory) => ['id' => $subcategory['id'], 'name' => $subcategory['name']])
                ->all();
        }

        return $map;
    }
}
