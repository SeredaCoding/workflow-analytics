<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        $email = $this->resolveEmailFromToken($request->route('token'));

        if (! $email) {
            return redirect()->route('login')->with('error', 'Link de redefinição inválido ou expirado.');
        }

        return Inertia::render('Auth/ResetPassword', [
            'email' => $email,
            'token' => $request->route('token'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $email = $this->resolveEmailFromToken($request->token);

        if (! $email) {
            return redirect()->route('login')->with('error', 'Link de redefinição inválido ou expirado.');
        }

        $status = Password::reset(
            ['email' => $email, 'password' => $request->password, 'password_confirmation' => $request->password_confirmation, 'token' => $request->token],
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status == Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }

    private function resolveEmailFromToken(string $token): ?string
    {
        return DB::table('password_reset_tokens')
            ->where('created_at', '>', now()->subMinutes(60))
            ->get()
            ->first(fn ($record) => Hash::check($token, $record->token))
            ?->email;
    }
}
