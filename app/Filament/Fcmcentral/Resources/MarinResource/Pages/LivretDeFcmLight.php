<?php
 
namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Livewire;
use Filament\Resources\Pages\ViewRecord;

use Modules\FcmCommun\Http\Livewire\LivretFcmLight as LivretFcm;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;

use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Events\UserGeneratedEvent;
 
class LivretDeFcmLight extends ViewRecord
{
    
    protected static string $resource = MarinResource::class;
    protected static ?string $title = 'Livret du marin';

     
    public static function canAccess(array $parameters = []): bool
    {
        // TODO Bugfix 713874: si l'utilisateur est le mentor FCM du marin dont on essaye d'ouvrir le livret de FCM: donner accès.
        return parent::canAccess($parameters);
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
