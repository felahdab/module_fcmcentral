<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\ParcoursSerialise;

class AttribuerParcoursAction extends Action
{
    public static function make(?string $name = null): static
    {

        
        return parent::make($name)
            ->modalHeading('Gestion du parcours')
            ->modalDescription(fn ($record) => $record->fullNameAndGrade)
            ->schema([
                Select::make('parcours_id')
                    ->label('Choisir un parcours')
                    ->options(function () {
                        return ParcoursSerialise::with('parcoursModel') // ✅
                            ->whereNull('date_fin')
                            ->orderBy('version', 'desc')
                            ->get()
                            ->groupBy('parcours_id')
                            ->map(fn ($group) => $group->first())
                            ->values()
                            ->mapWithKeys(fn ($ps) => [
                                $ps->parcoursModel?->uuid => ($ps->libelle_court ?? $ps->parcoursModel?->libelle_court ?? '?') . ' (v' . $ps->version . ')'
                            ])
                            ->toArray();
                    })
                    
                    ->searchable(),
            ])
            ->action(function ($record, array $data) {
                if (!empty($data['parcours_id'])) {
                    event(new UserGeneratedEvent(
                        UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $record->uuid,
                        ["parcours" => $data["parcours_id"]] // ✅ UUID du Parcours
                    ));
                }
            });
    }
}
