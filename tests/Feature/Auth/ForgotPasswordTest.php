<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_contains_the_expected_accessibility_and_recovery_controls(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Gestión Segura')
            ->assertSee('¿Olvidaste tu contraseña?')
            ->assertSee('Mostrar contraseña')
            ->assertSee('Continuar con Google')
            ->assertSee(route('password.request'));
    }

    public function test_forgot_password_page_can_be_opened(): void
    {
        $this->get('/forgot-password')
            ->assertOk()
            ->assertSee('Recupera tu acceso');
    }

    public function test_reset_link_can_be_requested_case_insensitively(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'CLIENTE@EXAMPLE.COM']);

        $this->post('/forgot-password', ['email' => 'cliente@example.com'])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_unknown_email_receives_the_same_non_enumerating_response(): void
    {
        Notification::fake();

        $this->post('/forgot-password', ['email' => 'UNKNOWN@EXAMPLE.COM'])
            ->assertSessionHas('status');

        Notification::assertNothingSent();
    }

    public function test_invalid_email_is_rejected(): void
    {
        $this->post('/forgot-password', ['email' => 'correo-invalido'])
            ->assertSessionHasErrors('email');
    }
}
