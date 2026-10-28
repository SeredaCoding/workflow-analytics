<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = User::findOrFail($request->route('id'));

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect(route('login'))->with('status', 'E-mail já verificado. Faça login.');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect(route('login'))->with('status', 'E-mail verificado com sucesso! Faça login.');
    }
}
