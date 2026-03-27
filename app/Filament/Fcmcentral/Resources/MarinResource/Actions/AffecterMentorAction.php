<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Actions;

use Filament\Actions\Action;
use Filament\Forms;
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
            ->modalHeading('Gestion du mentor')
            ->modalDescription(fn ($record) => $record->fullNameAndGrade)
            ->schema([
                Select::make('mentor_id')
                    ->label('Choisir un mentor')
                    ->options(
                        Marin::query()
                            ->whereNull('deleted_at')
                            ->orderBy('nom')
                            ->get()
                            ->mapWithKeys(fn ($m) => [
                                $m->uuid => ($m->grade?->libelle_court ?? '').' '.$m->prenom.' '.$m->nom,
                            ])
                    )
                    ->searchable(),
                // Forms\Components\Toggle::make('retirer_mentor')
                //     ->label('Retirer le mentor')
                //     ->visible(fn ($record) => $record->getMentor() !== null),
            ])
            ->action(function ($record, array $data) {
                // if (!empty($data['retirer_mentor'])) {
                //     $record->complementsFcm->update(['mentor_id' => null]);
                // } elseif (!empty($data['mentor_id'])) {
                if (!empty($data['mentor_id'])) {
                    event(new UserGeneratedEvent(
                        UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $record->uuid,
                        ['mentor' => $data['mentor_id']]
                    ));
                }
            })
        ;
    }
}
