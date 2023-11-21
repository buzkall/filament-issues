<?php

namespace App\Providers;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Filament\Http\Responses\Auth\LoginResponse;
use Filament\Http\Responses\Auth\LogoutResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
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

        Field::macro('withImageCaption', fn($collection) => $this->hintAction(
            fn(Set $set, $record) => Action::make('Custom_properties')
                ->icon('heroicon-o-camera')
                ->tooltip(__('Add a caption to the images'))
                ->label(__('Image captions'))
                ->form([
                    Select::make('media_id')
                        ->label(__('Image name'))
                        ->options([1 => 'option 1', 2 => 'option 2'])
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function($state, $record, Set $set) {

                            // force sleep
                            sleep(2);

                            $set('description_es', 'test es');
                            $set('description_en', 'test en');
                        }),

                    Section::make('description')
                        ->heading(__('Image caption'))
                        ->compact()
                        ->schema(
                            [
                                TextInput::make('description_es')
                                    ->label(__('Spanish')),
                                TextInput::make('description_en')
                                    ->label(__('English')),
                            ]
                        )->hidden(fn(callable $get) => ! $get('media_id')),

                ])
                ->modalSubmitActionLabel(__('Save'))
                ->action(function($data) use ($record) {
                    $record->media->find($data['media_id'])->setCustomProperty('description', [
                        'es' => $data['description_es'],
                        'en' => $data['description_en'],
                    ]);
                    $record->push();
                })
        ));
    }
}
