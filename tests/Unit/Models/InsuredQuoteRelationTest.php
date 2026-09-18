<?php

use App\Models\Insured;
use App\Models\Quote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('associates a quote with its insured', function () {
    $insured = Insured::factory()->create();
    $quote = Quote::factory()->for($insured)->create();

    expect($insured->quotes)->toHaveCount(1)
        ->and($insured->quotes->first()->is($quote))->toBeTrue()
        ->and($quote->insured->is($insured))->toBeTrue();
});

it('deletes an insured\'s quotes when the insured is deleted', function () {
    $insured = Insured::factory()->create();
    Quote::factory()->for($insured)->count(2)->create();

    $insured->delete();

    expect(Quote::count())->toBe(0);
});
