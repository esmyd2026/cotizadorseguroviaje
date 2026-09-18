<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

function actingAsAdmin(): User
{
    $admin = User::factory()->admin()->create();
    test()->actingAs($admin);

    return $admin;
}
