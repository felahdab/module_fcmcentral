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
                ])
            ]);    
    }
 
    protected function getFooterWidgets(): array
    {
        return [];
    }
 
    }
