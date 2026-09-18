<?php

use App\Services\EcuadorianIdentityService;

beforeEach(function () {
    $this->service = app(EcuadorianIdentityService::class);
});

it('validates an Ecuadorian identity card using its check digit', function () {
    expect($this->service->isValidCedula('1710034065'))->toBeTrue()
        ->and($this->service->isValidCedula('1710034064'))->toBeFalse();
});

it('rejects invalid province, length and non-numeric values', function () {
    expect($this->service->isValidCedula('9910034065'))->toBeFalse()
        ->and($this->service->isValidCedula('171003406'))->toBeFalse()
        ->and($this->service->isValidCedula('17100340A5'))->toBeFalse();
});
