<?php

namespace App\Filament\Resources\ClientResource\Pages;

use App\Filament\Resources\ClientResource;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ClientWelcomeNotification;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Str;

class CreateClient extends CreateRecord
{
    protected static bool $canCreateAnother = false;
    protected static string $resource = ClientResource::class;

}
