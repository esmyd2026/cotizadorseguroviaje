<?php

use App\Actions\Users\ProvisionCustomerAccountAction;
use App\Enums\UserRole;
use App\Models\Insured;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

it('lets an admin log in with their email and reach the admin panel', function () {
    $admin = User::factory()->admin()->create(['password' => 'correct-password']);

    $this->post('/login', [
        'login' => $admin->email,
        'password' => 'correct-password',
    ])->assertRedirect(route('admin.quotes.index'));

    $this->assertAuthenticatedAs($admin);
});

it('resolves a document number as a username credential, not an email', function () {
    $insured = Insured::factory()->create(['document_id' => '1710034065']);
    app(ProvisionCustomerAccountAction::class)
        ->execute($insured, $insured->email, "{$insured->first_name} {$insured->last_name}");

    expect(Auth::attempt(['username' => '1710034065', 'password' => '1710034065']))->toBeTrue();
    Auth::logout();

    // The admin-only login gate (covered separately below) still applies —
    // this test isolates credential resolution from that authorization rule.
    $this->post('/login', [
        'login' => '1710034065',
        'password' => '1710034065',
    ])->assertRedirect('/login');

    $this->assertGuest();
});

it('rejects an incorrect password', function () {
    $admin = User::factory()->admin()->create(['password' => 'correct-password']);

    $this->post('/login', [
        'login' => $admin->email,
        'password' => 'wrong-password',
    ])->assertSessionHasErrors('login');

    $this->assertGuest();
});

it('logs a customer account back out and denies admin access', function () {
    $customer = User::factory()->create(['password' => 'correct-password']);

    $this->post('/login', [
        'login' => $customer->email,
        'password' => 'correct-password',
    ])->assertRedirect('/login');

    $this->assertGuest();
});

it('logs the user out', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post('/logout')
        ->assertRedirect('/login');

    $this->assertGuest();
});

it('provisions a customer account with the document id as username and password', function () {
    $insured = Insured::factory()->create(['document_id' => '1710034065']);

    $user = app(ProvisionCustomerAccountAction::class)
        ->execute($insured, $insured->email, "{$insured->first_name} {$insured->last_name}");

    expect($user->role)->toBe(UserRole::Customer)
        ->and($user->username)->toBe('1710034065')
        ->and($user->insured_id)->toBe($insured->id)
        ->and(Hash::check('1710034065', $user->password))->toBeTrue();
});

it('does not duplicate the account on a repeat quote for the same insured', function () {
    $insured = Insured::factory()->create();
    $action = app(ProvisionCustomerAccountAction::class);

    $first = $action->execute($insured, $insured->email, "{$insured->first_name} {$insured->last_name}");
    $second = $action->execute($insured, $insured->email, "{$insured->first_name} {$insured->last_name}");

    expect(User::where('insured_id', $insured->id)->count())->toBe(1)
        ->and($first->id)->toBe($second->id);
});

it('disambiguates a username collision between two different insureds', function () {
    $first = Insured::factory()->create(['document_type' => 'cedula', 'document_id' => '11223344']);
    $second = Insured::factory()->create(['document_type' => 'passport', 'document_id' => '11223344']);
    $action = app(ProvisionCustomerAccountAction::class);

    $firstUser = $action->execute($first, $first->email, 'Uno');
    $secondUser = $action->execute($second, $second->email, 'Dos');

    expect($firstUser->username)->toBe('11223344')
        ->and($secondUser->username)->toBe('11223344-2');
});

it('completes the password reset flow end to end', function () {
    $admin = User::factory()->admin()->create();

    $token = Password::createToken($admin);

    $this->post('/password/reset', [
        'token' => $token,
        'email' => $admin->email,
        'password' => 'a-brand-new-password',
        'password_confirmation' => 'a-brand-new-password',
    ])->assertRedirect(route('login'));

    $this->post('/login', [
        'login' => $admin->email,
        'password' => 'a-brand-new-password',
    ])->assertRedirect(route('admin.quotes.index'));

    $this->assertAuthenticatedAs($admin);
});
