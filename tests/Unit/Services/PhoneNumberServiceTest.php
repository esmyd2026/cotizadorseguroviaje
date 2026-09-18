<?php

use App\Services\PhoneNumberService;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->service = app(PhoneNumberService::class);
});

it('returns the dial code for a region', function () {
    expect($this->service->dialCodeFor('EC'))->toBe('+593')
        ->and($this->service->dialCodeFor('US'))->toBe('+1');
});

it('returns the possible national number length range for a region', function () {
    $length = $this->service->possibleLengthFor('US');

    expect($length)->not->toBeNull()
        ->and($length['min'])->toBe(10)
        ->and($length['max'])->toBe(10);
});

it('validates a well-formed national number for its region', function () {
    expect($this->service->isValidNumber('2025550123', 'US'))->toBeTrue()
        ->and($this->service->isValidNumber('123', 'US'))->toBeFalse();
});

it('formats a valid number to E.164', function () {
    expect($this->service->toE164('2025550123', 'US'))->toBe('+12025550123')
        ->and($this->service->toE164('123', 'US'))->toBeNull();
});
