<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Notifications\Notification;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\Note;
use Modules\FcmCentral\Models\TypeNote;
use Modules\FcmCentral\Models\Etat;
use Modules\FcmCentral\Models\TypeEtat;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
use Livewire\Attributes\Computed;
use Modules\FcmCommun\Services\CalculHeuresParcoursService;

class FicheBilan extends ViewRecord
{
    protected static string $resource = MarinResource::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'fcmcentral::filament.fcmcentral.resources.marin-resource.pages.fiche-bilan';
    protected static ?string $title = 'Fiche Bilan';

    public ?int $marinId = null;

    // ============================================
    // 🔧 LIFECYCLE METHODS
    // ============================================

    public function mount($record): void
    {
        parent::mount($record);
        
        $this->marinId = is_numeric($record) ? $record : $record->id;
        
        if (!$this->canAccessMarin()) {
            abort(403, 'Accès non autorisé à cette fiche bilan');
        }
    }

    // ============================================
    // 🔐 CONTRÔLES D'ACCÈS
    // ============================================

    private function canAccessMarin(): bool
    {
        // Admin a tous les droits
        if (MarinResource::canAccessAnyPermission(['admin'])) {
            return true;
        }
        
        // Mentor du marin
        if (MarinResource::canAccessAnyPermission(['permission.mentor'])) {
            $marin = $this->marin;
            return $marin ? MarinResource::isMentorOfMarin($marin) : false;
        }
        
        // BCF / FPS / Bord peuvent voir les fiches
        if (MarinResource::canAccessAnyPermission(['permission.bcf', 'permission.fps', 'permission.bord'])) {
            return true;
        }
        
        return false;
    }

    public function canCreateNote(): bool
    {
        // ✅ SEULS BCF, BORD, FPS et ADMIN peuvent créer/voir les notes
        return MarinResource::canAccessAnyPermission([
            'admin',
            'permission.bcf',
            'permission.fps',
            'permission.bord'
        ]);
    }

    public function canCreateEtat(): bool
    {
        // ✅ SEULS BCF, BORD, FPS et ADMIN peuvent créer/modifier les etats
        return MarinResource::canAccessAnyPermission([
            'admin',
            'permission.bcf',
            'permission.fps',
            'permission.bord'
        ]);
    }

    private function canValidateBCF(): bool
    {
        return MarinResource::canAccessAnyPermission([
            'admin',
            'permission.bcf'
        ]);
    }

    // ============================================
    // 📊 COMPUTED PROPERTIES
    // ============================================

    #[Computed]
    public function marin()
    {
        return Marin::with([
            'grade',
            'specialite',
            'unite',
            'complementsFcm.cohorte',
            'complementsFcm.mentor',
            'etats.typeEtat' 
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

        // ✅ STRUCTURE CORRIGÉE : Affectation > Compétences > CompetenceSavoirfaires > Objectifs
        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    $objectifs = collect($csf['objectifs'] ?? []);
                    $totalObjectifs = $objectifs->count();
                    $objectifsValides = $objectifs->where('etat_valid', true)->count();
                   //dd($csf);
                    // Récupère le type de savoir-faire
                    $typeCode = $csf['savoirfaire_type']['code'] ?? 'C';
                
                    if ($typeCode === 'C') {
                        $pourcentage = $totalObjectifs > 0
                            ? round(($objectifsValides / $totalObjectifs) * 100, 0)
                            : 0;
                    } elseif ($typeCode === 'S' || $typeCode === 'EAD') {
                        $pourcentage = ($csf['etat_valid'] ?? false) ? 100 : 0;
                    } else {
                        $pourcentage = 0;
                    }
                
                    $couleurClasse = $this->getCouleurSavoirFaire($pourcentage, $csf['etat_valid'] ?? false);
                
                    $savoirFairesArray[] = [
                        'id' => $csf['savoirfaire']['id'] ?? $csf['id'],
                        'uuid' => $csf['savoirfaire']['uuid'] ?? $csf['uuid'] ?? '',
                        'libelle_court' => $csf['savoirfaire']['libelle_court'] ?? $csf['libelle_court'] ?? '',
                        'libelle_long' => $csf['savoirfaire']['libelle_long'] ?? '',
                        'total_objectifs' => $totalObjectifs,
                        'objectifs_valides' => $objectifsValides,
                        'pourcentage' => $pourcentage,
                        'couleur_classe' => $couleurClasse,
                        'etat_valid' => $csf['etat_valid'] ?? false,
                        'date_validation' => $csf['date_validation'] ?? null,
                        'commentaire' => $csf['commentaire'] ?? null,
                        'valideur' => $csf['valideur'] ?? null,
                        'affectation' => $affectation['libelle_court'] ?? '',
                        'competence' => $competence['libelle_court'] ?? '',
                        'savoirfaire_type' => $csf['savoirfaire_type']['code'],
                        'savoirfaire_type_libelle' => $csf['savoirfaire_type']['libelle_court'],
                        'intitule' => $csf['libelle_court'],
                        'objectifs' => $objectifs->toArray()
                    ];
                }
                
            }
        }

