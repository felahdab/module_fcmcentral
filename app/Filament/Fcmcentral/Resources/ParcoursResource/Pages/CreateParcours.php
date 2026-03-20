<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Illuminate\Support\Str;
use Modules\FcmCentral\Models\Affectation;
use Modules\FcmCentral\Models\ParcoursType;

class CreateParcours extends CreateRecord
{
    use HasWizard;

    protected static string $resource = ParcoursResource::class;

    protected function getSteps(): array
    {
        return [

            // ── ÉTAPE 1 : Infos ──────────────────────────────────────────
            Step::make('Parcours')
                ->icon('heroicon-o-rectangle-stack')
                ->schema([
                    TextInput::make('code')
                        ->label('Code')
                        ->required()
                        ->maxLength(150),

                    TextInput::make('libelle_court')
                        ->label('Libellé court')
                        ->required()
                        ->maxLength(150),

                    TextInput::make('libelle_long')
                        ->label('Libellé long')
                        ->required()
                        ->maxLength(1500),

                    Select::make('parcours_type_id')
                        ->label('Type de parcours')
                        ->options(ParcoursType::orderBy('libelle_court')->pluck('libelle_court', 'id'))
                        ->required()
                        ->searchable(),
                ]),

            // ── ÉTAPE 2 : Affectations ────────────────────────────────────
            Step::make('Affectations')
                ->icon('heroicon-o-user-group')
                ->schema([
                    Repeater::make('affectations')
                        ->label('Affectations')
                        ->addActionLabel('+ Ajouter une affectation')
                        ->defaultItems(0)
                        ->schema([
                            Select::make('affectation_id')
                                ->label('Affectation existante')
                                ->options(Affectation::orderBy('libelle_court')->pluck('libelle_court', 'id'))
                                ->searchable()
                                ->live()
                                ->placeholder('Sélectionner ou créer ci-dessous'),

                            TextInput::make('nouvelle_code')
                                ->label('Ou créer — Code')
                                ->hidden(fn ($get) => $get('affectation_id') !== null),

                            TextInput::make('nouvelle_libelle_court')
                                ->label('Libellé court')
                                ->hidden(fn ($get) => $get('affectation_id') !== null),

                            TextInput::make('nouvelle_libelle_long')
                                ->label('Libellé long')
                                ->hidden(fn ($get) => $get('affectation_id') !== null),
                        ]),
                ]),
        ];
    }

    protected function afterCreate(): void
    {
        $parcours = $this->record;
        $data     = $this->data;

        $affectationIds = [];

        foreach ($data['affectations'] ?? [] as $aff) {
            if (!empty($aff['affectation_id'])) {
                $affectationIds[] = $aff['affectation_id'];
            } else {
                if (empty($aff['nouvelle_code']) && empty($aff['nouvelle_libelle_court'])) continue;
                $affectationIds[] = Affectation::create([
                    'uuid'          => Str::uuid(),
                    'code'          => $aff['nouvelle_code'] ?? '',
                    'libelle_court' => $aff['nouvelle_libelle_court'] ?? '',
                    'libelle_long'  => $aff['nouvelle_libelle_long'] ?? null,
                ])->id;
            }
        }

        $parcours->affectations()->sync($affectationIds);
    }
}
