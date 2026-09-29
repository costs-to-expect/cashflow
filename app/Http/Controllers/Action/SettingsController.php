<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action;

use App\Actions\Category\CreateCategory;
use App\Actions\Category\CreateSubcategory;
use App\Actions\Category\UpdateCategory;
use App\Actions\Category\UpdateSubcategory;
use App\Actions\Settings\SaveDefaultSplit;
use App\Http\Controllers\Controller;
use App\Models\ReportingPeriod;
use App\Models\ResourceType;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function saveDefaultSplit(Request $request, ResourceType $resourceType, SaveDefaultSplit $saveDefaultSplit): RedirectResponse
    {
        $validated = $request->validate([
            'allocations' => ['required', 'array', 'min:1'],
            'allocations.*.resource_id' => ['required', 'string'],
            'allocations.*.percentage' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $saveDefaultSplit($resourceType, $validated['allocations']);

        return redirect()->route('settings.default-split', $resourceType)->with('status', 'Default split saved.');
    }

    public function saveResourceNaming(Request $request, ResourceType $resourceType): RedirectResponse
    {
        $validated = $request->validate([
            'singular' => ['required', 'string', 'max:255'],
            'plural' => ['required', 'string', 'max:255'],
        ]);

        Setting::set('resource_term_singular', $validated['singular'], $resourceType);
        Setting::set('resource_term_plural', $validated['plural'], $resourceType);

        return redirect()->route('settings.resource-naming', $resourceType)->with('status', 'Naming saved.');
    }

    public function storeCategory(Request $request, ResourceType $resourceType, CreateCategory $createCategory): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        $result = $createCategory($validated['name'], $validated['description']);

        return $this->redirectForApiResult($result, 'settings.categories', ['resourceType' => $resourceType], "{$validated['name']} has been added.");
    }

    /**
     * Every category on the settings page has its own update form, all
     * rendered at once - fields are named categories[{id}][name] etc so a
     * failed submission's old()/error state can't bleed into other cards.
     */
    public function updateCategory(Request $request, ResourceType $resourceType, string $category_id, UpdateCategory $updateCategory): RedirectResponse
    {
        $validated = $request->validate([
            "categories.{$category_id}.name" => ['required', 'string', 'max:255'],
            "categories.{$category_id}.description" => ['required', 'string'],
        ]);

        $name = $validated['categories'][$category_id]['name'];
        $description = $validated['categories'][$category_id]['description'];

        $result = $updateCategory($category_id, $name, $description);

        return $this->redirectForApiResult($result, 'settings.categories', ['resourceType' => $resourceType], "{$name} has been updated.");
    }

    public function storeSubcategory(Request $request, ResourceType $resourceType, string $category_id, CreateSubcategory $createSubcategory): RedirectResponse
    {
        $validated = $request->validate([
            "new_subcategories.{$category_id}.name" => ['required', 'string', 'max:255'],
            "new_subcategories.{$category_id}.description" => ['required', 'string'],
        ]);

        $name = $validated['new_subcategories'][$category_id]['name'];
        $description = $validated['new_subcategories'][$category_id]['description'];

        $result = $createSubcategory($category_id, $name, $description);

        return $this->redirectForApiResult($result, 'settings.categories', ['resourceType' => $resourceType], "{$name} has been added.");
    }

    public function updateSubcategory(Request $request, ResourceType $resourceType, string $category_id, string $subcategory_id, UpdateSubcategory $updateSubcategory): RedirectResponse
    {
        $validated = $request->validate([
            "subcategories.{$subcategory_id}.name" => ['required', 'string', 'max:255'],
            "subcategories.{$subcategory_id}.description" => ['required', 'string'],
        ]);

        $name = $validated['subcategories'][$subcategory_id]['name'];
        $description = $validated['subcategories'][$subcategory_id]['description'];

        $result = $updateSubcategory($category_id, $subcategory_id, $name, $description);

        return $this->redirectForApiResult($result, 'settings.categories', ['resourceType' => $resourceType], "{$name} has been updated.");
    }

    public function storePeriod(Request $request, ResourceType $resourceType): RedirectResponse
    {
        $validated = $request->validate($this->periodRules());

        ReportingPeriod::create([
            ...$validated,
            'resource_type_id' => $resourceType->id,
            'sort_order' => ReportingPeriod::query()->where('resource_type_id', $resourceType->id)->max('sort_order') + 1,
        ]);

        return redirect()->route('settings.periods', $resourceType)->with('status', "{$validated['name']} has been added.");
    }

    /**
     * Every period has its own update form, all rendered at once - fields
     * are named periods[{id}][field] so a failed submission's old()/error
     * state can't bleed into other cards.
     */
    public function updatePeriod(Request $request, ResourceType $resourceType, ReportingPeriod $reportingPeriod): RedirectResponse
    {
        abort_unless($reportingPeriod->resource_type_id === $resourceType->id, 404);

        $validated = $request->validate($this->periodRules("periods.{$reportingPeriod->id}."));

        $data = $validated['periods'][$reportingPeriod->id];

        $reportingPeriod->update($data);

        return redirect()->route('settings.periods', $resourceType)->with('status', "{$data['name']} has been updated.");
    }

    public function destroyPeriod(ResourceType $resourceType, ReportingPeriod $reportingPeriod): RedirectResponse
    {
        abort_unless($reportingPeriod->resource_type_id === $resourceType->id, 404);

        $name = $reportingPeriod->name;
        $reportingPeriod->delete();

        return redirect()->route('settings.periods', $resourceType)->with('status', "{$name} has been removed.");
    }

    private function periodRules(string $prefix = ''): array
    {
        return [
            "{$prefix}name" => ['required', 'string', 'max:255'],
            "{$prefix}start_month" => ['required', 'integer', 'min:1', 'max:12'],
            "{$prefix}start_day" => ['required', 'integer', 'min:1', 'max:31'],
            "{$prefix}end_month" => ['required', 'integer', 'min:1', 'max:12'],
            "{$prefix}end_day" => ['required', 'integer', 'min:1', 'max:31'],
        ];
    }
}