        return collect($savoirFairesArray);
    }

    private function getCouleurSavoirFaire(float $pourcentage, bool $etatValid = false): array
    {
        if ($etatValid && $pourcentage == 100) {
            // Validé
            return [
                'bg' => 'bg-green-100 dark:bg-green-900',
                'text' => 'text-green-800 dark:text-green-200',
                'progress' => 'bg-green-500 dark:bg-green-400',
                'border' => 'border-green-500 dark:border-green-400'
            ];
        } elseif ($pourcentage > 0 && $pourcentage < 100) {
            // En cours
            return [
                'bg' => 'bg-blue-100 dark:bg-blue-900',
                'text' => 'text-blue-800 dark:text-blue-200',
                'progress' => 'bg-blue-500 dark:bg-blue-400',
                'border' => 'border-blue-500 dark:border-blue-400'
            ];
        } else {
            // Non commencé
            return [
                'bg' => 'bg-gray-100 dark:bg-gray-800',
                'text' => 'text-gray-800 dark:text-gray-200',
                'progress' => 'bg-gray-500 dark:bg-gray-400',
                'border' => 'border-gray-500 dark:border-gray-400'
            ];
        }
    }
    
    

    #[Computed]
    public function notes()
    {
        // ✅ SEULS BCF/FPS/BORD/ADMIN voient les notes
        if (!$this->canCreateNote()) {
            return collect([]);
        }

        $query = Note::where('rh_marin_id', $this->marinId)
            ->with(['typeNote', 'redacteur'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc');

        // ✅ FILTRAGE SELON LES PERMISSIONS
        if (!$this->canValidateBCF()) {
            // Non-BCF ne voient que les notes validées
            $query->where('valide_bcf', true);
        }

        return $query->get();
    }

    
    public function ajouterEtat()
    {
        if (!$this->canCreateEtat()) {
            Notification::make()
                ->title('Accès refusé')
                ->body('Vous n\'avez pas les droits pour créer un état')
                ->warning()
                ->send();
            return;
        }
    
        $this->dispatch('open-modal', id: 'ajouterEtat');
    }


    public function modifierEtat($etatId)
{
    if (!$this->canCreateEtat()) {
        Notification::make()
            ->title('Accès refusé')
            ->body('Vous n\'avez pas les droits pour modifier un état')
            ->warning()
            ->send();
        return;
    }
    $this->dispatch('loadEtatForEdit',  etatId: $etatId);
    $this->dispatch('open-modal', id: 'ajouterEtat');
}

    

    #[Computed]
public function statistiques()
{
    $savoirFaires = $this->savoirFaires;
    //dd( $savoirFaires);
   
    $savoirFairesValides = $savoirFaires->where('etat_valid', true)->count();
    $totalSavoirFaires = $savoirFaires->count();
   
    // ✅ CALCUL PAR TYPE (S/C/EAD)
    $stages = $this->getStatsByType('S');
    $compagnonnage = $this->getStatsByType('C');
    $ead = $this->getStatsByType('EAD');

    $totalEtatValidParcours = new CalculHeuresParcoursService();
    $pourcentageGlobal = $this->parcours ?  $totalEtatValidParcours->calculerPourcentageEtatValid($this->parcours) : 0 ; 
   
    return [
        'total_savoirfaires' => $totalSavoirFaires,
        'savoirfaires_valides' => $savoirFairesValides,
        'pourcentage_global' => $pourcentageGlobal,
        'savoirfaires_complets' => $savoirFaires->where('pourcentage', 100)->count(),
        'savoirfaires_en_cours' => $savoirFaires->where('pourcentage', '>', 0)->where('pourcentage', '<', 100)->count(),
        'savoirfaires_non_commences' => $savoirFaires->where('pourcentage', 0)->count(),
       
        'total_objectifs' => $savoirFaires->sum('total_objectifs'),
        'objectifs_valides' => $savoirFaires->sum('objectifs_valides'),
       
        // ✅ STATS PAR TYPE
        'total_stages' => $stages['total'],
        'stages_valides' => $stages['valides'],
        'total_compagnonnage' => $compagnonnage['total'],
        'compagnonnage_valides' => $compagnonnage['valides'],
        'total_ead' => $ead['total'],
        'ead_valides' => $ead['valides'],
    ];
}

private function getStatsByType(string $typeCode): array
{
    if (!$this->parcours) {
        return ['total' => 0, 'valides' => 0];
    }

    $total = 0;
    $valides = 0;

    foreach ($this->parcours['affectations'] ?? [] as $affectation) {
        foreach ($affectation['competences'] ?? [] as $competence) {
            foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                if (($csf['savoirfaire_type']['code'] ?? null) === $typeCode) {
                    $total++;
                    if ($csf['etat_valid'] ?? false) {
                        $valides++;
                    }
                }
            }
        }
    }

    return ['total' => $total, 'valides' => $valides];
}

public function hasMultipleNonZeroStats(): bool
{
    $nonZeroCount = 0;
   
    if ($this->statistiques['savoirfaires_valides'] > 0) $nonZeroCount++;
    if ($this->statistiques['savoirfaires_en_cours'] > 0) $nonZeroCount++;
    if ($this->statistiques['savoirfaires_non_commences'] > 0) $nonZeroCount++;
   
    return $nonZeroCount >= 2;
}


    #[Computed]
    public function activitesEnAttente()
    {
        if (!$this->parcours) {
            return collect([]);
        }

        $objectifsEnAttente = [];

        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    foreach ($csf['objectifs'] ?? [] as $objectif) {
                        $estPropose = !empty($objectif['date_proposition']);
                        $estValide = ($objectif['etat_valid'] ?? false) === true;
                        
                        if ($estPropose && !$estValide) {
                            $objectifsEnAttente[] = [
                                'objectif' => $objectif,
                                'savoirfaire' => $csf['savoirfaire']['libelle_court'] ?? '',
                                'competence' => $competence['libelle_court'] ?? '',
                                'affectation' => $affectation['libelle_court'] ?? ''
                            ];
                        }
                    }
                }
            }
        }

        return collect($objectifsEnAttente);
    }

    // ============================================
    // 🎬 ACTION PUBLIQUE POUR LE BOUTON
    // ============================================

    public function ajouterNote()
    {
        if (!$this->canCreateNote()) {
            Notification::make()
                ->title('Accès refusé')
                ->body('Vous n\'avez pas les droits pour créer une note')
                ->warning()
                ->send();
            return;
        }

        $this->dispatch('open-modal', id: 'ajouterNote');
    }

    // ============================================
