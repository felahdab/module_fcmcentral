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

                    $savoirFairesArray[] = [
                        'id' => $savoirfaire['id'],
                        'uuid' => $savoirfaire['uuid'] ?? '',
                        'libelle_court' => $savoirfaire['libelle_court'],
                        'libelle_long' => $savoirfaire['libelle_long'] ?? '',
                        'total_activites' => $totalActivites,
                        'activites_validees' => $activitesValidees,
                        'pourcentage' => $pourcentage,
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

    #[Computed]
    public function notes()
    {
        return Note::where('marin_id', $this->marinId)
            ->with(['typeNote', 'redacteur'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    #[Computed]
    public function statistiques()
    {
        $savoirFaires = $this->savoirFaires;
        
        return [
            'total_savoirfaires' => $savoirFaires->count(),
            'savoirfaires_valides' => $savoirFaires->where('etat_valid', true)->count(),
            'total_activites' => $savoirFaires->sum('total_activites'),
            'activites_validees' => $savoirFaires->sum('activites_validees'),
            'pourcentage_global' => $savoirFaires->count() > 0
                ? round($savoirFaires->avg('pourcentage'), 1)
                : 0,
            'savoirfaires_complets' => $savoirFaires->where('pourcentage', 100)->count(),
            'savoirfaires_en_cours' => $savoirFaires->where('pourcentage', '>', 0)->where('pourcentage', '<', 100)->count(),
            'savoirfaires_non_commences' => $savoirFaires->where('pourcentage', 0)->count(),
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
}
