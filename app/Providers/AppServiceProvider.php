<?php

namespace App\Providers;

use Filament\Forms\Components\Field;
use Filament\Http\Responses\Auth\LoginResponse;
use Filament\Http\Responses\Auth\LogoutResponse;
use Filament\Pages\Page;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\ValidationException;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // override the default login/logout responses
        $this->app->singleton(LoginResponse::class, \App\Http\Responses\LoginResponse::class);
        $this->app->singleton(LogoutResponse::class, \App\Http\Responses\LogoutResponse::class);

        Page::$reportValidationErrorUsing = function(ValidationException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
        };

        Field::configureUsing(fn($field) => $field->translateLabel());
        Column::configureUsing(fn($field) => $field->translateLabel());
        SelectFilter::configureUsing(fn($field) => $field->translateLabel());
        TextColumn::configureUsing(fn(TextColumn $column) => $column->sortable());

        Table::configureUsing(fn(Table $table) => $table
            ->striped()
            ->paginationPageOptions([25, 50, 100]));

        FilamentView::registerRenderHook(
            'panels::footer',
            fn(): View => view('filament/footer'),
        );
    }
}
