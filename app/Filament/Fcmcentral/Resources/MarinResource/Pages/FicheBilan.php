<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Collection;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\Note;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
use Livewire\Attributes\Computed;

class FicheBilan extends ViewRecord
{
    protected static string $resource = MarinResource::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'fcmcentral::filament.fcmcentral.resources.marin-resource.pages.fiche-bilan';
    protected static ?string $title = 'Fiche Bilan';

    public ?int $marinId = null;
    // ✅ Supprimer cette ligne - $record est déjà définie dans ViewRecord
    // public $record = null;

    public function mount($record): void
    {
        // ✅ Appeler le mount parent pour initialiser correctement $record
        parent::mount($record);
        
        $this->marinId = is_numeric($record) ? $record : $record->id;
        
        // Vérifier les droits d'accès
        if (!$this->canAccessMarin()) {
            abort(403, 'Accès non autorisé à cette fiche bilan');
        }
    }

    private function canAccessMarin(): bool
    {
        // Utiliser les mêmes contrôles d'accès que MarinResource
        if (MarinResource::canAccessAdminMenus()) {
            return true;
        }
        
        if (MarinResource::canAccessMentorFeatures()) {
            $marin = $this->marin;
            return $marin ? MarinResource::isMentorOfMarin($marin) : false;
        }
        
        return false;
    }

    #[Computed]
    public function marin()
    {
        return Marin::with([
            'grade',
            'specialite',
            'unite',
            'complementsFcm.cohorte',
            'complementsFcm.mentor'
        ])->find($this->marinId);
    }

    #[Computed]
    public function parcours()
    {
        if (!$this->marin->complementsFcm?->donnees_du_parcours?->parcoursmarin) {
            return null;
        }
        
        return $this->marin->complementsFcm->donnees_du_parcours->parcoursmarin;
    }

    #[Computed]
public function savoirFaires()
{
    if (!$this->parcours) {
        return collect([]);
    }

    $savoirFairesArray = [];

    foreach ($this->parcours['fonctions'] ?? [] as $fonction) {
        foreach ($fonction['competences'] ?? [] as $competence) {
            foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                $activites = collect($savoirfaire['activites'] ?? []);
                $totalActivites = $activites->count();
                $activitesValidees = $activites->where('etat_valid', true)->count();
               
                $pourcentage = $totalActivites > 0
                    ? round(($activitesValidees / $totalActivites) * 100, 1)
                    : 0;

                // ✅ DÉTERMINER LA COULEUR SELON LE POURCENTAGE
                $couleurClasse = $this->getCouleurSavoirFaire($pourcentage);

                $savoirFairesArray[] = [
                    'id' => $savoirfaire['id'],
                    'uuid' => $savoirfaire['uuid'] ?? '',
                    'libelle_court' => $savoirfaire['libelle_court'],
                    'libelle_long' => $savoirfaire['libelle_long'] ?? '',
                    'total_activites' => $totalActivites,
                    'activites_validees' => $activitesValidees,
                    'pourcentage' => $pourcentage,
                    'couleur_classe' => $couleurClasse, 
                    'etat_valid' => $savoirfaire['etat_valid'] ?? false,
                    'date_validation' => $savoirfaire['date_validation'] ?? null,
                    'valideur' => $savoirfaire['valideur'] ?? null,
                    'fonction' => $fonction['libelle_court'],
                    'competence' => $competence['libelle_court'],
                    'activites' => $activites->toArray()
                ];
            }
        }
    }

    return collect($savoirFairesArray);
}


