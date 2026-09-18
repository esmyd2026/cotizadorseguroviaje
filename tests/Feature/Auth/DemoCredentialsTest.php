<?php

it('shows the demo credentials box on the login screen in local', function () {
    config(['demo.show_credentials' => true, 'demo.admin_password' => 'AdminSeguro2026!']);

    $this->get('/login')
        ->assertOk()
        ->assertSee('Acceso de demostración')
        ->assertSee(config('demo.admin_email'))
        ->assertSee('AdminSeguro2026!')
        ->assertSee(config('demo.customer_document_id'));
});

it('hides the demo credentials box outside the local environment', function () {
    config(['demo.show_credentials' => false, 'demo.admin_password' => 'AdminSeguro2026!']);

    $this->get('/login')
        ->assertOk()
        ->assertDontSee('Acceso de demostración');
});

it('hides the demo credentials box when no admin password is configured', function () {
    config(['demo.show_credentials' => true, 'demo.admin_password' => null]);

    $this->get('/login')
        ->assertOk()
        ->assertDontSee('Acceso de demostración');
});
