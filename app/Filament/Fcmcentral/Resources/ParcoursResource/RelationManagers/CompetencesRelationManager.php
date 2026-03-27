<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Modules\FcmCentral\Models\Competence;

class CompetencesRelationManager extends RelationManager
{
    protected static string $relationship = 'competences';
    protected static ?string $title = 'Compétences';

    public function table(Table $table): Table
    {
        $parcours = $this->ownerRecord;

        $competenceIds = Competence::where('parcours_type_id', $parcours->parcours_type_id)
            ->whereIn('affectation_id', $parcours->affectations->pluck('id')->toArray())
            ->pluck('id')
            ->toArray()
        ;

        return $table
            ->query(
                Competence::where('parcours_type_id', $parcours->parcours_type_id)
                    ->whereIn('affectation_id', $parcours->affectations->pluck('id')->toArray())
                    ->with('affectation')
            )
            ->reorderable('ordre')
            ->columns([
                TextColumn::make('ordre')
                    ->label('Ordre')
                    ->sortable(),
                TextColumn::make('affectation.libelle_court')
                    ->label('Affectation')
                    ->badge()
                    ->color('info'),
                TextColumn::make('code')->searchable()->sortable(),
                TextColumn::make('libelle_court')->searchable()->label('Libellé'),
                // TextColumn::make('competenceSavoirfaires_count')
                //     ->counts('competenceSavoirfaires')
                //     ->label('Savoir-faires')
                //     ->badge()
                //     ->color('success'),
            ])
            ->headerActions([
                // ✅ Ajouter une compétence existante au parcours
                Action::make('ajouter_competence')
                    ->label('+ Ajouter une compétence')
                    ->form([
                        Select::make('competence_id')
                            ->label('Compétence')
                            ->options(
                                Competence::where('parcours_type_id', $parcours->parcours_type_id)
                                    ->whereIn('affectation_id', $parcours->affectations->pluck('id')->toArray())
                                    ->with('affectation')
                                    ->get()
                                    ->mapWithKeys(fn ($c) => [
                                        $c->id => ($c->affectation?->libelle_court ?? '?').' — '.$c->libelle_court,
                                    ])
                                    ->toArray()
                            )
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (array $data) use ($parcours): void {
                        // ✅ Rien à sauvegarder — la compétence est déjà liée via affectation_id + parcours_type_id
                        // On stocke en session pour SavoirfairesRelationManager
                        $existing = session('parcours_competences_'.$parcours->id, []);
                        $existing[] = $data['competence_id'];
                        session(['parcours_competences_'.$parcours->id => array_unique($existing)]);

                        Notification::make()
                            ->title('Compétence ajoutée')
                            ->success()
                            ->send()
                        ;
                    }),

                // ✅ Créer une nouvelle compétence
                Action::make('creer_competence')
                    ->label('+ Créer une compétence')
                    ->form([
                        Select::make('affectation_id')
                            ->label('Affectation')
                            ->options($parcours->affectations->pluck('libelle_court', 'id')->toArray())
                            ->required()
                            ->searchable(),

                        TextInput::make('code')->required()->maxLength(150),
                        TextInput::make('libelle_court')->required()->maxLength(150)->label('Libellé court'),
                        TextInput::make('libelle_long')->maxLength(1500)->label('Libellé long'),
                    ])
                    ->action(function (array $data) use ($parcours): void {
                        // ✅ Crée la compétence liée à l'affectation et au type parcours
                        Competence::create([
                            'uuid' => Str::uuid(),
                            'code' => $data['code'],
                            'libelle_court' => $data['libelle_court'],
                            'libelle_long' => $data['libelle_long'] ?? null,
                            'affectation_id' => $data['affectation_id'],
                            'parcours_type_id' => $parcours->parcours_type_id,
                        ]);

                        Notification::make()
                            ->title('Compétence créée')
                            ->success()
                            ->send()
                        ;
                    }),
            ])

            ->recordActions([
                Action::make('edit')
                    ->label('Modifier')
                    ->icon('heroicon-m-pencil-square')
                    ->iconButton()
                    ->fillForm(fn (Model $record) => [
                        'affectation_id' => $record->affectation_id,
                        'code' => $record->code,
                        'libelle_court' => $record->libelle_court,
                        'libelle_long' => $record->libelle_long,
                    ])
                    ->schema([
                        Select::make('affectation_id')
                            ->label('Affectation')
                            ->options(
                                $this->ownerRecord->affectations
                                    ->pluck('libelle_court', 'id')
                                    ->toArray()
                            )
                            ->required()
                            ->searchable(),

                        TextInput::make('code')->required()->maxLength(150),
                        TextInput::make('libelle_court')->required()->maxLength(150)->label('Libellé court'),
                        TextInput::make('libelle_long')->maxLength(1500)->label('Libellé long'),
                    ])
                    ->action(function (Model $record, array $data): void {
                        $record->update([
                            'affectation_id' => $data['affectation_id'],
                            'code' => $data['code'],
                            'libelle_court' => $data['libelle_court'],
                            'libelle_long' => $data['libelle_long'] ?? null,
                        ]);

                        Notification::make()
                            ->title('Compétence mise à jour')
                            ->success()
                            ->send()
                        ;
                    }),

                Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->iconButton()
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (Model $record) => $record->delete()),
            ])

        ;
    }
}
