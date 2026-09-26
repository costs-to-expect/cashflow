<?php

declare(strict_types=1);

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AuthenticationController extends Controller
{
    public function signIn(): View
    {
        return view('auth.sign-in');
    }
}
