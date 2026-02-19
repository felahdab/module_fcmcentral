<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions;

use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

use Filament\Forms;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;

use Modules\FcmCommun\Services\CalculHeuresParcoursService;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\Cohorte;
use Modules\FcmCentral\Models\Parcours;

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
                            ->options(Parcours::whereHas('parcoursSerialise')->pluck('libelle_court', 'uuid'))
                            ->searchable(),
                    ])
                    ->action(function ($record, array $data) {
                        if (!empty($data['parcours_id'])) {
                            event(new UserGeneratedEvent(
                                UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
                                auth()->user()->uuid,
                                ObjectClasses::MARIN,
                                $record->uuid,
                                ["parcours" => $data["parcours_id"]]
                            ));
                        }
                    });
    }

}