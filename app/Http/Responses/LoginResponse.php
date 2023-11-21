<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse extends \Filament\Http\Responses\Auth\LoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        if (auth()->user()->role === 'admin') {
            return redirect(route('filament.admin.pages.dashboard'));
        }

        if (auth()->user()->role === 'federation') {
            return redirect(route('filament.federation.pages.dashboard'));
        }

        return redirect(route('filament.client.pages.dashboard'));
    }
}
