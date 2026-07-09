<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

class AffecterMentorAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name)
            ->modalHeading('Gestion des mentors')
            ->modalDescription(fn ($record) => $record->fullNameAndGrade)
            ->schema([
                Select::make('mentors')
                    ->label('Choisir les mentors')
                    ->multiple()
                    ->options(
                        Marin::query()
                            ->whereNull('deleted_at')
                            ->orderBy('nom')
                            ->get()
                            ->mapWithKeys(fn ($m) => [
                                $m->uuid => ($m->grade?->libelle_court ?? '').' '.$m->prenom.' '.$m->nom,
                            ])
                    )
                    ->searchable()
                    ->default(fn ($record) => $record->complementsFcm?->mentors->pluck('uuid')->toArray() ?? []),
            ])
            ->action(function ($record, array $data) {
                if (isset($data['mentors'])) {
                    event(new UserGeneratedEvent(
                        UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN,
                        auth()->user()?->uuid,
                        ObjectClasses::MARIN,
                        $record->uuid,
                        ['mentor' => $data['mentors']]
                    ));
                }
            });
    }
}
