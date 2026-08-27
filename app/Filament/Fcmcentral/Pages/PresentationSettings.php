<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Pages;

use App\Models\Setting;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PresentationSettings extends Page implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    // Nombre d'emplacements de logo fixes. Un Repeater (nombre illimité, ajout/suppression
    // dynamique) provoque une erreur connue de Filament (getStateSnapshot sur null) quand on
    // ajoute ou supprime un item sans recharger la page entre chaque. Des emplacements fixes,
    // tous présents dès le chargement, évitent complètement le problème.
    protected const NB_EMPLACEMENTS_LOGOS = 6;

    public ?array $data = [];

    protected static ?string $title = 'Présentation du dashboard';
    protected static ?string $navigationLabel = 'Présentation du dashboard';
    protected static string|\UnitEnum|null $navigationGroup = 'Administration';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-photo';

    protected string $view = 'fcmcentral::filament.fcmcentral.pages.presentation-settings';

    public function mount(): void
    {
        $this->data = Setting::forKey('fcmcentral_presentation')->data ?? [];
        $this->form->fill($this->data);
    }

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->check() && auth()->user()->can('fcmcentral::dashboard');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('texte')
                    ->label('Texte de présentation')
                    ->helperText('Affiché en haut du dashboard pour tous les utilisateurs.')
                    ->rows(6)
                    ->columnSpanFull(),

                Section::make('Logos / images')
                    ->description('Jusqu\'à '.self::NB_EMPLACEMENTS_LOGOS.' logos. Laissez un emplacement vide s\'il n\'est pas utilisé.')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema(collect(range(0, self::NB_EMPLACEMENTS_LOGOS - 1))
                            ->map(fn (int $i) => Section::make('Logo '.($i + 1))
                                ->compact()
                                ->schema([
                                    FileUpload::make("logos.{$i}.image")
                                        ->label('Image')
                                        ->image()
                                        ->disk('s3-public')
                                        ->directory('fcmcentral/dashboard/logos')
                                        ->visibility('public')
                                        ->imageEditor()
                                        ->imageEditorAspectRatios(['1:1'])
                                        ->helperText('Idéalement carrée, pour un affichage homogène.'),
                                    TextInput::make("logos.{$i}.libelle")
                                        ->label('Libellé (optionnel)'),
                                    TextInput::make("logos.{$i}.url")
                                        ->label('Lien (optionnel)')
                                        ->url(),
                                ]))
                            ->all()),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $this->data = $this->form->getState();

        $logosValides = collect($this->data['logos'] ?? [])
            ->filter(fn (?array $logo) => is_string($logo['image'] ?? null))
            ->values()
            ->all();

        $this->data['logos'] = $logosValides;

        Setting::forKey('fcmcentral_presentation')->updateSetting($this->data);

        \Filament\Notifications\Notification::make()
            ->title('Présentation enregistrée')
            ->success()
            ->send();
    }
}