private function getCouleurSavoirFaire(float $pourcentage): array
    {
        if ($pourcentage == 100) {
            return [
                'bg' => 'bg-green-100',
                'text' => 'text-green-800',
                'progress' => 'bg-green-500',
                'border' => 'border-green-500'
            ];
        } elseif ($pourcentage >= 80) {
            return [
                'bg' => 'bg-blue-100',
                'text' => 'text-blue-800',
                'progress' => 'bg-blue-500',
                'border' => 'border-blue-500'
            ];
        } elseif ($pourcentage >= 60) {
            return [
                'bg' => 'bg-yellow-100',
                'text' => 'text-yellow-800',
                'progress' => 'bg-yellow-500',
                'border' => 'border-yellow-500'
            ];
        } elseif ($pourcentage >= 40) {
            return [
                'bg' => 'bg-orange-100',
                'text' => 'text-orange-800',
                'progress' => 'bg-orange-500',
                'border' => 'border-orange-500'
            ];
        } elseif ($pourcentage > 0) {
            return [
                'bg' => 'bg-amber-100',
                'text' => 'text-amber-800',
                'progress' => 'bg-amber-500',
                'border' => 'border-amber-500'
            ];
        } else {
            return [
                'bg' => 'bg-red-100',
                'text' => 'text-red-800',
                'progress' => 'bg-red-500',
                'border' => 'border-red-500'
            ];
        }
    }



    #[Computed]
    public function notes()
    {
        return Note::where('marin_id', $this->marinId)
            ->with(['typeNote', 'redacteur'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /// LIVEWIRE NOTE ///

protected $listeners = [
    'note-added' => 'refreshNotes',
    'notify' => 'handleNotification'
];

public function refreshNotes()
{
    // Forcer le recalcul des notes
   // unset($this->cachedMountedProperties['notes']);
    $this->dispatch('$refresh');
}

public function handleNotification($data)
{
    if ($data['type'] === 'success') {
        \Filament\Notifications\Notification::make()
            ->title($data['message'])
            ->success()
            ->send();
    } elseif ($data['type'] === 'error') {
        \Filament\Notifications\Notification::make()
            ->title($data['message'])
            ->danger()
            ->send();
    }
}

public function ajouterNote()
{
    // Vérifier les permissions
    if (!auth()->check()) {
        \Filament\Notifications\Notification::make()
            ->title('Vous devez être connecté pour ajouter une note')
            ->warning()
            ->send();
        return;
    }

    $this->dispatch('open-modal', id: 'ajouterNote');
}



    #[Computed]
    public function statistiques()
    {
        $savoirFaires = $this->savoirFaires;
    
        // ✅ CALCULER SEULEMENT LES STATS SAVOIR-FAIRE (pas les activités)
        $savoirFairesValides = $savoirFaires->where('etat_valid', true)->count();
        $totalSavoirFaires = $savoirFaires->count();
    
        return [
            'total_savoirfaires' => $totalSavoirFaires,
            'savoirfaires_valides' => $savoirFairesValides,
            'pourcentage_global' => $totalSavoirFaires > 0
                ? round(($savoirFairesValides / $totalSavoirFaires) * 100, 1)
                : 0,
            'savoirfaires_complets' => $savoirFaires->where('pourcentage', 100)->count(),
            'savoirfaires_en_cours' => $savoirFaires->where('pourcentage', '>', 0)->where('pourcentage', '<', 100)->count(),
            'savoirfaires_non_commences' => $savoirFaires->where('pourcentage', 0)->count(),
        
            // ✅ GARDER AUSSI LES STATS ACTIVITÉS POUR L'AFFICHAGE DÉTAILLÉ
            'total_activites' => $savoirFaires->sum('total_activites'),
            'activites_validees' => $savoirFaires->sum('activites_validees'),
        ];
    }



    #[Computed]
    public function activitesEnAttente()
    {
        if (!$this->parcours) {
            return collect([]);
        }

        $activitesEnAttente = [];

        foreach ($this->parcours['fonctions'] ?? [] as $fonction) {
            foreach ($fonction['competences'] ?? [] as $competence) {
                foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                    foreach ($savoirfaire['activites'] ?? [] as $activite) {
                        $estProposee = !empty($activite['date_proposition']);
                        $estValidee = ($activite['etat_valid'] ?? false) === true;
                        $aDateValidation = !empty($activite['date_validation']);
                        
                        if ($estProposee && !$estValidee && !$aDateValidation) {
                            $activitesEnAttente[] = [
                                'activite' => $activite,
                                'savoirfaire' => $savoirfaire['libelle_court'],
                                'competence' => $competence['libelle_court'],
                                'fonction' => $fonction['libelle_court']
                            ];
                        }
                    }
                }
            }
        }

        return collect($activitesEnAttente);
    }

    public function getTitle(): string
    {
        if ($this->marin) {
            return "Fiche Bilan FCM - {$this->marin->grade?->libelle_court} {$this->marin->prenom} {$this->marin->nom}";
        }
        
        return 'Fiche Bilan FCM';
    }

    // ✅ MÉTHODES DE CALCUL DES DURÉES
private function calculerDureeSedimentation(): int
{
    $debut = $this->marin->debut_sedimentation;
    $vert = $this->marin->vert_sedimentation;
    
    if ($debut && $vert) {
        return \Carbon\Carbon::parse($debut)->diffInDays(\Carbon\Carbon::parse($vert));
    }
    
    return 0;
}

private function calculerDureePPA(): int
{
    $vert = $this->marin->vert_sedimentation;
    
    if ($vert) {
        return \Carbon\Carbon::parse($vert)->diffInDays(now());
    }
    
    return 0;
}

private function calculerDureeTotale(): int
{
    $debut = $this->marin->debut_sedimentation;
    
    if ($debut) {
        return \Carbon\Carbon::parse($debut)->diffInDays(now());
    }
    
    return 0;
}

// ✅ MÉTHODES POUR LES INDICATEURS
private function getDateDerniereMajFfast(): string
{
    // Récupérer la date de dernière validation
    $derniereValidation = $this->getDerniereValidation();
    return $derniereValidation ? $derniereValidation->format('d/m/Y') : 'N/A';
}

private function getPourcentageCompagnonnage(): float
{
    // À implémenter selon votre logique métier
    return 0;
}

private function getPourcentageStages(): float
{
    // À implémenter selon votre logique métier
    return 0;
}

private function getDerniereValidation()
{
    // Parcourir le parcours pour trouver la dernière date de validation
    if (!$this->parcours) {
        return null;
    }

    $derniereDate = null;

    foreach ($this->parcours['fonctions'] ?? [] as $fonction) {
        foreach ($fonction['competences'] ?? [] as $competence) {
            foreach ($competence['savoirfaires'] ?? [] as $savoirfaire) {
                if (!empty($savoirfaire['date_validation'])) {
                    $date = \Carbon\Carbon::parse($savoirfaire['date_validation']);
                    if (!$derniereDate || $date->gt($derniereDate)) {
                        $derniereDate = $date;
                    }
                }
                
                foreach ($savoirfaire['activites'] ?? [] as $activite) {
                    if (!empty($activite['date_validation'])) {
                        $date = \Carbon\Carbon::parse($activite['date_validation']);
                        if (!$derniereDate || $date->gt($derniereDate)) {
                            $derniereDate = $date;
                        }
                    }
                }
            }
        }
    }

    return $derniereDate;
}






}