// 🏷️ MÉTHODES POUR LA SECTION ÉTATS
// ============================================

#[Computed]
public function etatActuel()
{
    return $this->marin->getDernierTypeEtat();
}

public function getStatutLibelle(): string
{
    return $this->marin->getDernierTypeEtatLibelle() ?? 'Non défini';
}

public function getStatutColor(): string
{
    $code = $this->marin->getDernierTypeEtatCode();
   
    return match($code) {
        'FCM_ACT' => 'text-green-600',
        'FCM_SED' => 'text-blue-600',
        'FCM_PAU' => 'text-orange-600',
        'FCM_FIN' => 'text-purple-600',
        default => 'text-gray-600'
    };
}

public function getStatutBgColor(): string
{
    $code = $this->marin->getDernierTypeEtatCode();
   
    return match($code) {
        'FCM_ACT' => 'bg-green-50 border-green-200',
        'FCM_SED' => 'bg-blue-50 border-blue-200',
        'FCM_PAU' => 'bg-orange-50 border-orange-200',
        'FCM_FIN' => 'bg-purple-50 border-purple-200',
        default => 'bg-gray-50 border-gray-200'
    };
}

public function getDureeEtat(): int
{
    $etat = $this->etatActuel;
   
    if (!$etat || !$etat->date_debut) {
        return 0;
    }
   
    $dateDebut = \Carbon\Carbon::parse($etat->date_debut);
    $dateFin = $etat->date_fin ? \Carbon\Carbon::parse($etat->date_fin) : now();
   
    return $dateDebut->diffInDays($dateFin);
}

public function getDateDebutEtat(): ?string
{
    $etat = $this->etatActuel;
   
    if (!$etat || !$etat->date_debut) {
        return null;
    }
   
    return \Carbon\Carbon::parse($etat->date_debut)->format('d/m/Y');
}

