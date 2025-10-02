<?php

declare(strict_types=1);

namespace App\Filament\App\Resources\CheckResource\Pages;

use App\Actions\RunSingleCheck;
use App\Enums\CheckHistoryType;
use App\Filament\App\Resources\CheckResource;
use App\Filament\App\Resources\CheckResource\Widgets\CheckStats;
use App\Models\Check;
use CodebarAg\FilamentJsonField\Infolists\Components\JsonEntry;
use Exception;
use Filament\Actions;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\HtmlString;

final class ViewCheck extends ViewRecord
{
    protected static string $resource = CheckResource::class;

    public function getHeading(): string
    {
        /** @var Check $check */
        $check = $this->getRecord();

        return $check->name;
    }

    public function getSubheading(): string
    {
        /** @var Check $check */
        $check = $this->getRecord();

        $httpMethod = $check->http_method->value;
        $endpoint = $check->endpoint;

        return "$httpMethod $endpoint";
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema(
            [
                Split::make([
                    Grid::make()->schema([
                        Section::make([
                            RepeatableEntry::make('latestChecks')
                                ->hiddenLabel()
                                ->contained(true)
                                ->placeholder(__('checks.no_checks_found'))
                                ->schema([
                                    TextEntry::make('type')
                                        ->label(__('checks.status'))
                                        ->badge()
                                        ->color(fn (CheckHistoryType $state): string => match ($state) {
                                            CheckHistoryType::SUCCESS => 'success',
                                            CheckHistoryType::ERROR => 'danger',
                                        }),
                                    TextEntry::make('created_at')
                                        ->label(__('common.created_at'))
                                        ->dateTime()
                                        ->since(),
                                    JsonEntry::make('metadata')
                                        ->key('metadata')
                                        ->lineNumbers(false)
                                        ->lineWrapping(true)
                                        ->darkTheme(true)
                                        ->foldingCode(true)
                                        ->foldedCode(true),
                                ])
                                ->columns(3),
                        ])->heading(__('checks.latest_activity')),
                        Section::make([
                            RepeatableEntry::make('latestIssues')
                                ->hiddenLabel()
                                ->contained(true)
                                ->placeholder(__('checks.no_issues_found'))
                                ->schema([
                                    TextEntry::make('created_at')
                                        ->dateTime()
                                        ->since(),
                                    JsonEntry::make('root_cause')
                                        ->key('root_cause')
                                        ->lineNumbers(false)
                                        ->lineWrapping(true)
                                        ->autoCloseBrackets(true)
                                        ->darkTheme(true)
                                        ->foldingCode(true)
                                        ->foldedCode(true),
                                    JsonEntry::make('metadata')
                                        ->key('metadata')
                                        ->lineNumbers(false)
                                        ->lineWrapping(true)
                                        ->darkTheme(true)
                                        ->foldingCode(true)
                                        ->foldedCode(true),
                                ])
                                ->columns(3),
                        ])->heading('Latest Issues')
                            ->description('Issues that have been reported in the last 24 hours'),
                    ]),
                    Section::make([
                        TextEntry::make('endpoint')
                            ->label('Request')
                            ->formatStateUsing(fn (): string => $this->getSubheading()),
                        Grid::make([])->schema([
                            TextEntry::make('interval')
                                ->formatStateUsing(fn (string $state): string => __('checks.every_seconds', ['seconds' => $state])),
                            TextEntry::make('type'),
                        ])->columns(2),
                        TextEntry::make('request_timeout')
                            ->formatStateUsing(fn (string $state): string => __('checks.value_seconds', ['value' => $state])),
                        KeyValueEntry::make('request_headers'),
                        JsonEntry::make('request_body')
                            ->key('request_body')
                            ->columnSpanFull()
                            ->lineNumbers(true)
                            ->lineWrapping(true)
                            ->autoCloseBrackets(true)
                            ->darkTheme(true)
                            ->foldingCode(false)
                            ->foldedCode(false),
                        RepeatableEntry::make('assertions')
                            ->schema([
                                TextEntry::make('type'),
                                TextEntry::make('sign'),
                                TextEntry::make('value'),
                            ])
                            ->columns(3),
                        TextEntry::make('notify_emails')
                            ->formatStateUsing(fn (string $state): HtmlString => new HtmlString(implode('<br>', preg_split("/\r\n|\r|\n/", $state)))),
                        TextEntry::make('created_at')
                            ->label(__('common.created_at'))
                            ->dateTime()
                            ->since(),
                    ])
                        ->heading(__('checks.details'))
                        ->grow(false),
                ])->columnSpan('full')->from('md'),
            ]
        );
    }

    protected function getHeaderWidgets(): array
    {
        return [
            CheckStats::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('run_check_now')
                ->label(__('checks.run_check_now'))
                ->icon('heroicon-o-play')
                ->color('success')
                ->action(function (): void {
                    try {
                        /** @var Check $check */
                        $check = $this->getRecord();

                        app(RunSingleCheck::class)->handle($check);

                        Notification::make()
                            ->title(__('checks.check_executed'))
                            ->success()
                            ->send();

                        // Refresh the page to show the new check results
                        $this->redirect($this->getResource()::getUrl('view', ['record' => $check]));
                    } catch (Exception $e) {
                        Notification::make()
                            ->title(__('checks.check_execution_failed', ['error' => $e->getMessage()]))
                            ->danger()
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalHeading(__('checks.run_check_now'))
                ->modalDescription('Are you sure you want to run this check now? This will execute the check immediately and may send notifications if issues are found.')
                ->modalSubmitActionLabel(__('checks.run_check_now')),
            Actions\EditAction::make(),
        ];
    }
}
