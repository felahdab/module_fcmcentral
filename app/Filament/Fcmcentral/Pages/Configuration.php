<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages;

use Filament\Pages\Page;

use App\Models\Setting;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

use Filament\Forms\Components\MarkdownEditor;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Form;
use Filament\Actions\Action;
use Filament\Forms\Get;

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
        $this->data = Setting::forKey('fcmcentral')->data;
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
                TextInput::make('url_of_remote_fcmcentral_instance')
                    ->label('URL de l\'instance distante')
                    ->hidden(fn (Get $get) => ! $get('transmit_user_generated_events_to_remote_fcmcentral_instance'))
                    ->required(),
                TextInput::make('token_for_remote_fcmcentral_instance')
                    ->label('Token à utiliser')
                    ->hidden(fn (Get $get) => ! $get('transmit_user_generated_events_to_remote_fcmcentral_instance'))
                    ->required(),
            ])
            ->statePath('data');
    }
    
    public function save(): void
    {
        Setting::forKey('fcmcentral')->updateSetting($this->form->getState());
    }
}
