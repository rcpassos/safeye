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
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
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
                Toggle::make('active')
                    ->default(true),
                Section::make(__('checks.basic_information'))
                    ->columns([
                        'sm' => 1,
                        'xl' => 2,
                    ])
                    ->schema([
                        TextInput::make('name')
                            ->label(__('checks.name'))
                            ->required()
                            ->maxLength(255),
                        Select::make('group_id')
                            ->relationship(name: 'group', titleAttribute: 'name')
                            ->options(Auth::user()->groups->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required(),
                                Hidden::make('user_id')->default(Auth::id()),
                            ]),
                    ]),

                Section::make(__('checks.request_details'))
                    ->columns([
                        'sm' => 1,
                        'xl' => 3,
                    ])
                    ->schema([
                        Select::make('type')
                            ->label(__('checks.type'))
                            ->columns([
                                'xl' => 1,
                            ])
                            ->options(CheckType::class)
                            ->required()
                            ->default(CheckType::HTTP),
                        TextInput::make('endpoint')
                            ->label(__('checks.endpoint'))
                            ->columnSpan([
                                'xl' => 3,
                            ])
                            ->url()
                            ->required()
                            ->maxLength(255),
                        Select::make('http_method')
                            ->label(__('checks.http_method'))
                            ->options(HTTPMethod::class)
                            ->required()
                            ->default(HTTPMethod::GET),
                        TextInput::make('interval')
                            ->label(__('checks.interval'))
                            ->suffix(__('checks.seconds'))
                            ->required()
                            ->numeric()
                            ->default(60),
                        TextInput::make('request_timeout')
                            ->label(__('checks.request_timeout'))
                            ->suffix(__('checks.seconds'))
                            ->required()
                            ->numeric()
                            ->default(10),
                        KeyValue::make('request_headers')
                            ->label(__('checks.request_headers'))
                            ->columnSpanFull(),
                        JsonInput::make('request_body')
                            ->label(__('checks.request_body'))
                            ->key('request_body')
                            ->columnSpanFull()
                            ->lineNumbers(true)
                            ->lineWrapping(true)
                            ->autoCloseBrackets(true)
                            ->darkTheme(true)
                            ->foldingCode(false)
                            ->foldedCode(false),
                    ]),

                Section::make(__('checks.assertions'))
                    ->schema([
                        Repeater::make('assertions')
                            ->relationship()
                            ->hiddenLabel()
                            ->schema([
                                Select::make('type')
                                    ->hiddenLabel()
                                    ->options(AssertionType::class)
                                    ->required()
                                    ->default(AssertionType::RESPONSE_CODE),
                                Select::make('sign')
                                    ->hiddenLabel()
                                    ->options(AssertionSign::class)
                                    ->required()
                                    ->default(AssertionSign::EQUAL),
                                TextInput::make('value')
                                    ->hiddenLabel()
                                    ->placeholder(__('checks.placeholder_assertion_value'))
                                    ->default('200')
                                    ->required(),
                            ])
                            ->reorderable(false)
                            ->defaultItems(1)
                            ->minItems(1)
                            ->addActionLabel(__('checks.add_assertion'))
                            ->columns(3),
                    ]),

                Section::make(__('checks.alert_settings'))
                    ->schema([
                        Textarea::make('notify_emails')
                            ->label(__('checks.notify_emails'))
                            ->columnSpanFull()
                            ->rows(4)
                            ->helperText(__('checks.helper_notify_emails')),
                        TextInput::make('slack_webhook_url')
                            ->label(__('checks.slack_webhook_url'))
                            ->columnSpanFull()
                            ->url()
                            ->helperText(__('checks.helper_slack_webhook_url')),
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