public function getDateFinEtat(): ?string
{
    $etat = $this->etatActuel;
   
    if (!$etat || !$etat->date_fin) {
        return null;
    }
   
    return \Carbon\Carbon::parse($etat->date_fin)->format('d/m/Y');
}

#[Computed]
public function historiqueEtats()
{
    return $this->marin->etats()
        ->with('typeEtat')
        ->orderBy('date_debut', 'desc')
        ->get();
}


 

    // ============================================
    // 🔄 LIVEWIRE LISTENERS
    // ============================================

    protected $listeners = [
        'note-added' => 'refreshNotes',
        'etat-added' => 'refreshEtats',
        'notify' => 'handleNotification'
    ];

    public function refreshNotes()
    {
        //unset($this->cachedMountedProperties['notes']);
        $this->dispatch('$refresh');
    }

    public function refreshEtats()
    {
        //unset($this->cachedMountedProperties['notes']);
        $this->dispatch('$refresh');
    }

    public function handleNotification($data)
    {
        Notification::make()
            ->title($data['message'])
            ->{$data['type'] === 'success' ? 'success' : 'danger'}()
            ->send();
    }

    // ============================================
    // 📏 CALCULS DE DURÉES
    // ============================================

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

    // ============================================
    // 📊 INDICATEURS COMPLÉMENTAIRES
    // ============================================

    public function getDateDerniereMajFfast(): string
    {
        $derniereValidation = $this->getDerniereValidation();
        return $derniereValidation ? $derniereValidation->format('d/m/Y') : 'N/A';
    }

    public function getPourcentageCompagnonnage(): float
    {
        if (!$this->parcours) {
            return 0;
        }

        // ✅ CALCUL BASÉ SUR savoirfaire_type.code = "C" (Compagnonnage)
        $totalCompagnonnage = 0;
        $compagnonnageValide = 0;

        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    // ✅ Vérifier si c'est du compagnonnage
                    if (($csf['savoirfaire_type']['code'] ?? null) === 'C') {
                        $totalCompagnonnage++;
                        
                        if ($csf['etat_valid'] ?? false) {
                            $compagnonnageValide++;
                        }
                    }
                }
            }
        }

        return $totalCompagnonnage > 0 
            ? round(($compagnonnageValide / $totalCompagnonnage) * 100, 1)
            : 0;
    }

    public function getPourcentageStages(): float
    {
        if (!$this->parcours) {
            return 0;
        }

        // ✅ CALCUL BASÉ SUR savoirfaire_type.code = "S" (Stage)
        $totalStages = 0;
        $stagesValides = 0;

        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    // ✅ Vérifier si c'est un stage
                    if (($csf['savoirfaire_type']['code'] ?? null) === 'S') {
                        $totalStages++;
                        
                        if ($csf['etat_valid'] ?? false) {
                            $stagesValides++;
                        }
                    }
                }
            }
        }

        return $totalStages > 0 
            ? round(($stagesValides / $totalStages) * 100, 1)
            : 0;
    }

    public function getPourcentageEad(): float
    {
        if (!$this->parcours) {
            return 0;
        }

        // ✅ CALCUL BASÉ SUR savoirfaire_type.code = "S" (Stage)
        $totalStages = 0;
        $stagesValides = 0;

        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    // ✅ Vérifier si c'est un stage
                    if (($csf['savoirfaire_type']['code'] ?? null) === 'EAD') {
                        $totalStages++;
                        
                        if ($csf['etat_valid'] ?? false) {
                            $stagesValides++;
                        }
                    }
                }
            }
        }

        return $totalStages > 0 
            ? round(($stagesValides / $totalStages) * 100, 1)
            : 0;
    }

    private function getDerniereValidation()
    {
        if (!$this->parcours) {
            return null;
        }

        $derniereDate = null;

        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    if (!empty($csf['date_validation'])) {
                        $date = \Carbon\Carbon::parse($csf['date_validation']);
                        if (!$derniereDate || $date->gt($derniereDate)) {
                            $derniereDate = $date;
                        }
                    }
                    
                    foreach ($csf['objectifs'] ?? [] as $objectif) {
                        if (!empty($objectif['date_validation'])) {
                            $date = \Carbon\Carbon::parse($objectif['date_validation']);
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

    public function getTitle(): string
    {
        if ($this->marin) {
            return "Fiche Bilan FCM - {$this->marin->grade?->libelle_court} {$this->marin->prenom} {$this->marin->nom}";
        }
        
        return 'Fiche Bilan FCM';
    }
}
