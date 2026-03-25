<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\CompetenceSavoirfaire;
use Modules\FcmCentral\Models\Savoirfaire;
use Modules\FcmCentral\Models\SavoirfaireType;

class SavoirfairesRelationManager extends RelationManager
{
    protected static string $relationship = 'competenceSavoirfaires'; 
    protected static ?string $title = 'Savoir-faires & Objectifs';

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public function table(Table $table): Table
    {
        $parcours = $this->ownerRecord;

        $competenceIds = Competence::where('parcours_type_id', $parcours->parcours_type_id)
            ->whereIn('affectation_id', $parcours->affectations->pluck('id')->toArray())
            ->pluck('id')
            ->toArray();

        return $table
            ->query(
                CompetenceSavoirfaire::whereIn('competence_id', $competenceIds)
                    ->with(['competence', 'savoirfaire', 'savoirfaireType', 'objectifs'])
            )
            ->reorderable('ordre') // ✅ table qualifiée
             ->defaultSort('ordre', 'asc') // ✅
            ->columns([
                TextColumn::make('ordre')
                    ->label('Ordre')
                    ->sortable(),
                TextColumn::make('competence.libelle_court')
                    ->label('Compétence')
                    ->badge()
                    ->color('info'),
                TextColumn::make('savoirfaire.code')
                    ->label('Code')
                    ->searchable(),
                TextColumn::make('libelle_court')
                    ->label('Libellé contexte')
                    ->searchable(),
                TextColumn::make('savoirfaireType.libelle_court')
                    ->label('Type')
                    ->badge()
                    ->color('warning'),
                TextColumn::make('heures')->label('Heures'),
                TextColumn::make('jours')->label('Jours'),
                TextColumn::make('objectifs_count')
                    ->counts('objectifs')
                    ->label('Objectifs')
                    ->badge()
                    ->color('success'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('+ Ajouter un savoir-faire')
                    ->form(fn (Schema $schema) => $schema->components([
                        Select::make('competence_id')
                            ->label('Compétence')
                            ->options(
                                Competence::whereIn('id', $competenceIds)
                                    ->with('affectation')
                                    ->get()
                                    ->mapWithKeys(fn ($c) => [
                                        $c->id => ($c->affectation?->libelle_court ?? '?') . ' — ' . $c->libelle_court
                                    ])
                                    ->toArray()
                            )
                            ->required()
                            ->searchable(),

                        Select::make('savoirfaire_id')
                            ->label('Savoir-faire existant')
                            ->options(fn ($get) =>
                                $get('competence_id')
                                    ? Savoirfaire::where('competence_id', $get('competence_id'))
                                        ->orderBy('libelle_court')
                                        ->pluck('libelle_court', 'id')
                                        ->toArray()
                                    : []
                            )
                            ->searchable()
                            ->live()
                            ->placeholder('Sélectionner ou créer ci-dessous'),

                        TextInput::make('nouvelle_code')
                            ->label('Ou créer — Code')
                            ->hidden(fn ($get) => $get('savoirfaire_id') !== null),

                        TextInput::make('nouvelle_libelle_court')
                            ->label('Libellé court')
                            ->hidden(fn ($get) => $get('savoirfaire_id') !== null),
                        
                        TextInput::make('nouvelle_libelle_long')
                            ->label('Libellé long')
                            ->hidden(fn ($get) => $get('savoirfaire_id') !== null),

                        Select::make('savoirfaire_type_id')
                            ->label('Type')
                            ->options(SavoirfaireType::orderBy('libelle_court')->pluck('libelle_court', 'id'))
                            ->required()
                            ->searchable()
                            ->live(),

                        TextInput::make('libelle_court')->label('Libellé contexte'),
                        //TextInput::make('libelle_long')->label('Libellé long'),
                        TextInput::make('heures')->label('Heures')->numeric(),
                        TextInput::make('jours')->label('Jours')->numeric(),

                        // ✅ Objectifs si type C
                        Repeater::make('objectifs')
                            ->label('Objectifs')
                            ->addActionLabel('+ Ajouter un objectif')
                            ->defaultItems(0)
                            ->hidden(function ($get) {
                                $typeId = $get('savoirfaire_type_id');
                                if (!$typeId) return true;
                                $type = SavoirfaireType::find($typeId);
                                return strtoupper($type?->code ?? '') !== 'C';
                            })
                            ->schema([
                                TextInput::make('code')->label('Code')->required(),
                                TextInput::make('libelle_court')->label('Libellé court')->required(),
                                //Textarea::make('libelle_long')->label('Libellé long')->rows(2),
                                TextInput::make('heures')->label('Heures')->numeric(),
                                
                            ]),
                    ]))
                    ->action(function (array $data) use ($competenceIds): void {
                        // ✅ Récupère ou crée le savoir-faire
                        if (!empty($data['savoirfaire_id'])) {
                            $savoirfaire = Savoirfaire::find($data['savoirfaire_id']);
                        } else {
                            $savoirfaire = Savoirfaire::create([
                                'uuid'          => Str::uuid(),
                                'code'          => $data['nouvelle_code'] ?? '',
                                'libelle_court' => $data['nouvelle_libelle_court'] ?? '',
                                'libelle_long' => $data['nouvelle_libelle_long'] ?? '',
                                'competence_id' => $data['competence_id'],
                            ]);
                        }

                        if (!$savoirfaire) return;

                        // ✅ Crée le pivot CSF
                        $csf = CompetenceSavoirfaire::create([
                            'uuid'                => Str::uuid(),
                            'competence_id'       => $data['competence_id'],
                            'savoirfaire_id'      => $savoirfaire->id,
                            'savoirfaire_type_id' => $data['savoirfaire_type_id'] ?? null,
                            'libelle_court'       => $data['libelle_court'] ?? null,
                            //'libelle_long'       => $data['libelle_long'] ?? null,
                            'heures'              => $data['heures'] ?? null,
                            'jours'               => $data['jours'] ?? null,
                        ]);

                        // ✅ Crée les objectifs
                        foreach ($data['objectifs'] ?? [] as $obj) {
                            if (empty($obj['code'])) continue;
                            $csf->objectifs()->create([
                                'uuid'          => Str::uuid(),
                                'code'          => $obj['code'],
                                'libelle_court' => $obj['libelle_court'] ?? '',
                                'libelle_long'  => $obj['libelle_long'] ?? null,
                                'heures'        => $obj['heures'] ?? null,
                            ]);
                        }
                    }),
            ])
            ->recordActions([
                \Filament\Actions\Action::make('edit')
                    ->label('Modifier')
                    ->icon('heroicon-m-pencil-square')
                    ->iconButton()
                    ->fillForm(fn (\Illuminate\Database\Eloquent\Model $record) => [
                        'competence_id'       => $record->competence_id,
                        'savoirfaire_id'      => $record->savoirfaire_id,
                        'sf_code'             => $record->savoirfaire?->code,
                        'sf_libelle_court'    => $record->savoirfaire?->libelle_court,
                        'sf_libelle_long'     => $record->savoirfaire?->libelle_long,
                        'savoirfaire_type_id' => $record->savoirfaire_type_id,
                        'libelle_court'       => $record->libelle_court,
                        'libelle_long'        => $record->libelle_long,
                        'heures'              => $record->heures,
                        'jours'               => $record->jours,
                        'objectifs'           => $record->objectifs->map(fn ($obj) => [
                            'code'          => $obj->code,
                            'heures'        => $obj->heures,
                            'libelle_court' => $obj->libelle_court,
                            'libelle_long'  => $obj->libelle_long,
                        ])->toArray(),
                    ])
                    
                    
                    ->schema([
                        Select::make('competence_id')
                            ->label('Compétence')
                            ->options(
                                Competence::whereIn('id', $competenceIds)
                                    ->with('affectation')
                                    ->get()
                                    ->mapWithKeys(fn ($c) => [
                                        $c->id => ($c->affectation?->libelle_court ?? '?') . ' — ' . $c->libelle_court
                                    ])
                                    ->toArray()
                            )
                            ->required()
                            ->searchable(),
                    
                        Select::make('savoirfaire_id')
                            ->label('Savoir-faire')
                            ->options(fn ($get) =>
                                $get('competence_id')
                                    ? Savoirfaire::where('competence_id', $get('competence_id'))
                                        ->orderBy('libelle_court')
                                        ->pluck('libelle_court', 'id')
                                        ->toArray()
                                    : []
                            )
                            ->searchable()
                            ->live(),
                    
                        // ✅ Édition directe du savoir-faire sélectionné
                        TextInput::make('sf_code')
                            ->label('Code du savoir-faire')
                            ->required()
                            ->helperText('Modifie directement le code du savoir-faire'),
                    
                        TextInput::make('sf_libelle_court')
                            ->label('Libellé court du savoir-faire')
                            ->required(),
                    
                        TextInput::make('sf_libelle_long')
                            ->label('Libellé long du savoir-faire'),
                    
                        Select::make('savoirfaire_type_id')
                            ->label('Type')
                            ->options(SavoirfaireType::orderBy('libelle_court')->pluck('libelle_court', 'id'))
                            ->required()
                            ->searchable()
                            ->live(),
                    
                        TextInput::make('libelle_court')
                            ->label('Libellé contexte')
                            ->helperText('Intitulé spécifique au parcours (ex : stage embarqué, poste tenu…). Laissez vide pour utiliser le libellé du savoir-faire.'),
                    
                        TextInput::make('heures')->label('Heures')->numeric(),
                        TextInput::make('jours')->label('Jours')->numeric(),
                    
                        Repeater::make('objectifs')
                            ->label('Objectifs')
                            ->addActionLabel('+ Ajouter un objectif')
                            ->defaultItems(0)
                            ->hidden(function ($get) {
                                $typeId = $get('savoirfaire_type_id');
                                if (!$typeId) return true;
                                return strtoupper(SavoirfaireType::find($typeId)?->code ?? '') !== 'C';
                            })
                            ->schema([
                                TextInput::make('code')->label('Code')->required(),
                                TextInput::make('libelle_court')->label('Libellé court')->required(),
                                Textarea::make('libelle_long')->label('Libellé long')->rows(2),
                                TextInput::make('heures')->label('Heures')->numeric(),
                            ]),
                    ])
                    
                    ->action(function (\Illuminate\Database\Eloquent\Model $record, array $data): void {

                        // ✅ Mise à jour du savoir-faire lui-même
                        if ($data['savoirfaire_id']) {
                            Savoirfaire::where('id', $data['savoirfaire_id'])->update([
                                'code'          => $data['sf_code'],
                                'libelle_court' => $data['sf_libelle_court'],
                                'libelle_long'  => $data['sf_libelle_long'] ?? null,
                            ]);
                        }
                    
                        // ✅ Mise à jour du pivot CSF
                        $record->update([
                            'competence_id'       => $data['competence_id'],
                            'savoirfaire_id'      => $data['savoirfaire_id'],
                            'savoirfaire_type_id' => $data['savoirfaire_type_id'] ?? null,
                            'libelle_court'       => $data['libelle_court'] ?? null,
                            'libelle_long'        => $data['libelle_long'] ?? null,
                            'heures'              => $data['heures'] ?? null,
                            'jours'               => $data['jours'] ?? null,
                        ]);
                    
                        // ✅ Recréation des objectifs
                        $record->objectifs()->delete();
                        foreach ($data['objectifs'] ?? [] as $obj) {
                            if (empty($obj['code'])) continue;
                            $record->objectifs()->create([
                                'uuid'          => Str::uuid(),
                                'code'          => $obj['code'],
                                'libelle_court' => $obj['libelle_court'] ?? '',
                                'libelle_long'  => $obj['libelle_long'] ?? null,
                                'heures'        => $obj['heures'] ?? null,
                            ]);
                        }
                    
                        \Filament\Notifications\Notification::make()
                            ->title('Savoir-faire mis à jour')
                            ->success()
                            ->send();
                    }),
                    
            
                \Filament\Actions\Action::make('delete')
                    ->icon('heroicon-m-trash')
                    ->iconButton()
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(fn (\Illuminate\Database\Eloquent\Model $record) => $record->delete()),
            ])
            
                ;
            
            
    }
}
