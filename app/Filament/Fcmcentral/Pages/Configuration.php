<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages;

use Illuminate\Support\Arr;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Action;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Fieldset;

use Filament\Pages\Page;

use App\Models\Setting;

class Configuration extends Page implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected static ?string $title = 'Configuration du module FCM Central';
    protected static ?string $navigationLabel = 'Configuration';

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'fcmcentral::filament.fcmcentral.pages.configuration';

    public ?array $data = [];
    
    public function mount(): void
    {
        $this->data = array_merge(Setting::forKey('fcmcentral')->data ?? [], Setting::forKey('fcmcommun')->data ?? []);
        $this->form->fill($this->data);
    }

    public static function canAccess(): bool
    {
        // TODO fcmcentral::change_module_configuration permission must be seeded into the permissions when intalling this module.
        return auth()->user()->can('fcmcentral::change_module_configurartion');
    }
    
    public function validateConfigurationAction()
    {
        return Action::make('validateConfigurationAction')
            ->label('Valider')
            ->action(fn() => $this->save());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Toggle::make('transmit_user_generated_events_to_remote_fcmcentral_instance')
                    ->label("Transmettre les évènements locaux à une instance distante ?")
                    ->live(),
                Fieldset::make('remote_transmit_settings')
                    ->label('Paramètres de transmission à distance')
                    ->visible(fn (Get $get) => $get('transmit_user_generated_events_to_remote_fcmcentral_instance'))
                    ->schema([
                        TextInput::make('url_of_remote_fcmcentral_instance')
                            ->label('URL de l\'instance distante')
                            ->required(),
                        TextInput::make('token_for_remote_fcmcentral_instance')
                            ->label('Token à utiliser')
                            ->required(),
                    ]),
                Toggle::make('retreive_cohortes_from_distant_fcmcentral_module')
                    ->label("Récupérer les cohortes auprès d'un serveur FCM Central distant ?")
                    ->live(),
                Fieldset::make('remote_cohorte_retreive_settings')
                    ->label('Paramètres de récupération des cohortes à distance')
                    ->visible(fn (Get $get) => $get('retreive_cohortes_from_distant_fcmcentral_module'))
                    ->schema([
                        TextInput::make('cohortes_url_of_remote_fcmcentral_instance')
                            ->label('URL de l\'instance distante')
                            ->required(),
                        TextInput::make('cohorte_token_for_remote_fcmcentral_instance')
                            ->label('Token à utiliser')
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }
    
    public function save(): void
    {
        $formdata = $this->form->getState();
        $fcmcentralsettings = [];
        $fcmcentralsettings['transmit_user_generated_events_to_remote_fcmcentral_instance'] = Arr::get($formdata, 'transmit_user_generated_events_to_remote_fcmcentral_instance') ;
        $fcmcentralsettings['url_of_remote_fcmcentral_instance'] = Arr::get($formdata, 'url_of_remote_fcmcentral_instance') ;
        $fcmcentralsettings['token_for_remote_fcmcentral_instance'] = Arr::get($formdata, 'token_for_remote_fcmcentral_instance') ;
        Setting::forKey('fcmcentral')->updateSetting($fcmcentralsettings);

        $fcmcommunsettings = [];
        $fcmcommunsettings['retreive_cohortes_from_distant_fcmcentral_module'] = Arr::get($formdata, 'retreive_cohortes_from_distant_fcmcentral_module') ;
        $fcmcommunsettings['cohortes_url_of_remote_fcmcentral_instance'] = Arr::get($formdata, 'cohortes_url_of_remote_fcmcentral_instance') ;
        $fcmcommunsettings['cohorte_token_for_remote_fcmcentral_instance'] = Arr::get($formdata, 'cohorte_token_for_remote_fcmcentral_instance') ;
        Setting::forKey('fcmcommun')->updateSetting($fcmcommunsettings);
    }
}
