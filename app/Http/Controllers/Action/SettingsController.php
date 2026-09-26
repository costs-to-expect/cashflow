<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action;

use App\Actions\Settings\SaveDefaultSplit;
use App\Http\Controllers\Controller;
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
}
