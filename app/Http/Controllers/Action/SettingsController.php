<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action;

use App\Actions\Category\CreateCategory;
use App\Actions\Category\CreateSubcategory;
use App\Actions\Category\UpdateCategory;
use App\Actions\Category\UpdateSubcategory;
use App\Actions\Settings\SaveDefaultSplit;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function saveDefaultSplit(Request $request, SaveDefaultSplit $saveDefaultSplit): RedirectResponse
    {
        $validated = $request->validate([
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.resource_id' => ['required', 'string'],
            'allocations.*.percentage' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $saveDefaultSplit($validated['allocations']);

        return redirect()->route('settings.default-split')->with('status', 'Default split saved.');
    }

    public function saveResourceNaming(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'singular' => ['required', 'string', 'max:255'],
            'plural' => ['required', 'string', 'max:255'],
        ]);

        Setting::set('resource_term_singular', $validated['singular']);
        Setting::set('resource_term_plural', $validated['plural']);

        return redirect()->route('settings.resource-naming')->with('status', 'Naming saved.');
    }

    public function storeCategory(Request $request, CreateCategory $createCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        $result = $createCategory($validated['name'], $validated['description']);

        return $this->redirectForApiResult($result, 'settings.categories', [], "{$validated['name']} has been added.");
    }

    /**
     * Every category on the settings page has its own update form, all
     * rendered at once - fields are named categories[{id}][name] etc so a
     * failed submission's old()/error state can't bleed into other cards.
     */
    public function updateCategory(Request $request, string $category_id, UpdateCategory $updateCategory): RedirectResponse
    {
        $validated = $request->validate([
            "categories.{$category_id}.name" => ['required', 'string', 'max:255'],
            "categories.{$category_id}.description" => ['required', 'string'],
        ]);

        $name = $validated['categories'][$category_id]['name'];
        $description = $validated['categories'][$category_id]['description'];

        $result = $updateCategory($category_id, $name, $description);

        return $this->redirectForApiResult($result, 'settings.categories', [], "{$name} has been updated.");
    }

    public function storeSubcategory(Request $request, string $category_id, CreateSubcategory $createSubcategory): RedirectResponse
    {
        $validated = $request->validate([
            "new_subcategories.{$category_id}.name" => ['required', 'string', 'max:255'],
            "new_subcategories.{$category_id}.description" => ['required', 'string'],
        ]);

        $name = $validated['new_subcategories'][$category_id]['name'];
        $description = $validated['new_subcategories'][$category_id]['description'];

        $result = $createSubcategory($category_id, $name, $description);

        return $this->redirectForApiResult($result, 'settings.categories', [], "{$name} has been added.");
    }

    public function updateSubcategory(Request $request, string $category_id, string $subcategory_id, UpdateSubcategory $updateSubcategory): RedirectResponse
    {
        $validated = $request->validate([
            "subcategories.{$subcategory_id}.name" => ['required', 'string', 'max:255'],
            "subcategories.{$subcategory_id}.description" => ['required', 'string'],
        ]);

        $name = $validated['subcategories'][$subcategory_id]['name'];
        $description = $validated['subcategories'][$subcategory_id]['description'];

        $result = $updateSubcategory($category_id, $subcategory_id, $name, $description);

        return $this->redirectForApiResult($result, 'settings.categories', [], "{$name} has been updated.");
    }
}
