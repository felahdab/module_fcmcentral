<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\UserResource\Widgets;

use Illuminate\Database\Eloquent\Model;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\UserParcours;

class SelectionDeParcours extends BaseWidget
{
    protected int | string | array $columnSpan = 2;

    public ?Model $record = null;

    public function table(Table $table): Table
    {
        $liste_des_parcours_deja_attribues = UserParcours::where('user_id', $this->record->uuid)
                                                ->get()
                                                ->pluck('parcours_id');

        //ddd($liste_des_parcours_deja_attribues);

        return $table
            ->query(
                ParcoursSerialise::query()
                    ->whereNotIn('id', $liste_des_parcours_deja_attribues)
            )
            ->columns([
                TextColumn::make('version'),
                TextColumn::make('libelle_court'),
            ])
            ->actions([
                Action::make("essai")
                ->requiresConfirmation()
                ->action(function()
                {
                    return;
                })
            ]);
    }
}
