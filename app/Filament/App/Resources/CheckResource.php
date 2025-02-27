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
                Section::make('Basic Information')
                    ->columns([
                        'sm' => 1,
                        'xl' => 2,
                    ])
                    ->schema([
                        TextInput::make('name')
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

                Section::make('Request Details')
                    ->columns([
                        'sm' => 1,
                        'xl' => 3,
                    ])
                    ->schema([
                        Select::make('type')
                            ->columns([
                                'xl' => 1,
                            ])
                            ->options(CheckType::class)
                            ->required()
                            ->default(CheckType::HTTP),
                        TextInput::make('endpoint')
                            ->columnSpan([
                                'xl' => 3,
                            ])
                            ->url()
                            ->required()
                            ->maxLength(255),
                        Select::make('http_method')
                            ->label('HTTP Method')
                            ->options(HTTPMethod::class)
                            ->required()
                            ->default(HTTPMethod::GET),
                        TextInput::make('interval')
                            ->suffix('seconds')
                            ->required()
                            ->numeric()
                            ->default(60),
                        TextInput::make('request_timeout')
                            ->suffix('seconds')
                            ->required()
                            ->numeric()
                            ->default(10),
                        KeyValue::make('request_headers')
                            ->columnSpanFull(),
                        JsonInput::make('request_body')
                            ->key('request_body')
                            ->columnSpanFull()
                            ->lineNumbers(true)
                            ->lineWrapping(true)
                            ->autoCloseBrackets(true)
                            ->darkTheme(true)
                            ->foldingCode(false)
                            ->foldedCode(false),
                    ]),

                Section::make('Assertions')
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
                                    ->placeholder('200')
                                    ->default('200')
                                    ->required(),
                            ])
                            ->reorderable(false)
                            ->defaultItems(1)
                            ->minItems(1)
                            ->addActionLabel('Add Assertion')
                            ->columns(3),
                    ]),

                Section::make('Alert Settings')
                    ->schema([
                        Textarea::make('notify_emails')
                            ->columnSpanFull()
                            ->rows(4)
                            ->helperText('Place one email address per line'),
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
