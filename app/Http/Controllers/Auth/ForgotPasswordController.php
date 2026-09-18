<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Ingresa el correo asociado a tu cuenta.',
            'email.email' => 'Ingresa un correo electrónico válido.',
        ]);

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [mb_strtolower($validated['email'], 'UTF-8')])
            ->first();

        if ($user) {
            Password::sendResetLink(['email' => $user->email]);
        }

        return back()->with(
            'status',
            'Si el correo pertenece a una cuenta, recibirás un enlace para recuperar tu contraseña.',
        );
    }
}
