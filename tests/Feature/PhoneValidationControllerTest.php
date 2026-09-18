<?php

it('validates and formats an Ecuadorian mobile number', function () {
    $this->postJson('/api/phone/validate', [
        'country_code' => 'EC',
        'number' => '0984923396',
    ])->assertOk()
        ->assertJsonPath('data.valid', true)
        ->assertJsonPath('data.formatted', '+593984923396');
});

it('rejects an invalid phone number for the selected country', function () {
    $this->postJson('/api/phone/validate', [
        'country_code' => 'EC',
        'number' => '098492339',
    ])->assertOk()
        ->assertJsonPath('data.valid', false)
        ->assertJsonPath('data.formatted', null);
});
