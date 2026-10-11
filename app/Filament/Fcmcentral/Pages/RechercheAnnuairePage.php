<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages;

use App\Events\UnUtilisateurLocalDoitEtreCreeEvent;
use App\Filament\PageTemplates\RechercheAnnuairePageTemplate;
use App\Models\Role;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Wizard;
use Filament\Support\Enums\Width;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\RechercheAnnuaireForms\RechercheAnnuaireForm;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCommun\Models\Marin;
use Modules\RH\Events\UnMarinDoitEtreCreeEvent;
use Modules\RH\Filament\RH\Pages\RechercheAnnuaireForms\RechercheAnnuaireCreateUserOrMarinForm;
use Modules\RH\Services\AnnudefGradeService;
use Modules\RH\Services\AnnudefUniteService;

class RechercheAnnuairePage extends RechercheAnnuairePageTemplate
{
    protected static string|\UnitEnum|null $navigationGroup = 'Marins';
    protected static ?string $navigationLabel = 'Recherche Annudef';

    public static function canAccess(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        $user = auth()->user();

        return $user->can('fcmcentral::recherche_annuaire')
        && auth()->user()->canAny(['users.create', 'rh::marins.create', 'fcmcentral::marin.suivreEnFcm']);
    }

    public function getRowActions()
    {
        return [
            Action::make('create-local-user-or-marin')
            // ->visible(function(){
            //     return auth()->check() && (static::canAccessAdminMenus() || static::canAccessMentorFeatures());
            // })
                ->iconButton()
                ->icon('heroicon-m-user-plus')
                ->extraAttributes([
                    'title' => 'Créer une fiche Marin et/ou un utilisateur local',
                    'class' => 'btn-consult',
                ])
                ->requiresConfirmation()
                ->modalSubmitAction(false)
                ->modalWidth(Width::SevenExtraLarge)
                ->fillForm(fn ($record): array => [
                    'nid' => $record->nid,
                    'grade_id' => AnnudefGradeService::getGradeFromAnnudefRank($record->gradelong)?->id,
                    'unite_id' => AnnudefUniteService::getUniteFromAnnudefBranch($record->unite)?->id
                ])
                ->schema([
                    Wizard::make(array_merge(
                        RechercheAnnuaireCreateUserOrMarinForm::getSchema(),
                        RechercheAnnuaireForm::getSchema()
                    ))
                        ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                                <x-filament::button
                                    type="submit"
                                    size="md"
                                >
                                    Valider
                                </x-filament::button>
                            BLADE))),
                ])
                ->action(function ($record, $data) {
                    // $record contient nom, prenom, email, unite et nid

                    if (Arr::get($data, 'user')) {
                        if (!User::where('email', $record->email)->first()) {
                            UnUtilisateurLocalDoitEtreCreeEvent::dispatch($record->toArray(), $data['roles']);
                        }
                    }

                    if (Arr::get($data, 'marin')) {
                        if (!Marin::where('nid', $record->nid)->first()) {
                            $creation_data = array_merge($record->toArray(), $data);

                            $existingUser = User::where('email', $record->email)->first();
                            
                            if ($existingUser) {
                                 $creation_data['user_id'] = $existingUser->id;
                            }

                            UnMarinDoitEtreCreeEvent::dispatch($creation_data);
                        }
                    }

                    /**
                 * Pour la suite, il faut impérativement qu'il y ait bien un Marin en base correspondant au résultat de la recherche en cours.
                 * Sinon, on interrompt l'action.
                 */
                    $marin = Marin::where('email', $record->email)->first();
                    if (Arr::get($data, 'suivre_en_fcm') && null == $marin) {
                        /*
                     * TODO: prévoir une notification pour prévenir l'utilisateur que la partie FCM de sa demande n'a pas pu être traitée
                     * et qu'il doit retourner dans la liste des Marin pour indiquer la cohorte, le mentor et le parcours.
                     */

                        Notification::make()
                            ->title('Il n\'y a pas de fiche de marin correspondant à ce résultat de recherche.')
                            ->body('Vous devez d\'abord créer la fiche de cette personne pour pouvoir ensuite la suivre en FCM.')
                            ->warning()
                            ->send()
                        ;

                        return;
                    }

                    // Ici, on a trouvé un marin en base sur lequel la suite des actions vont être exécutées

                    if (Arr::get($data, 'suivre_en_fcm')) {
                        $event = new UserGeneratedEvent(
                            UserGeneratedEventTypes::MARIN_EN_FCM,
                            auth()->user()->uuid,
                            ObjectClasses::MARIN,
                            $marin->nid,
                            []
                        );
                        event($event);
                    }
                    if (Arr::get($data, 'cohorte_id')) {
                        $event = new UserGeneratedEvent(
                            UserGeneratedEventTypes::ATTRIBUER_COHORTE_MARIN,
                            auth()->user()->uuid,
                            ObjectClasses::MARIN,
                            $marin->nid,
                            [
                                'cohorte' => $data['cohorte_id'],
                            ]
                        );
                        event($event);
                    }
                    if (!empty(Arr::get($data, 'mentors'))) {
                        $event = new UserGeneratedEvent(
                            UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN,
                            auth()->user()->uuid,
                            ObjectClasses::MARIN,
                            $marin->nid,
                            [
                                'mentors' => $data['mentors'],
                            ]
                        );
                        event($event);
                    }
                    if (Arr::get($data, 'parcours_id')) {
                        $event = new UserGeneratedEvent(
                            UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
                            auth()->user()->uuid,
                            ObjectClasses::MARIN,
                            $marin->nid,
                            [
                                'parcours' => $data['parcours_id'],
                            ]
                        );
                        event($event);
                    }
                }),
        ];
    }

    public function getBulkActions()
    {
        return [
            BulkAction::make('create-local-user')
                // ->visible(function(){
                //     return auth()->check() &&
                //     (auth()->user()->can('users.store')|| static::canAccessMentorFeatures());
                // })
                ->icon('heroicon-m-user-plus')
                ->label("Créé l'utilisateur local")
                ->requiresConfirmation()
                ->form([
                    Select::make('roles')
                        ->label('Rôles à attribuer')
                        ->options(Role::all()->pluck('name', 'id'))
                        ->multiple()
                        ->required(),
                ])
                ->action(function ($records, $data) {
                    foreach ($records as $record) {
                        UnUtilisateurLocalDoitEtreCreeEvent::dispatch($record->toArray(), $data['roles']);
                    }

                    Notification::make()
                        ->title('Utilisateurs créés')
                        ->success()
                        ->send()
                    ;
                }),
        ];
    }
}

