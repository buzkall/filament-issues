<?php

namespace App\Filament\Client\Pages;

use Filament\Pages\Page;

class Client extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.client.pages.client';
}
