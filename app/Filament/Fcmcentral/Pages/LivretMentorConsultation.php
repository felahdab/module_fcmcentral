<?php
 
namespace Modules\FcmCentral\Filament\Fcmcentral\Pages;
 
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Components\Livewire;
use Modules\FcmCommun\Http\Livewire\LivretFcmLight as LivretFcm;
use Modules\FcmCentral\Traits\FcmAccessControl;


 
class LivretMentorConsultation extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'fcmcentral::filament.fcmcentral.pages.mon-livret-de-fcm';
    protected static bool $shouldRegisterNavigation = false; // Pas dans le menu
    
 
    public Marin $marin;
    public int $marinId;

    
 
    public static function canAccess(array $parameters = []): bool
    {
        return static::canAccessMentorFeatures();
    }

 
    public function mount()
    {
        
        
      
        
        $this->marinId = request()->get('marinId');
        if (!$this->marinId){
            abort(404, 'ID Marin manquant');
        }
        $marinCible = Marin::findOrFail($this->marinId);
        $currentMarin = Marin::fromCurrentUser();
        
        if (!static::canAccessMentorFeatures()) {
            abort(403, 'Accès non autorisé');
        }

        
        $estSupervise = FcmMarin::where('mentor_id', $currentMarin->id)
            ->where('rh_marin_id', $this->marinId)
            ->exists();
            
        if (!$estSupervise) {
            abort(403, 'Vous n\'êtes pas le mentor de ce marin');
        }
        
        $this->marin = $marinCible;
        $this->form->fill();
    }
 
    public function form(Form $form): Form
    {
        return $form->schema([
            Livewire::make(LivretFcm::class, [
                "marin" => $this->marin,
                "canValidate" => static::canValidateElements(),
            ]),
        ]);
    }
 
    public function getTitle(): string
    {
        return "Livret FCM de {$this->marin->nom} {$this->marin->prenom}";
    }

 





}
