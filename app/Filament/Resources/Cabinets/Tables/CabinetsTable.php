<?php

namespace App\Filament\Resources\Cabinets\Tables;

use App\Enums\CabinetStatus;
use App\Models\Cabinet;
use App\Services\CabinetFulfillmentService;
use App\Support\Wilayas;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CabinetsTable
{
    /** @var array<string, string> */
    private const STATUS_LABELS = [
        'pending' => 'En attente',
        'active' => 'Actif',
        'suspended' => 'Suspendu',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Cabinet')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('owner.email')
                    ->label('Propriétaire')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('specialization')
                    ->label('Spécialité')
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('wilaya_code')
                    ->label('Wilaya')
                    ->formatStateUsing(fn (?int $state): string => Wilayas::label($state) ?? '—'),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => self::STATUS_LABELS[self::statusValue($state)] ?? self::statusValue($state))
                    ->color(fn ($state): string => match (self::statusValue($state)) {
                        'active' => 'success',
                        'pending' => 'warning',
                        'suspended' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('activated_at')
                    ->label('Activé le')
                    ->dateTime()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(self::STATUS_LABELS),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('activate')
                        ->label('Activer')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('success')
                        ->visible(fn (Cabinet $record): bool => $record->status === CabinetStatus::PENDING)
                        ->requiresConfirmation()
                        ->modalDescription('Une licence perpétuelle sera émise et le propriétaire sera notifié.')
                        ->action(function (Cabinet $record): void {
                            app(CabinetFulfillmentService::class)->activate($record);

                            Notification::make()
                                ->title('Cabinet activé')
                                ->success()
                                ->send();
                        }),
                    Action::make('suspend')
                        ->label('Suspendre')
                        ->icon(Heroicon::OutlinedPauseCircle)
                        ->color('warning')
                        ->visible(fn (Cabinet $record): bool => $record->status === CabinetStatus::ACTIVE)
                        ->requiresConfirmation()
                        ->action(function (Cabinet $record): void {
                            app(CabinetFulfillmentService::class)->suspend($record);

                            Notification::make()
                                ->title('Cabinet suspendu')
                                ->warning()
                                ->send();
                        }),
                    Action::make('reactivate')
                        ->label('Réactiver')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('success')
                        ->visible(fn (Cabinet $record): bool => $record->status === CabinetStatus::SUSPENDED)
                        ->requiresConfirmation()
                        ->action(function (Cabinet $record): void {
                            app(CabinetFulfillmentService::class)->reactivate($record);

                            Notification::make()
                                ->title('Cabinet réactivé')
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }

    private static function statusValue(mixed $state): string
    {
        return $state instanceof CabinetStatus ? $state->value : (string) $state;
    }
}
