<?php
 
namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;
 
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
 
use Filament\Resources\Pages\ViewRecord;
 

use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Livewire;
use Filament\Forms\Components\Placeholder;
use Modules\FcmCommun\Http\Livewire\LivretFcm as LivretFcm;
use Modules\FcmCommun\Http\Livewire\FilamentSpeedTest;
use Modules\FcmCentral\Models\FcmMarin;
use Illuminate\Support\Facades\Log;
 
class LivretDeFcm extends ViewRecord
{
    protected static string $resource = MarinResource::class;
  
    public function getRelationManagers(): array
    {
        return [];
    }
    
    public function form(Form $form): Form
    {
        return $form
        ->schema([
            Section::make('Informations sur le marin ')
                ->schema([
                    Grid::make(2)
                    ->schema([
                        Placeholder::make('nom')
                            ->label('Nom Prénom')
                            ->content(fn ($record)=> $record->nom.' '. $record->prenom),
                        
                    ]),
                ]),
 
                
            Section::make('Livret de FCM')
                ->schema([    
                   // Livewire::make(FilamentSpeedTest::class),
                    // DONNÉES PRÉ-CHARGÉES OBLIGATOIRES
                    Livewire::make(LivretFcm::class, [
                        "fcmMarinId" => $this->record->complements_fcm->id,
                        "tablePrefix" => 'fcmcentral_',
                        "parcoursMarin" => $this->getParcoursMarin($this->record->complements_fcm->id),
                    ]),
                ])
            ]);    
    }
 
    protected function getFooterWidgets(): array
    {
        return [];
    }
 
    /**
     *  CHARGEMENT OPTIMISÉ DES DONNÉES PARCOURS
     * Une seule requête pour tout charger
     */
    protected function getParcoursMarin(int $fcmMarinId)
    {
        $startTime = microtime(true);
        
        //  REQUÊTE OPTIMISÉE - TOUS LES CHAMPS NÉCESSAIRES EN UNE FOIS
        $fcmMarin = FcmMarin::select([
            'id',
            'rh_marin_id',
        ])
        ->with([
            'parcoursSerialises:id,libelle_court,uuid',
            //'parcoursSerialises.pivot:fcmmarin_id,parcoursserialise_id,parcoursmarin,taux_global,taux_stage,taux_activite',
            'marin:id,uuid,nom,prenom'
        ])
        ->find($fcmMarinId);
 
        $loadTime = round((microtime(true) - $startTime) * 1000, 2);
        Log::info("Données parcours chargées en: {$loadTime}ms pour marin: {$fcmMarinId}");
 
        if (!$fcmMarin) {
            Log::error("Marin FCM non trouvé: {$fcmMarinId}");
            return null;
        }
 
        //  RETOURNER DONNÉES STRUCTURÉES POUR LIVEWIRE
        return [
            'fcmMarin' => $fcmMarin,
            'parcoursSerialises' => $fcmMarin->parcoursSerialises,
        ];
    }
}
