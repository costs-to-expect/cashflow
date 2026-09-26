<?php

declare(strict_types=1);

namespace App\Http\Controllers\Action;

use App\Actions\Authentication\SignIn;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{
    public function signIn(Request $request, SignIn $signIn): RedirectResponse
    {
        $result = $signIn($request->input('email', ''), $request->input('password', ''));

        if ($result->ok) {
            $request->session()->regenerate();

            return redirect()->route('dashboard');
        }

        return $this->redirectForApiResult($result, 'dashboard');
    }

    public function signOut(): RedirectResponse
    {
        Auth::guard('web')->logout();

        return redirect()->route('auth.sign-in');
    }
}
