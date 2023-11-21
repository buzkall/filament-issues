<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Models\User;
use Carbon\Carbon;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $modelLabel = 'Client';
    protected static ?string $pluralModelLabel = 'Clients';

    public static function form(Form $form): Form
    {
        return $form->schema(
            [
                Tabs::make()
                    ->persistTabInQueryString()
                    ->tabs(
                        [
                            Tab::make(__('Personal data'))
                                ->schema(self::getFormSchemaPersonalData())
                                ->columns(),

                            Tab::make(__('Contact data'))
                                ->schema(self::getFormSchemaContactData())
                                ->columns(),

                            Tab::make(__('Disability general data'))
                                ->schema(self::getFormSchemaDisabilityGeneralData())
                                ->columns(),

                            Tab::make(__('Hearing disability data'))
                                ->schema(self::getFormSchemaHearingDisabilityData())
                                ->columns(),

                            Tab::make(__('Entity data'))
                                ->schema(self::getFormSchemaEntityData())
                                ->columns(),
                        ]
                    )
            ]
        )->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table->columns(
            [
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
            ]
        )
            ->filters([

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->modifyQueryUsing(fn($query) => $query->where('role', 'client'));
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    public static function getFormSchemaPersonalData(): array
    {
        return [
            Group::make()
                ->columns(3)
                ->columnSpanFull()
                ->schema(
                    [
                        TextInput::make('name')
                            ->required(),

                        TextInput::make('surname')
                            ->required(),

                        FileUpload::make('avatar')
                            ->avatar()
                        ->withImageCaption('avatar')
                    ]
                ),


            Group::make()->schema(
                [
                    DatePicker::make('birth_date')
                        ->maxDate(today())
                        ->required()
                        ->live()
                        ->suffix(fn($state) => $state ? Carbon::parse($state)->age . ' ' . __('years old') : null),

                    Select::make('gender_id')
                        ->required()
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->name),

                    Select::make('legal_document_type_id')
                        ->required()
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->name),

                    TextInput::make('legal_document')
                        ->required()
                        ->maxLength(50)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, Set $set, HasForms $livewire, TextInput $component) {
                            $set('legal_document', str($state)->upper()->remove(['-', '_', ' ', '/'])->toString());
                            $livewire->validateOnly($component->getStatePath());
                        }),

                ]
            )->columns(3)->columnSpanFull(),

            Section::make(__('Client tutors'))
                ->collapsible()
                ->collapsed(fn(Get $get) => !$get('clientTutors'))
                ->schema([
                    Repeater::make('clientTutors')
                        ->hiddenLabel()
                        ->addActionLabel(__('Add tutor'))
                        ->defaultItems(0)
                        ->schema([
                            TextInput::make('name')
                                ->maxLength(120)
                                ->required(),
                            TextInput::make('surname')
                                ->maxLength(120)
                                ->required(),
                            TextInput::make('legal_document')
                                ->required()
                        ])->columns(3)->columnSpanFull()
                ]),

            Select::make('country_id')
                ->required()
                ->searchable()
                ->preload()
                ->optionsLimit(200),

            Select::make('nationality_id')
                ->required()
                ->searchable()
                ->preload()
                ->optionsLimit(200),

            Select::make('marital_status_id')
                ->required()
                ->getOptionLabelFromRecordUsing(fn($record) => $record->name),

            Select::make('legal_situation_id')
                ->required()
                ->getOptionLabelFromRecordUsing(fn($record) => $record->name),

            Group::make()->schema(
                [
                    // this is an implicit field, it depends on large_family_type_id
                    Toggle::make('has_large_family')
                        ->inline(fn(Get $get) => !$get('has_large_family')) // auto format itself!
                        ->live()
                        ->afterStateUpdated(function ($state, Set $set) {
                            if ($state === false) {
                                $set('large_family_type_id', null);
                            }
                        }),

                    Select::make('large_family_type_id')
                        ->hidden(fn(Get $get) => !$get('has_large_family'))
                        ->required(fn(Get $get) => $get('has_large_family'))
                        ->getOptionLabelFromRecordUsing(fn($record) => $record->name),

                ]
            )->columns(3)->columnSpanFull(),

            Toggle::make('is_single_parent_family'),

            Toggle::make('is_orphan'),

            Section::make(__('Client relatives'))
                ->collapsible()
                ->collapsed(fn(Get $get) => !$get('clientRelatives'))
                ->schema([
                    Repeater::make('clientRelatives')
                        ->hiddenLabel()
                        ->addActionLabel(__('Add relative'))
                        ->defaultItems(0)
                        ->schema([
                            Group::make()->schema(
                                [
                                    Select::make('family_relation_id')
                                        ->getOptionLabelFromRecordUsing(fn($record) => $record->name)
                                        ->live()
                                        ->required(),

                                    TextInput::make('family_relation_other')
                                        ->label(__('Indicate family relation')),

                                    TextInput::make('legal_document')
                                        ->required()
                                        ->live(onBlur: true)
                                    ,
                                ]
                            )->columns(3)->columnSpanFull(),

                            TextInput::make('name')
                                ->maxLength(120)
                                ->required(),

                            TextInput::make('surname')
                                ->maxLength(120)
                                ->required(),

                            DatePicker::make('birth_date')
                                ->required()
                                ->native(false)
                                ->maxDate(today()),

                            Toggle::make('has_disability')
                                ->live(),

                            Select::make('disabilityTypes')
                                ->multiple()
                                ->placeholder(__('Select one or more options'))
                                ->required(fn(Get $get) => $get('has_disability'))
                                ->searchable()
                                ->preload()
                                ->getOptionLabelFromRecordUsing(fn($record) => $record->name)
                                ->hidden(fn(Get $get) => !$get('has_disability'))
                        ])->columns(3)->columnSpanFull()
                ]),

            Select::make('training_id')
                ->getOptionLabelFromRecordUsing(fn($record) => $record->name)
                ->live(),

            TextArea::make('extra_training_info')
                ->required(fn(Get $get) => User::find($get('training_id'))?->show_extra_info)
                // TODO: don't repeat query
                ->hidden(fn(Get $get) => !User::find($get('training_id'))?->show_extra_info),

            Section::make(__('Other training'))
                ->collapsible()
                ->collapsed(fn(Get $get) => !$get('otherTrainings'))
                ->schema([
                    Repeater::make('otherTrainings')
                        ->hiddenLabel()
                        ->addActionLabel(__('Add other training'))
                        ->defaultItems(0)
                        ->schema([
                            Textarea::make('not_homologated')
                                ->hintIcon('heroicon-m-question-mark-circle', tooltip: __('not homologated in Spain')),
                            Textarea::make('not_regulated')
                                ->hintIcon('heroicon-m-question-mark-circle', tooltip: __('Every certificate justified')),
                        ])
                ]),

            TextInput::make('security_social_number')
                ->maxLength(50),

            Toggle::make('is_employed')
                ->inline(false)
                ->live(),

            Textarea::make('job_description')
                ->columnSpanFull()
                ->required(fn(Get $get) => $get('is_employed'))
                ->hidden(fn(Get $get) => !$get('is_employed')),

            Textarea::make('observations')
                ->columnSpanFull(),
        ];
    }

    public static function getFormSchemaContactData(): array
    {
        return [
            Group::make()
                ->columns()->columnSpanFull()
                ->schema(
                    [
                        TextInput::make('phone_1')
                            ->required()
                            ->tel()
                            ->prefixIcon('heroicon-m-phone'),

                        TextInput::make('phone_2')
                            ->tel()
                            ->prefixIcon('heroicon-m-phone'),

                        TextInput::make('email')
                            ->required()
                            ->email()
                            ->prefixIcon('heroicon-m-envelope'),

                        Select::make('contact_preferences')
                            ->multiple()
                            ->placeholder(__('Select one or more options'))
                            ->required(),

                        Group::make()->columns(3)->columnSpanFull()->schema(
                            [
                                Select::make('autonomy_id')
                                    ->label(__('Region'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (Set $set) {
                                        $set('province_id', null);
                                        $set('municipality_id', null);
                                    }),

                                Select::make('province_id')
                                    ->label(__('Province'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn(Set $set) => $set('municipality_id', null)),

                                Select::make('municipality_id')
                                    ->label(__('Municipality'))
                                    ->searchable()
                                    ->preload()
                                    ->optionsLimit(100)
                                    ->required()
                                    ->createOptionForm([
                                        Hidden::make('province_id')
                                            ->formatStateUsing(fn(HasForms $livewire) => $livewire->data['contactDetails']['province_id']),

                                        TextInput::make('name')
                                            ->required(),
                                    ])
                                    ->createOptionAction(
                                        fn(Action $action, Get $get) => $get('province_id') ?
                                            $action
                                                ->modalHeading(__('New municipality'))
                                                ->modalWidth('xl') :
                                            $action
                                                ->disabled()
                                                ->tooltip(__('Select a province first'))
                                    ),
                            ]
                        ),

                        Group::make()->columns(3)->columnSpanFull()->schema(
                            [
                                TextInput::make('address')
                                    ->columnSpan(2)
                                    ->required(),

                                TextInput::make('postal_code')
                                    ->required(),
                            ]
                        ),

                        Textarea::make('observations')
                            ->columnSpanFull(),
                    ]
                )
        ];
    }

    public static function getFormSchemaDisabilityGeneralData(): array
    {
        return [
            Group::make()
                ->columns()->columnSpanFull()
                ->schema(
                    [
                        Select::make('has_disability')
                            ->label(__('Disability'))
                            ->options([
                                'yes' => __('Yes'),
                                'no' => __('No'),
                                'pending' => __('Evaluation pending'),
                            ])
                            ->live()
                            ->required()
                            ->columnSpan(1)
                            ->columns(),

                        Group::make()
                            ->columns()
                            ->columnSpanFull()
                            ->hidden(fn(Get $get) => $get('has_disability') !== 'yes')
                            ->schema([
                                Select::make('disabilityTypes')
                                    ->multiple()
                                    ->placeholder(__('Select one or more options'))
                                    ->live() // affects to the 4th tab
                                    ->required(fn(Get $get) => $get('has_disability') === 'yes')
                                    ->searchable()
                                    ->preload()
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name),

                                // this is an attribute field, linked to certificate_type_id
                                Toggle::make('has_disability_certificate')
                                    ->inline(false)
                                    ->label(__('Disability certificate'))
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state === false) {
                                            $set('certificate_autonomy_id', null);
                                            $set('certificate_province_id', null);
                                            $set('disability_percentage', null);
                                            $set('disability_certificate_type_id', null);
                                            $set('disability_certificate_revision_date', null);
                                        }
                                    }),

                                Group::make()
                                    ->hidden(fn(Get $get) => !$get('has_disability_certificate'))
                                    ->schema([
                                        Group::make()->schema([
                                            Select::make('certificate_autonomy_id')
                                                ->label('Autonomy where the certificate was issued')
                                                ->searchable()
                                                ->preload()
                                                ->required(fn(Get $get) => $get('has_disability_certificate'))
                                                ->live()
                                                ->afterStateUpdated(fn(Set $set) => $set('certificate_province_id', null)),

                                            Select::make('certificate_province_id')
                                                ->label('Province where the certificate was issued')
                                                ->searchable()
                                                ->preload()
                                                ->required(fn(Get $get) => $get('has_disability_certificate')),

                                            TextInput::make('disability_percentage')
                                                ->suffix('%')
                                                ->minValue(0)
                                                ->maxValue(100)
                                                ->numeric(),
                                        ])->columns(3)->columnSpanFull(),

                                        Group::make()->schema([
                                            Select::make('disability_certificate_type_id')
                                                ->label('Type of certificate')
                                                ->getOptionLabelFromRecordUsing(fn($record) => $record->name)
                                                ->live(),

                                            DatePicker::make('disability_certificate_revision_date')
                                                ->date('d-m-Y'),
                                        ])->columns()->columnSpanFull(),

                                        Textarea::make('expert_judgement')
                                            ->rows(5)
                                            ->hintIcon('heroicon-m-question-mark-circle', tooltip: __('Expert judgement info'))
                                            ->columnSpanFull(),


                                        Toggle::make('needs_help'),

                                        Group::make()->schema([
                                            Toggle::make('has_dependency')
                                                ->live()
                                                ->afterStateUpdated(function ($state, Set $set) {
                                                    if ($state === false) {
                                                        $set('dependency_degree_id', null);
                                                        $set('dependency_punctuation', null);
                                                        $set('facilitating_measures', null);
                                                        $set('has_facilitating_measures', null);
                                                    }
                                                }),

                                            Select::make('dependency_degree_id')
                                                ->label('Dependency degree')
                                                ->hidden(fn(Get $get) => !$get('has_dependency'))
                                                ->getOptionLabelFromRecordUsing(fn($record) => $record->name),

                                            TextInput::make('dependency_punctuation')
                                                ->hidden(fn(Get $get) => !$get('has_dependency'))
                                                ->numeric(),

                                            // this is an implicit field, linked to facilitating_measures
                                            Toggle::make('has_facilitating_measures')
                                                ->hidden(fn(Get $get) => !$get('has_dependency'))
                                                ->live()
                                                ->afterStateUpdated(function ($state, Set $set) {
                                                    if ($state === false) {
                                                        $set('facilitating_measures', null);
                                                    }
                                                }),

                                            TextArea::make('facilitating_measures')
                                                ->hidden(fn(Get $get) => !$get('has_facilitating_measures'))
                                                ->required(fn(Get $get) => $get('has_facilitating_measures'))
                                                ->columnSpanFull(),
                                        ])->columns(3)->columnSpanFull(),

                                        Select::make('work_incapacity_id')
                                            ->label('Work incapacity (IT)')
                                            ->getOptionLabelFromRecordUsing(fn($record) => $record->name),

                                        Select::make('help_measures')
                                            ->options([
                                                'yes' => __('Yes'),
                                                'no' => __('No'),
                                                'pending' => __('Pending'),
                                            ])
                                            ->live(),

                                        TextArea::make('help_measures_description')
                                            ->hidden(fn(Get $get) => $get('help_measures') !== 'yes')
                                            ->required(fn(Get $get) => $get('help_measures') === 'yes')
                                            ->columnSpanFull(),

                                    ])->columns()->columnSpanFull(),
                            ]),
                        Textarea::make('observations')
                            ->columnSpanFull(),
                    ],
                ),
        ];
    }

    public static function getFormSchemaHearingDisabilityData(): array
    {
        // make two groups, to hide or show with the required depending on the disability types of the 3rd tab
        return [
            // group 1: "Auditiva" is not selected
            Group::make()
                ->columns()->columnSpanFull()
                ->schema(
                    [
                        Fieldset::make()
                            ->label(__('Deafness type'))
                            ->schema([
                                Select::make('left_deafness_type_id')
                                    ->label('Left ear deafness')
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name),

                                Select::make('right_deafness_type_id')
                                    ->label('Right ear deafness')
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name),
                            ]),

                        TextInput::make('deafness_start_age')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),

                        Toggle::make('uses_hearing_aid')
                            ->inline(false)
                            ->live(),

                        Group::make()->schema([
                            Select::make('implant_position')
                                ->label(__('Implant'))
                                ->options([
                                    'no' => __('No'),
                                    'left' => __('Left'),
                                    'right' => __('Right'),
                                    'both' => __('Both'),
                                ]),

                            Select::make('hearing_aid_position')
                                ->label(__('Hearing aids'))
                                ->options([
                                    'no' => __('No'),
                                    'left' => __('Left'),
                                    'right' => __('Right'),
                                    'both' => __('Both'),
                                ]),

                            Toggle::make('magnetic_loop')
                                ->inline(false),

                            Toggle::make('fm_equipment')
                                ->inline(false),

                            Textarea::make('others')
                                ->columnSpanFull(),
                        ])->columns(4)->columnSpanFull()
                            ->visible(fn(Get $get) => $get('uses_hearing_aid')),

                        Section::make(__('Deaf-blind people'))->schema([
                            Toggle::make('has_usher_syndrome')
                                ->inline(false),

                            Select::make('loss_order')
                                ->label(__('Loss order'))
                                ->options([
                                    'visual-hearing' => __('Visual-hearing'),
                                    'hearing-visual' => __('Hearing-visual'),
                                ])
                        ])->columns(),

                        Select::make('communicationModalities')
                            ->label(__('Communication modality'))
                            ->multiple()
                            ->placeholder(__('Select one or more options'))
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->name),
                    ]
                ),

            // group 2: Auditiva is selected -> force fields to be required
            Group::make()
                ->columns()->columnSpanFull()
                ->schema(
                    [
                        Fieldset::make()
                            ->label(__('Deafness type'))
                            ->schema([
                                Select::make('left_deafness_type_id')
                                    ->required()
                                    ->label('Left ear deafness')
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name),

                                Select::make('right_deafness_type_id')
                                    ->required()
                                    ->label('Right ear deafness')
                                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name),
                            ]),

                        TextInput::make('deafness_start_age')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),

                        Toggle::make('uses_hearing_aid')
                            ->inline(false)
                            ->live(),

                        Group::make()->schema([
                            Select::make('implant_position')
                                ->label(__('Implant'))
                                ->required(fn(Get $get) => $get('uses_hearing_aid'))
                                ->options([
                                    'no' => __('No'),
                                    'left' => __('Left'),
                                    'right' => __('Right'),
                                    'both' => __('Both'),
                                ]),

                            Select::make('hearing_aid_position')
                                ->label(__('Hearing aids'))
                                ->required(fn(Get $get) => $get('uses_hearing_aid'))
                                ->options([
                                    'no' => __('No'),
                                    'left' => __('Left'),
                                    'right' => __('Right'),
                                    'both' => __('Both'),
                                ]),

                            Toggle::make('magnetic_loop')
                                ->inline(false),

                            Toggle::make('fm_equipment')
                                ->inline(false),

                            Textarea::make('others')
                                ->columnSpanFull(),
                        ])->columns(4)->columnSpanFull()
                            ->visible(fn(Get $get) => $get('uses_hearing_aid')),

                        Section::make(__('Deaf-blind people'))->schema([
                            Toggle::make('has_usher_syndrome')
                                ->inline(false),

                            Select::make('loss_order')
                                ->label(__('Loss order'))
                                ->required()
                                ->options([
                                    'visual-hearing' => __('Visual-hearing'),
                                    'hearing-visual' => __('Hearing-visual'),
                                ])
                        ])->columns(),

                        Select::make('communicationModalities')
                            ->label(__('Communication modality'))
                            ->required()
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->name),
                    ]
                )
        ];
    }

    public static function getFormSchemaEntityData(): array
    {
        return [
            Group::make()
                ->columns(3)->columnSpanFull()
                ->schema(
                    [
                        TextInput::make('file_number')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (HasForms $livewire, TextInput $component) {
                                $livewire->validateOnly($component->getStatePath());
                            }),

                        TextInput::make('reference_person'),

                        DatePicker::make('first_attendance_date')
                            ->date('d-m-Y')
                            ->maxDate(today()),

                        Group::make()
                            ->columns(3)->columnSpanFull()
                            ->schema([
                                // this field is an implicit one, and it's linked to cancellation_date
                                Toggle::make('has_cancelled')
                                    ->inline(false)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state === false) {
                                            $set('cancellation_date', null);
                                            $set('cancellation_reason', null);
                                        }
                                    }),

                                DatePicker::make('cancellation_date')
                                    ->date('d-m-Y')
                                    ->hidden(fn(Get $get) => !$get('has_cancelled'))
                                    ->required(fn(Get $get) => $get('has_cancelled')),

                                Textarea::make('cancellation_reason')
                                    ->hidden(fn(Get $get) => !$get('has_cancelled'))
                                    ->required(fn(Get $get) => $get('has_cancelled')),
                            ]),
                        Select::make('services')
                            ->multiple()
                            ->placeholder(__('Select one or more options'))
                            ->searchable()
                            ->preload()
                            ->getOptionLabelFromRecordUsing(fn($record) => $record->name)
                            ->createOptionForm(fn() => [
                                Hidden::make('federation_id')
                                    ->formatStateUsing(fn(Get $get) => auth()->user()->federation_id),

                                TextInput::make('name')
                                    ->required(),
                            ])
                            ->createOptionAction(
                                fn(Action $action) => $action
                                    ->modalHeading(__('New service'))
                                    ->modalWidth('xl'),
                            ),

                        Toggle::make('is_member')
                            ->inline(false)
                            ->live(),

                        TextInput::make('entity_name')
                            ->hidden(fn(Get $get) => !$get('is_member'))
                            ->required(fn(Get $get) => $get('is_member')),

                        Fieldset::make()
                            ->label(__('Person previously assisted by another entity of the CNSE associative movement'))
                            ->columns(1)
                            ->schema([
                                Repeater::make('attended_by_other_federation')
                                    ->addActionLabel(__('Add entity'))
                                    ->hiddenLabel()
                                    ->defaultItems(0)
                                    ->schema(
                                        [
                                            Select::make('federation_id')
                                                ->label(__('Federation'))
                                                ->searchable()
                                                ->preload()
                                                ->required(),

                                            Textarea::make('attended_demand')
                                                ->label(__('Demand'))
                                                ->required(),
                                        ]
                                    )->columns()->columnSpanFull(),
                            ]),

                        Fieldset::make()
                            ->label(__('Person previously assisted by another entity of deaf people'))
                            ->columns(1)
                            ->schema([
                                Repeater::make('attended_by_other_entity')
                                    ->addActionLabel(__('Add other entity'))
                                    ->hiddenLabel()
                                    ->defaultItems(0)
                                    ->schema(
                                        [
                                            TextInput::make('attended_entity_name')
                                                ->label(__('Indicate which'))
                                                ->required(),

                                            Textarea::make('attended_demand')
                                                ->label(__('Demand'))
                                                ->required(),

                                        ]
                                    )->columns()->columnSpanFull(),
                            ]),

                        Textarea::make('observations')
                            ->columnSpanFull()
                    ]
                )
        ];
    }
}
