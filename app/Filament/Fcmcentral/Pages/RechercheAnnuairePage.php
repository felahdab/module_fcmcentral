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
use Modules\FcmCommun\Models\Marin;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\RH\Filament\RH\Pages\RechercheAnnuaireForms\RechercheAnnuaireCreateUserOrMarinForm;

use Modules\FcmCentral\Events\SaveFcmMarinEvent;
use Modules\FcmCentral\Events\AssignerMarinParcoursEvent;
use Modules\FcmCentral\Filament\Fcmcentral\Pages\RechercheAnnuaireForms\RechercheAnnuaireForm;

class RechercheAnnuairePage extends RechercheAnnuairePageTemplate
{
    protected static ?string $navigationGroup = 'Marins';

    public function getRowActions()
    {
        return [
            Action::make('create-local-user-or-marin')
            ->visible(function(){
                return auth()->check();
            })
            ->icon('heroicon-m-user-plus')
            ->label("Créé une fiche Marin et/ou un utilisateur local")
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
                $marin = Marin::where("email", $record->email)->first();
                if (Arr::get($data, 'suivre_en_fcm'))
                {
                    // $marin->suivi_en_fcm = true;
                }
                if (Arr::get($data, 'cohorte'))
                {
                    // $event = ["cohorte_id" =>Arr::get($data, 'cohorte') ];
                    // event(new SaveFcmMarinEvent($marin, $event));
                }
                if (Arr::get($data, 'mentor_id'))
                {
                    // $event = ["mentor_id" =>Arr::get($data, 'mentor_id') ];
                    // event(new SaveFcmMarinEvent($marin, $event));
                }
                if (Arr::get($data, 'parcoursserialise_id'))
                {
                    // $parcoursSerialise = ParcoursSerialise::find($data['parcoursserialise_id']);
                    // if (!$parcoursSerialise) {
                    //     throw new \Exception('Le Parcours serialise selectionne est introuvable');
                    // }
                    // event(new AssignerMarinParcoursEvent($marin, $parcoursSerialise, []));
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
                ->icon('heroicon-o-plus')
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
