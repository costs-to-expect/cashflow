<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
        \App\Actions\ApiActionResult $result,
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
}
