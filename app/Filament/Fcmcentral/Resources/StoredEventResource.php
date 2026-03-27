<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources;

use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use App\Models\User;
use Modules\RH\Models\Marin as RHMarin;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\Objectif;
use Modules\FcmCentral\Models\CompetenceSavoirfaire;
use Modules\FcmCommun\Models\Cohorte;
use Modules\FcmCentral\Models\StoredEvent;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\StoredEventResource\Pages\ListStoredEvents;

class StoredEventResource extends Resource
{
    protected static ?string $model = StoredEvent::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static string|\UnitEnum|null $navigationGroup = 'Administration';
    protected static ?string $navigationLabel = 'Journal des événements';
    protected static ?string $modelLabel = 'Événement';
    protected static ?string $pluralModelLabel = 'Journal des événements';
    protected static ?int $navigationSort = 99;

    public static function canCreate(): bool { return false; }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return StoredEvent::filtre_par_unite();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([

                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),

                TextColumn::make('event_type')
                    ->label('Action')
                    ->badge()
                    ->sortable()
                    ->color(fn ($state) => match ($state) {
                        'valid_elmt_marin_parcours'          => 'success',
                        'propos_elmt_marin_parcours'         => 'warning',
                        'annul_elmt_marin_parcours',
                        'annul_propos_elmt_marin_parcours',
                        'marin_pas_fcm'                      => 'danger',
                        'marin_en_fcm',
                        'attribuer_mentor_marin',
                        'attribuer_cohorte_marin',
                        'attribuer_parcours_serialise_marin' => 'info',
                        'creation_parcours_serialise'        => 'primary',
                        'definir_jalon_fcm'                  => 'gray',
                        default                              => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'marin_en_fcm'                       => 'Marin en FCM',
                        'marin_pas_fcm'                      => 'Marin retiré FCM',
                        'creation_parcours_serialise'        => 'Création version parcours',
                        'attribuer_parcours_serialise_marin' => 'Attribution parcours',
                        'attribuer_cohorte_marin'            => 'Attribution cohorte',
                        'attribuer_mentor_marin'             => 'Attribution mentor',
                        'definir_jalon_fcm'                  => 'Jalon FCM',
                        'propos_elmt_marin_parcours'         => 'Proposé',
                        'annul_propos_elmt_marin_parcours'   => 'Annul. proposition',
                        'valid_elmt_marin_parcours'          => 'Validé',
                        'annul_elmt_marin_parcours'          => 'Annulé',
                        default                              => $state,
                    }),

                TextColumn::make('user_uuid')
                    ->label('Auteur')
                    ->searchable()
                    ->formatStateUsing(function ($state) {
                        if (!$state) return '—';
                        $user = User::where('uuid', $state)->first();
                        return $user?->display_name ?? $user?->name ?? '—';
                    }),

                TextColumn::make('object_uuid')
                    ->label('Marin / Objet')
                    ->formatStateUsing(function ($state, $record) {
                        if (!$state) return '—';

                        $class = strtolower(class_basename($record->object_class ?? ''));

                        return match (true) {
                            str_contains($class, 'marin') =>
                                RHMarin::where('uuid', $state)->first()?->fullNameAndGrade ?? $state,

                            str_contains($class, 'parcours') =>
                                Parcours::where('uuid', $state)->value('libelle_court') ?? $state,

                            default => $state,
                        };
                    }),

                TextColumn::make('detail_formate')
                ->label('Détail')
                ->wrap()
                ->getStateUsing(function ($record): string|\Illuminate\Support\HtmlString {
                    $detail    = is_array($record->detail) ? $record->detail : [];
                    $eventType = $record->event_type;
            
                    if (empty($detail)) return '—';
            
                    return match ($eventType) {
            
                        'creation_parcours_serialise' =>
                            'Version ' . ($detail['version'] ?? '?') .
                            ' — dès le ' . (isset($detail['date_de_debut'])
                                ? \Carbon\Carbon::parse($detail['date_de_debut'])->format('d/m/Y')
                                : '?'),
            
                        'attribuer_parcours_serialise_marin' =>
                            'Parcours : ' . (Parcours::where('uuid', $detail['parcours'] ?? '')->value('libelle_court') ?? ($detail['parcours'] ?? '?')),
            
                        'attribuer_cohorte_marin' =>
                            'Cohorte : ' . (Cohorte::where('uuid', $detail['cohorte'] ?? '')->value('libelle_court') ?? ($detail['cohorte'] ?? '?')),
            
                        'attribuer_mentor_marin' =>
                            'Mentor : ' . (RHMarin::where('uuid', $detail['mentor'] ?? '')->first()?->fullNameAndGrade ?? ($detail['mentor'] ?? '?')),
            
                        'definir_jalon_fcm' => collect([
                                isset($detail['date_sedimentation']) ? 'Sédimentation : ' . \Carbon\Carbon::parse($detail['date_sedimentation'])->format('d/m/Y') : null,
                                isset($detail['date_fcm'])           ? 'FCM : '           . \Carbon\Carbon::parse($detail['date_fcm'])->format('d/m/Y')           : null,
                                isset($detail['date_competence'])    ? 'Compétence : '    . \Carbon\Carbon::parse($detail['date_competence'])->format('d/m/Y')    : null,
                            ])->filter()->implode(' | '),
            
                        'propos_elmt_marin_parcours',
                        'annul_propos_elmt_marin_parcours',
                        'valid_elmt_marin_parcours',
                        'annul_elmt_marin_parcours' =>
                            static::formatElementDetail($detail),
            
                        default => collect($detail)
                            ->map(fn ($v, $k) => is_scalar($v) ? "$k: $v" : null)
                            ->filter()
                            ->implode(' | ') ?: '—',
                    };
                }),
            

            ])
            ->filters([
                SelectFilter::make('event_type')
                    ->label('Type d\'action')
                    ->options([
                        'marin_en_fcm'                       => 'Marin en FCM',
                        'marin_pas_fcm'                      => 'Marin retiré FCM',
                        'creation_parcours_serialise'        => 'Création version parcours',
                        'attribuer_parcours_serialise_marin' => 'Attribution parcours',
                        'attribuer_cohorte_marin'            => 'Attribution cohorte',
                        'attribuer_mentor_marin'             => 'Attribution mentor',
                        'definir_jalon_fcm'                  => 'Jalon FCM',
                        'propos_elmt_marin_parcours'         => 'Proposé',
                        'annul_propos_elmt_marin_parcours'   => 'Annul. proposition',
                        'valid_elmt_marin_parcours'          => 'Validé',
                        'annul_elmt_marin_parcours'          => 'Annulé',
                    ]),

                Filter::make('periode')
                    ->label('Période')
                    ->form([
                        DatePicker::make('from')->label('Du'),
                        DatePicker::make('until')->label('Au'),
                    ])
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['from'], fn ($q) => $q->whereDate('created_at', '>=', $data['from']))
                        ->when($data['until'], fn ($q) => $q->whereDate('created_at', '<=', $data['until']))
                    ),
            ]);
    }

    /**
     * Formate le détail pour les événements de type élément parcours
     * (objectif ou savoirfaire/csf)
     */
    private static function formatElementDetail(array $detail): \Illuminate\Support\HtmlString
    {
        $type        = $detail['type_element'] ?? null;
        $uuid        = $detail['uuid_element'] ?? null;
        $date        = isset($detail['date']) ? \Carbon\Carbon::parse($detail['date'])->format('d/m/Y') : null;
        $commentaire = $detail['commentaire'] ?? null;

        $libelle = '—';

        if ($uuid) {
            $libelle = match ($type) {
                'objectif' =>
                    Objectif::where('uuid', $uuid)->value('code') ?? $uuid,

                'savoirfaire', 'csf', 'competence_savoirfaire' =>
                    CompetenceSavoirfaire::where('uuid', $uuid)
                        ->with('savoirfaire')
                        ->first()
                        ?->savoirfaire
                        //?->libelle_court ?? $uuid
                        ?->code?? $uuid
                        ,

                default => $uuid,
            };
        }

        $items = collect([
            $type    ? '<li><span class="font-medium">' . ucfirst($type) . ' :</span> ' . e($libelle) . '</li>' : null,
            $date    ? '<li><span class="font-medium">Date :</span> ' . $date . '</li>'                          : null,
            filled($commentaire) && $commentaire !== '/'
                    ? '<li><span class="font-medium">Commentaire :</span> ' . e($commentaire) . '</li>'         : null,
        ])->filter()->implode('');

        return new \Illuminate\Support\HtmlString(
            '<ul class="list-disc list-inside text-sm space-y-0.5">' . $items . '</ul>'
        );
    }


    public static function getPages(): array
    {
        return [
            'index' => ListStoredEvents::route('/'),
        ];
    }
}
