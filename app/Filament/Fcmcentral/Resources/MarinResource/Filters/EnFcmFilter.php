<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Filters;

use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Select;

use Filament\Tables\Filters\Filter;

class EnFcmFilter extends Filter
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
        ->schema([
            Select::make('value')
                ->label('Statut FCM')
                ->options([
                    'enFcm' => 'Marins en FCM uniquement',
                    'horsFcm' => 'Marins hors FCM',
                ])
               ->placeholder('Tous les Marins')
               ->default('enFcm')
               ->native(false),
        ])
        ->query(function (Builder $query, array $data): Builder {
            if (empty($data['value'])){
                return $query;
            }

            return match($data['value'] ?? 'enFcm') {
                'enFcm' => $query->enFcm(),
                'horsFcm' => $query->pasEnFcm(),
                default => $query,
            };
        })
        ->indicateUsing(function (array $data): ?string {
            if (empty($data['value'])){
                return null;
            }

            return match($data['value'] ?? 'enFcm') {
                'enFcm' => '✓ Marins en FCM uniquement',
                'horsFcm' => '✓ Marins hors FCM',
                default => null,
            };
        });
    }

}