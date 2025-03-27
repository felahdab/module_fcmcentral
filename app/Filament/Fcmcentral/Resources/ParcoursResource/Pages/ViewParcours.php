<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource\Pages;

use Modules\FcmCentral\Filament\Fcmcentral\Resources\ParcoursResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Modules\FcmCentral\Models\Parcours;
use Livewire\Component;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Illuminate\Support\Str;

class ViewParcours extends ViewRecord
{
    protected static string $resource = ParcoursResource::class;
    
    // Vue personnalisée
    protected static string  $view = 'fcmcentral::filament.fcmcentral.resources.parcours-resource.pages.view-parcours';

    public $parcours;

    public function mount($record): void
    {
         parent::mount($record);

        // Charger le parcours complet avec ses relations
         $this->parcours = $this->getFullParcours($record);
 }

     /**
      * Récupere le parcours entier avec ses relations en fonction ID
      * @param int $id
      * @return Parcours|null
      */
     public function getFullParcours(int $id): ?Parcours
     {
         $parcours = Parcours::with([
             'fonctions.competences.savoirFaires.activites',
         ])->find($id);

         
    
    return $parcours;
     }

     /**
      *  Function pour Modifier 
      */
     public function editAction(): Action
     {
         return Action::make('edit')
        
             ->label('Modifier')
             ->modalHeading('Modifier ')
             ->modalWidth('lg')
             ->link()
             
             // Cree le form en fonction du type argument
             ->form(
                function(array $arguments){  
                    $model = $this->getModelByType($arguments['type']);
                    $record= $model::find($arguments['recordId']);

                    return $record 
                    ? collect($this->getFieldsByType($arguments['type']))->map(fn ($field) => $field->default($record->{$field->getName()}))->toArray() // je le transforme en collection car j'arrive pas a le charger en Array les data
                    : $this->getFieldsByType($arguments['type']);

                }) // Récupérer les champs dynamiquement
             // Action
             ->action(function (array $data, array $arguments): void {
                 // Récupérer le modèle en fonction du type
                 $model = $this->getModelByType($arguments['type']);
                 $record = $model::find($arguments['recordId']);
              
                 if ($record) {
                     $record->update($data); // Mettre à jour les champs
                     Notification::make()
                        ->title('Mise a jour OK.')
                        ->success()
                        ->body('Ok: ' . $record->libelleCourt)
                        ->send();
                 }
             });
     }
     
        /**
      *  Function pour Creer
      */
     public function createAction(): Action
     {
         return Action::make('create')
             ->label('Ajouter')
             ->modalHeading('Créer un nouvel élément')
             ->modalWidth('lg')
             ->link()
             ->form(fn (array $arguments) => $this->getFieldsByType($arguments['type'])) // Formulaire dynamique
             ->action(function (array $data, array $arguments): void {
                 $model = $this->getModelByType($arguments['type']);
                 // Recupere le Model Parent
                 $parentModel = $this->getModelByType($arguments['parentType']);

                 
                 // Trouve entite parent
                 $parent =  $parentModel::find($arguments['parentId']);
     
                     $record = $model::create($data); // Créer l'enregistrement
                     if ($parent && $model){
                        // Recupere dynamiquement la relation ManyToMany
                        $relationMethod = $this->getRelationMethod($arguments['type']);

                        // dd([
                        //     'parent_class'=>get_class($parent),
                        //     'relation_method'=>$relationMethod,
                        //     'available_methods'=>get_class_methods($parent),
                        // ]);
                        if (method_exists($parent,$relationMethod)){
                            $parent->$relationMethod()->attach($record->id);
                        }


                     }
     
                     Notification::make()
                        ->title('Creation Ok.')
                        ->success()
                        ->send();
                    
                 
             });
     }
     
     
     public function attachAction(): Action
     {
         return Action::make('attach')
             ->label('Associer')
             ->modalHeading('Associer un élément existant')
             ->modalWidth('lg')
             ->link()
             ->form(fn (array $arguments) => [
                 Select::make('recordId')
                     ->label('Sélectionner un élément')
                     ->options($this->getModelByType($arguments['parentType'])::pluck('libelle_long', 'id')) // Récupérer les options
                     ->searchable()
                     ->required(),
             ])
             ->action(function (array $data, array $arguments): void {
                 $parentModel = $this->getModelByType($arguments['parentType']);
                 $childModel = $this->getModelByType($arguments['type']);
     
                 $parent = $parentModel::find($arguments['parentId']);
                 $child = $childModel::find($data['recordId']);
     
                 if ($parent && $child) {
                     $parent->relatedModels()->attach($child); // Remplace `relatedModels()` par ta relation
     
                     Notification::make()
                     ->title('Attachement Ok.')
                     ->success()
                     ->send();
                 }
             });
     }
     

    /**
     *  Creation du Formulaire en fonction du Type
     * 
     */
    public function getFieldsByType(string $type): array
{
    return match ($type) {
        'fonction' => [
            TextInput::make('libelle_long')
                ->label('Libellé Long de la Fonction')
                ->live()
                ->required(),
                TextInput::make('libelle_court')
                ->label('Libellé Court de la Fonction')
                ->required(),
                TextInput::make('lien_url')
                ->label('Lien de la Fonction')
                
        ],
        'competence' => [
            TextInput::make('libelle_long')
                ->label('Libellé de la Compétence')
                ->required(),
                TextInput::make('libelle_court')
                ->label('Libellé Court Competence')
                ->required(),
        ],
        'savoirFaire' => [
            TextInput::make('libelle_long')
                ->label('Libellé du Savoir-Faire')
                ->required(),
                TextInput::make('libelle_court')
                ->label('Libellé Court Savoir Faire')
                ->required(),
        ],
        'activite' => [
            TextInput::make('libelle_long')
                ->label('Libellé de l\'Activité')
                ->required(),
                TextInput::make('libelle_court')
                ->label('Libellé Court Activite')
                ->required(),
        ],
        default => [],
    };
}

private function getModelByType(string $type)
{
    return match ($type) {
        'fonction' => \Modules\FcmCentral\Models\Fonction::class,
        'competence' => \Modules\FcmCentral\Models\Competence::class,
        'savoirFaire' => \Modules\FcmCentral\Models\SavoirFaire::class,
        'activite' => \Modules\FcmCentral\Models\Activite::class,
        'parcours' => \Modules\FcmCentral\Models\Parcours::class,
        default => null,
    };
}

private function getRelationMethod(string $type)
{
    return match ($type) {
        'fonction' => 'fonctions',
        'competence' => 'competences',
        'savoirFaire' => 'savoirfaires',
        'activite' => 'activites',
        'parcours' => 'parcours',
        default => null,
    };
}


   

}
