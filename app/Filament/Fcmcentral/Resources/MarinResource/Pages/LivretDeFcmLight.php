<?php
 
namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Livewire;
use Filament\Resources\Pages\ViewRecord;

use Modules\FcmCommun\Http\Livewire\LivretFcmLight as LivretFcm;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;

use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\User;
use Modules\FcmCentral\Events\UserGeneratedEvent;
 
class LivretDeFcmLight extends ViewRecord
{
    
    protected static string $resource = MarinResource::class;
    protected static ?string $title = 'Livret du marin';
    
    protected function authorizeAccess(): void
    {
        $user=auth()->user();
        $user = User::find($user->id); 
        // On ne peut pas utiliser le cast_as_eloquent_descendant car on veut ensuite utiliser marin.
        // Si on utilise cast_as_eloquent_descendant, $user->marin est serialisé en tableau et le cas suivant échoue.
        $marin = $user->marin;

        if ($marin != null) 
        {
            /** L'utilisateur a une fiche de Marin. Il peut donc peut-être être mentor. */
            $marin = cast_as_eloquent_descendant($marin, Marin::class);
            
            if ($marin->est_mentor_de($this->record))
            {
                logger()->info("Le marin du User est bien le mentor du marin dont on parle.");
                return;
            }
            
        }
        /** L'utilisateur n'a pas de fiche de Marin. Il ne peut pas être mentor. On applique les règles normales Filament. */
        abort_unless(static::getResource()::canView($this->getRecord()), 403);
    }

    public static function authorizeResourceAccess(): void
    {
        return;
    }

    public function getRelationManagers(): array
    {
        return [];
    }
    
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(fn() => 'Livret de ' . ($this->record->nom.' '. $this->record->prenom) )
                    ->schema([    
                        Livewire::make(LivretFcm::class, [
                            "marinId" => $this->record->id,
                            "Marin" => Marin::class,
                            "UserGeneratedEvent" => UserGeneratedEvent::class,
                            "PermissionPrefix" => "fcmcentral"
                        ]),
                    ])->columnSpan(2)
            ]);
    }
 
    protected function getFooterWidgets(): array
    {
        return [];
    }
 
    }
