<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages;

use Illuminate\support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;

use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Support\Enums;

use App\Events\UnUtilisateurLocalDoitEtreCreeEvent;
use App\Filament\PageTemplates\RechercheAnnuairePageTemplate;
use App\Models\Role;
use App\Models\User;

use Modules\RH\Events\UnMarinDoitEtreCreeEvent;
use Modules\RH\Filament\RH\Pages\RechercheAnnuaireForms\RechercheAnnuaireCreateUserOrMarinForm;

use Modules\FcmCommun\Models\Marin;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\RechercheAnnuaireForms\RechercheAnnuaireForm;


class RechercheAnnuairePage extends RechercheAnnuairePageTemplate
{
    protected static ?string $navigationGroup = 'Marins';
    protected static ?string $navigationLabel = 'Recherche Annudef';

    public function getRowActions()
    {
        return [
            Action::make('create-local-user-or-marin')
            ->visible(function(){
                return auth()->check();
            })
            ->iconButton()
            ->icon('heroicon-m-user-plus')
            ->extraAttributes([
                'title' => 'Créer une fiche Marin et/ou un utilisateur local',
                'class' => 'btn-consult',
                ])
            ->requiresConfirmation()
            ->modalSubmitAction(false)
            ->modalWidth(Enums\MaxWidth::SevenExtraLarge)
            ->form([
                Wizard::make(array_merge(RechercheAnnuaireCreateUserOrMarinForm::getSchema(), 
                                        RechercheAnnuaireForm::getSchema()))
                ->submitAction(new HtmlString(Blade::render(<<<BLADE
                <x-filament::button
                    type="submit"
                    size="md"
                >
                    Valider
                </x-filament::button>
            BLADE)))
                
            ])
            ->action(function ($record, $data){
                /**
                 * #attributes: array:6 [▼
                *    "id" => 1
                *    "nom" => "EL-AHDAB"
                *    "prenom" => "Florian"
                *    "email" => "florian.el-ahdab@intradef.gouv.fr"
                *    "unite" => "MARINE/FRSTRIKEFOR/C2N - CENTRE COMBAT NAVAL/DIRECTEUR"
                *    "nid" => "0012030028"
                *  ]
                 */
                /**
                 * $record contient nom, prenom, email, unite et nid
                 */
                
                if (Arr::get($data, 'user'))
                {
                    if (! User::where('email', $record->email)->first())
                    {
                        UnUtilisateurLocalDoitEtreCreeEvent::dispatch($record->toArray(), $data['roles']);
                    }
                }

                if (Arr::get($data, 'marin'))
                {
                    if (! Marin::where("nid", $record->nid)->first()){
                        $creation_data = array_merge($record->toArray(), $data);
                        if (Arr::get($data, 'user'))
                        {
                            $newUser= User::where('email', $record->email)->first();
                            $creation_data['user_id'] = $newUser->id;
                        }
                        UnMarinDoitEtreCreeEvent::dispatch($creation_data);
                    }
                }

                /**
                 * Pour la suite, il faut impérativement qu'il y ait bien un Marin en base correspondant au résultat de la recherche en cours.
                 * Sinon, on interrompt l'action.
                 */
                $marin = Marin::where("email", $record->email)->first();
                if ($marin == null)
                {
                    /**
                     * TODO: prévoir une notification pour prévenir l'utilisateur que la partie FCM de sa demande n'a pas pu être traitée
                     * et qu'il doit retourner dans la liste des Marin pour indiquer la cohorte, le mentor et le parcours.
                     */
                    return;
                }

                /**
                 * Ici, on a trouvé un marin en base sur lequel la suite des actions vont être exécutées
                 */

                if (Arr::get($data, 'suivre_en_fcm'))
                {
                    $event = new UserGeneratedEvent(
                        UserGeneratedEventTypes::MARIN_EN_FCM,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $marin->uuid,
                        []
                    );
                    event($event);
                }
                if (Arr::get($data, 'cohorte_id'))
                {
                    $event = new UserGeneratedEvent(
                        UserGeneratedEventTypes::ATTRIBUER_COHORTE_MARIN,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $marin->uuid,
                        [
                            "cohorte" => $data["cohorte_id"],
                        ]
                    );
                    event($event);
                }
                if (Arr::get($data, 'mentor_id'))
                {
                    $event = new UserGeneratedEvent(
                        UserGeneratedEventTypes::ATTRIBUER_MENTOR_MARIN,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $marin->uuid,
                        [
                            "mentor" => $data["mentor_id"],
                        ]
                    );
                    event($event);
                }
                if (Arr::get($data, 'parcours_id'))
                {
                    $event = new UserGeneratedEvent(
                        UserGeneratedEventTypes::ATTRIBUER_PARCOURS_SERIALISE_MARIN,
                        auth()->user()->uuid,
                        ObjectClasses::MARIN,
                        $marin->uuid,
                        [
                            "parcours" =>  $data["parcours_id"],
                        ]
                    );
                    event($event);
                }

            })
        ];
    }

    public function getBulkActions()
    {
        return [
            BulkAction::make('create-local-user')
                ->visible(function(){
                    return auth()->check() && auth()->user()->can('users.store');
                })
                ->icon('heroicon-m-user-plus')
                ->label("Créé l'utilisateur local")
                ->requiresConfirmation()
                ->form([
                    Select::make('roles')
                        ->label("Rôles à attribuer")
                        ->options(Role::all()->pluck('name', 'id'))
                        ->multiple()
                        ->required()
                ])
                ->action(function ($records, $data){
                    foreach($records as $record){
                        UnUtilisateurLocalDoitEtreCreeEvent::dispatch($record->toArray(), $data['roles']);
                    }
                }),
        ];
    }
}
