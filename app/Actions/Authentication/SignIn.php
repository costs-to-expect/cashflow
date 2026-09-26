<?php

declare(strict_types=1);

namespace App\Actions\Authentication;

use App\Actions\ApiActionResult;
use App\Auth\Guard\Api\Guard;
use Illuminate\Support\Facades\Auth;

class SignIn
{
    public function __invoke(string $email, string $password): ApiActionResult
    {
        if (trim($email) === '' || trim($password) === '') {
            return ApiActionResult::validationFailed([
                'email' => ['Please enter your email address and password.'],
            ]);
        }

        /** @var Guard $guard */
        $guard = Auth::guard('web');

        if ($guard->attempt($email, $password)) {
            return ApiActionResult::success();
        }

        return ApiActionResult::validationFailed($guard->errors());
    }
}
