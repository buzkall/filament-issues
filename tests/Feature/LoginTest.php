<?php

use App\Models\User;
use Filament\Facades\Filament;

test('Admin logout redirects to the general login page', function() {
    $user = User::factory()->create();

    $panel = Filament::getPanel('admin');
    Filament::setCurrentPanel($panel);

    $this->actingAs($user)
        ->post(Filament::getLogoutUrl())
        ->assertRedirect('/login');

    $this->assertGuest();
});
