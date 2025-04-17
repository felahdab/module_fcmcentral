<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages;

use Illuminate\support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;

use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Support\Enums;

use App\Events\UnUtilisateurLocalDoitEtreCreeEvent;
use App\Filament\PageTemplates\RechercheAnnuairePageTemplate;
use App\Models\Role;
use App\Models\User;

use Modules\RH\Events\UnMarinDoitEtreCreeEvent;

use Modules\RH\Models\Marin;
use Modules\RH\Models\Grade;
use Modules\RH\Models\Specialite;
use Modules\RH\Models\Brevet;
use Modules\RH\Models\Unite;

use Modules\RH\Filament\RH\Pages\RechercheAnnuaireForms\RechercheAnnuaireCreateUserOrMarinForm;
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
                    size="sm"
                >
                    Valider
                </x-filament::button>
            BLADE)))
                
            ])
            ->action(function ($record, $data){
                // array:11 [▼ // vendor/spatie/laravel-ignition/src/helpers.php:14
                //   "marin" => true
                //   "matricule" => "123"
                //   "nid" => "321"
                //   "date_embarq" => null
                //   "date_debarq" => null
                //   "grade_id" => "9"
                //   "specialite_id" => "8"
                //   "brevet_id" => "3"
                //   "unite_id" => "8"
                //   "user" => true
                //   "roles" => array:1 [▶]
                // ]
                
                if (Arr::get($data, 'user'))
                {
                    UnUtilisateurLocalDoitEtreCreeEvent::dispatch($record->toArray(), $data['roles']);
                }

                if (Arr::get($data, 'marin'))
                {
                    $creation_data = array_merge($record->toArray(), $data);
                    if (Arr::get($data, 'user'))
                    {
                        $newUser= User::where('email', $record->email)->first();
                        $creation_data['user_id'] = $newUser->id;
                    }
                    UnMarinDoitEtreCreeEvent::dispatch($creation_data);
                }
                
                if (Arr::get($data, 'cohorte'))
                {

                }
                if (Arr::get($data, 'mentor_id'))
                {

                }
                if (Arr::get($data, 'parcoursserialise_id'))
                {

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
