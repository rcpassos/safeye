<?php

declare(strict_types=1);

namespace App\Filament\App\Resources;

use App\Enums\AssertionSign;
use App\Enums\AssertionType;
use App\Enums\CheckType;
use App\Enums\HTTPMethod;
use App\Filament\App\Resources\CheckResource\Pages;
use App\Models\Check;
use CodebarAg\FilamentJsonField\Forms\Components\JsonInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Illuminate\Support\Facades\Auth;

final class CheckResource extends Resource
{
    protected static ?string $model = Check::class;

    protected static ?string $navigationIcon = 'heroicon-o-fire';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Check Configuration')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make(__('checks.basic_information'))
                            ->icon('heroicon-o-information-circle')
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label(__('checks.name'))
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(1),
                                        Select::make('group_id')
                                            ->label(__('checks.group'))
                                            ->relationship(name: 'group', titleAttribute: 'name')
                                            ->options(Auth::user()->groups->pluck('name', 'id'))
                                            ->searchable()
                                            ->preload()
                                            ->columnSpan(1)
                                            ->createOptionForm([
                                                TextInput::make('name')
                                                    ->required(),
                                                Hidden::make('user_id')->default(Auth::id()),
                                            ]),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        Select::make('type')
                                            ->label(__('checks.type'))
                                            ->options(CheckType::class)
                                            ->required()
                                            ->default(CheckType::HTTP->value)
                                            ->live()
                                            ->afterStateUpdated(function ($state, $set): void {
                                                // Set appropriate defaults when switching types
                                                if ($state === CheckType::HTTP->value) {
                                                    $set('config.method', HTTPMethod::GET->value);
                                                    $set('config.timeout', 10);
                                                } elseif ($state === CheckType::PING->value) {
                                                    $set('config.count', 4);
                                                    $set('config.timeout', 10);
                                                }
                                            })
                                            ->columnSpan(1),
                                        TextInput::make('interval')
                                            ->label(__('checks.interval'))
                                            ->suffix(__('checks.seconds'))
                                            ->required()
                                            ->numeric()
                                            ->default(60)
                                            ->minValue(10)
                                            ->helperText(__('checks.helper_interval'))
                                            ->columnSpan(1),
                                    ]),

                                TextInput::make('endpoint')
                                    ->label(__('checks.endpoint'))
                                    ->required()
                                    ->maxLength(255)
                                    ->url(fn (Get $get): bool => $get('type') === CheckType::HTTP->value)
                                    ->helperText(fn (Get $get): string => $get('type') === CheckType::PING->value
                                        ? __('checks.helper_endpoint_ping')
                                        : __('checks.helper_endpoint_http')),

                                Toggle::make('active')
                                    ->label(__('checks.active'))
                                    ->default(true)
                                    ->helperText(__('checks.helper_active')),
                            ]),

                        Tabs\Tab::make(__('checks.check_configuration'))
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make(__('checks.http_settings'))
                                    ->description(__('checks.description_http_settings'))
                                    ->icon('heroicon-o-globe-alt')
                                    ->collapsible()
                                    ->visible(fn (Get $get): bool => $get('type') === CheckType::HTTP->value)
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                Select::make('config.method')
                                                    ->label(__('checks.http_method'))
                                                    ->options(HTTPMethod::class)
                                                    ->required(fn (Get $get): bool => $get('type') === CheckType::HTTP->value)
                                                    ->default(HTTPMethod::GET->value)
                                                    ->columnSpan(1),
                                                TextInput::make('config.timeout')
                                                    ->label(__('checks.request_timeout'))
                                                    ->suffix(__('checks.seconds'))
                                                    ->required(fn (Get $get): bool => $get('type') === CheckType::HTTP->value)
                                                    ->numeric()
                                                    ->default(10)
                                                    ->minValue(1)
                                                    ->maxValue(60)
                                                    ->columnSpan(1),
                                            ]),
                                        KeyValue::make('config.headers')
                                            ->label(__('checks.request_headers'))
                                            ->keyLabel(__('checks.header_name'))
                                            ->valueLabel(__('checks.header_value'))
                                            ->helperText(__('checks.helper_request_headers')),
                                        JsonInput::make('config.body')
                                            ->label(__('checks.request_body'))
                                            ->key('request_body')
                                            ->lineNumbers(true)
                                            ->lineWrapping(true)
                                            ->autoCloseBrackets(true)
                                            ->darkTheme(true)
                                            ->foldingCode(false)
                                            ->foldedCode(false)
                                            ->helperText(__('checks.helper_request_body')),
                                    ]),

                                Section::make(__('checks.ping_settings'))
                                    ->description(__('checks.description_ping_settings'))
                                    ->icon('heroicon-o-signal')
                                    ->collapsible()
                                    ->visible(fn (Get $get): bool => $get('type') === CheckType::PING->value)
                                    ->schema([
                                        Grid::make(2)
                                            ->schema([
                                                TextInput::make('config.count')
                                                    ->label(__('checks.ping_count'))
                                                    ->numeric()
                                                    ->default(4)
                                                    ->minValue(1)
                                                    ->maxValue(10)
                                                    ->required(fn (Get $get): bool => $get('type') === CheckType::PING->value)
                                                    ->helperText(__('checks.helper_ping_count'))
                                                    ->columnSpan(1),
                                                TextInput::make('config.timeout')
                                                    ->label(__('checks.ping_timeout'))
                                                    ->numeric()
                                                    ->default(10)
                                                    ->minValue(1)
                                                    ->maxValue(60)
                                                    ->suffix(__('checks.seconds'))
                                                    ->required(fn (Get $get): bool => $get('type') === CheckType::PING->value)
                                                    ->helperText(__('checks.helper_ping_timeout'))
                                                    ->columnSpan(1),
                                            ]),
                                    ]),
                            ]),

                        Tabs\Tab::make(__('checks.assertions'))
                            ->icon('heroicon-o-beaker')
                            ->schema([
                                Section::make()
                                    ->description(fn (Get $get): string => $get('type') === CheckType::PING->value
                                        ? __('checks.description_assertions_ping')
                                        : __('checks.description_assertions'))
                                    ->schema([
                                        Repeater::make('assertions')
                                            ->relationship()
                                            ->hiddenLabel()
                                            ->schema([
                                                Select::make('type')
                                                    ->hiddenLabel()
                                                    ->placeholder(__('checks.placeholder_assertion_type'))
                                                    ->options(AssertionType::class)
                                                    ->required(),
                                                Select::make('sign')
                                                    ->hiddenLabel()
                                                    ->placeholder(__('checks.placeholder_assertion_sign'))
                                                    ->options(AssertionSign::class)
                                                    ->required(),
                                                TextInput::make('value')
                                                    ->hiddenLabel()
                                                    ->placeholder(__('checks.placeholder_assertion_value'))
                                                    ->required(),
                                            ])
                                            ->reorderable(false)
                                            ->defaultItems(fn (Get $get): int => $get('type') === CheckType::HTTP->value ? 1 : 0)
                                            ->minItems(fn (Get $get): int => $get('type') === CheckType::HTTP->value ? 1 : 0)
                                            ->addActionLabel(__('checks.add_assertion'))
                                            ->columns(3),
                                    ]),
                            ]),

                        Tabs\Tab::make(__('checks.alert_settings'))
                            ->icon('heroicon-o-bell-alert')
                            ->schema([
                                Section::make()
                                    ->description(__('checks.description_alert_settings'))
                                    ->schema([
                                        Textarea::make('notify_emails')
                                            ->label(__('checks.notify_emails'))
                                            ->rows(4)
                                            ->helperText(__('checks.helper_notify_emails')),
                                        TextInput::make('slack_webhook_url')
                                            ->label(__('checks.slack_webhook_url'))
                                            ->url()
                                            ->helperText(__('checks.helper_slack_webhook_url')),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChecks::route('/'),
            'create' => Pages\CreateCheck::route('/create'),
            'view' => Pages\ViewCheck::route('/{record}'),
            'edit' => Pages\EditCheck::route('/{record}/edit'),
        ];
    }
}
