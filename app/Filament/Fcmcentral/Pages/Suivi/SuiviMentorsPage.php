<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages\Suivi;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\MentorAssignment;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\RH\Models\Scopes\MarinFiltreParUniteScope;

class SuiviMentorsPage extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected string $view = 'fcmcentral::filament.fcmcentral.pages.suivi.suivi-mentors';
    protected static string|\UnitEnum|null $navigationGroup = 'Suivi';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Suivi des Mentors';
    protected static ?int $navigationSort = 10;

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->check() && auth()->user()->can('fcmcentral::suivi.mentor');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->buildQuery())
            ->groups([
                Group::make('mentor.nom')
                    ->label('Mentor')
                    ->getTitleFromRecordUsing(fn (MentorAssignment $record): string => trim(
                        ($record->mentor?->grade?->libelle_court ?? '').' '.$record->mentor?->prenom.' '.$record->mentor?->nom
                    ))
                    ->collapsible(),
            ])
            ->defaultGroup('mentor.nom')
            ->defaultSort('mentor.nom')
            ->columns([
                TextColumn::make('mentor.grade.libelle_court')
                    ->label('Grade mentor')
                    ->badge()
                    ->toggleable(),

                TextColumn::make('mentor.nom')
                    ->label('Mentor')
                    ->formatStateUsing(fn (MentorAssignment $record): string => trim(
                        ($record->mentor?->grade?->libelle_court ?? '').' '.$record->mentor?->prenom.' '.$record->mentor?->nom
                    ))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->orWhereHas(
                        'mentor',
                        fn (Builder $q) => $q->withoutGlobalScope(MarinFiltreParUniteScope::class)
                            ->where('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%")
                    ))
                    ->sortable(),

                TextColumn::make('mentor.unite.libelle_court')
                    ->label('Unité mentor')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                TextColumn::make('fcmMarin.marin.grade.libelle_court')
                    ->label('Grade')
                    ->badge()
                    ->toggleable(),

                TextColumn::make('fcmMarin.marin.nom')
                    ->label('Marin supervisé')
                    ->formatStateUsing(fn (MentorAssignment $record): string => trim(
                        ($record->fcmMarin?->marin?->grade?->libelle_court ?? '').' '.$record->fcmMarin?->marin?->prenom.' '.$record->fcmMarin?->marin?->nom
                    ))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->orWhereHas(
                        'fcmMarin.marin',
                        fn (Builder $q) => $q->withoutGlobalScope(MarinFiltreParUniteScope::class)
                            ->where('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%")
                    ))
                    ->sortable(),

                TextColumn::make('fcmMarin.marin.unite.libelle_court')
                    ->label('Unité marin')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                IconColumn::make('decalage_unite')
                    ->label('Alerte')
                    ->state(fn (MentorAssignment $record): bool => $this->hasUniteMismatch($record))
                    ->icon(fn (bool $state): string => $state ? 'heroicon-o-exclamation-triangle' : 'heroicon-o-check-circle')
                    ->color(fn (bool $state): string => $state ? 'danger' : 'success')
                    ->tooltip(fn (bool $state): string => $state
                        ? 'Le mentor n\'est pas dans la même unité que le marin supervisé'
                        : 'Unités cohérentes'),
            ])
            ->filters([
                Filter::make('decalage_unite')
                    ->label('Uniquement les décalages d\'unité')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereRaw('(
                        select m.unite_id from rh_marins m where m.id = fcmcentral_fcm_marin_mentors.marin_id
                    ) != (
                        select rm.unite_id
                        from rh_marins rm
                        inner join fcmcentral_fcm_marins fm on fm.rh_marin_id = rm.id
                        where fm.id = fcmcentral_fcm_marin_mentors.fcm_marin_id
                    )')),
            ])
            ->recordActions([
                Action::make('gererMentors')
                    ->label('Gérer les mentors')
                    ->icon('heroicon-o-pencil-square')
                    ->color('gray')
                    ->modalHeading('Gestion des mentors')
                    ->modalDescription(fn (MentorAssignment $record) => $record->fcmMarin?->marin?->fullNameAndGrade)
                    ->schema([
                        Select::make('mentors')
                            ->label('Choisir les mentors')
                            ->helperText('Par défaut, seuls les marins de la même unité que le mentoré sont proposés, pour éviter les décalages. Les mentors déjà attribués restent visibles même s\'ils sont dans une autre unité.')
                            ->multiple()
                            ->options(function (MentorAssignment $record) {
                                $uniteId = $record->fcmMarin?->marin?->unite_id;
                                $mentorsActuels = $record->fcmMarin?->mentors->pluck('id')->all() ?? [];

                                return Marin::query()
                                    ->withoutGlobalScope(MarinFiltreParUniteScope::class)
                                    ->whereNull('deleted_at')
                                    // On garde aussi les mentors déjà attribués, même hors de l'unité,
                                    // pour ne pas les faire disparaître silencieusement du formulaire.
                                    ->where(fn ($q) => $q->when($uniteId, fn ($q) => $q->where('unite_id', $uniteId))
                                        ->when($mentorsActuels, fn ($q) => $q->orWhereIn('id', $mentorsActuels)))
                                    ->orderBy('nom')
                                    ->get()
                                    ->mapWithKeys(fn ($m) => [
                                        $m->uuid => ($m->grade?->libelle_court ?? '').' '.$m->prenom.' '.$m->nom,
                                    ]);
                            })
                            ->searchable()
                            ->default(fn (MentorAssignment $record) => $record->fcmMarin?->mentors->pluck('uuid')->toArray() ?? []),
                    ])
                    ->action(function (MentorAssignment $record, array $data): void {
                        $marin = $record->fcmMarin?->marin;

                        if (!$marin || !isset($data['mentors'])) {
                            return;
                        }

                        event(new UserGeneratedEvent(
                            UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN,
                            auth()->user()?->uuid,
                            ObjectClasses::MARIN,
                            $marin->nid,
                            // 'mentors' ET 'mentor' : MentorMarinListener a existé sous deux formes
                            // (clé plurielle avec ??[], clé singulière avec Arr::wrap) selon l'environnement.
                            ['mentors' => $data['mentors'], 'mentor' => $data['mentors']]
                        ));
                    }),
            ])
            ->emptyStateHeading('Aucun mentorat trouvé')
            ->emptyStateDescription('Aucun marin ne possède de mentor attribué pour le moment.')
            ->emptyStateIcon('heroicon-o-user-group')
            ->searchable()
            ->paginated([25, 50, 100]);
    }

    protected function buildQuery(): Builder
    {
        // On désactive le scope par unité : cette page doit permettre de repérer
        // les mentors situés dans une unité différente de celle de leur mentoré,
        // quelle que soit l'unité de l'utilisateur connecté.
        return MentorAssignment::query()
            ->with([
                'mentor' => fn ($q) => $q->withoutGlobalScope(MarinFiltreParUniteScope::class)->with(['grade', 'unite']),
                'fcmMarin.marin' => fn ($q) => $q->withoutGlobalScope(MarinFiltreParUniteScope::class)->with(['grade', 'unite']),
            ]);
    }

    protected function hasUniteMismatch(MentorAssignment $record): bool
    {
        $mentorUniteId = $record->mentor?->unite_id;
        $marinUniteId = $record->fcmMarin?->marin?->unite_id;

        if (blank($mentorUniteId) || blank($marinUniteId)) {
            return false;
        }

        return $mentorUniteId !== $marinUniteId;
    }
}
