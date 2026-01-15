<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Notifications\Notification;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Models\FcmMarin;
use Modules\FcmCentral\Models\Note;
use Modules\FcmCentral\Models\TypeNote;
use Modules\FcmCentral\Models\Etat;
use Modules\FcmCentral\Models\TypeEtat;
use Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource;
use Livewire\Attributes\Computed;
use Modules\FcmCommun\Services\CalculHeuresParcoursService;
use Modules\FcmCommun\DataObjects\Enums\UserGeneratedEventTypes;
use Modules\FcmCentral\Events\UserGeneratedEvent;
use Modules\FcmCommun\DataObjects\Enums\ObjectClasses;

class FicheBilan extends ViewRecord
{
    protected static string $resource = MarinResource::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'fcmcentral::filament.fcmcentral.resources.marin-resource.pages.fiche-bilan';
    protected static ?string $title = 'Fiche Bilan';

    public ?int $marinId = null;

    public $showJalonModal = false;
    public $jalonForm = [
        'date_sedimentation' => null,
        'date_fcm' => null,
        'date_competence' => null,
    ];
  
    protected $listeners = [
        'note-added' => 'refreshNotes',
        'etat-added' => 'refreshEtats',
        'notify' => 'handleNotification',
        'openJalonModal' => 'ouvrirJalonModal'
    ];

    // ============================================
    // 🔧 LIFECYCLE METHODS
    // ============================================

    public function mount($record): void
    {
        parent::mount($record);
        $this->marinId = is_numeric($record) ? $record : $record->id;
    }

    // ============================================
    // 🔐 CONTRÔLES D'ACCÈS
    // ============================================

    public static function canAccess(array $parameters = []): bool
    {
        if (! array_key_exists("record", $parameters)) return false;

        $marinId = $parameters["record"]->id;

        $marin_a_voir = Marin::find($marinId);
        if (! $marin_a_voir)
            return false;

        if (! auth()->user()->can("fcmcentral::ficheBilan.voir")){
            return false;
        }
        
        if ( auth()->user()->can("fcmcentral::voir_tous_les _marins") )
            return true;

        $currentMarin = Marin::fromCurrentUser();

        if ($currentMarin->id == $marin_a_voir->id) return true; // On autorise le marin à voir sa propre fiche Bilan.
    
        // Vérifier si c'est le mentor de CE marin
        $isMentor = FcmMarin::where('mentor_id', $currentMarin->id)
            ->where('rh_marin_id', $marinId)
            ->exists();
   
        return $isMentor;
    }

    private function canAccessMarin(): bool
    {
        try {
            // Admin a tous les droits
            if (MarinResource::canAccessAnyPermission(['admin'])) {
                return true;
            }
        
            // BCF / FPS peuvent voir toutes les fiches
            if (MarinResource::canAccessAnyPermission(['permission.bcf', 'permission.fps'])) {
                return true;
            }
        
            // Bord peut voir les fiches
            if (MarinResource::canAccessAnyPermission(['permission.bord'])) {
                return true;
            }
        
            // Mentor du marin
            if (MarinResource::canAccessAnyPermission(['permission.mentor'])) {
                $currentMarin = Marin::fromCurrentUser();
            
                if (!$currentMarin) {
                    \Log::warning('Mentor sans compte marin', [
                        'user_id' => auth()->id(),
                    ]);
                    return false;
                }
            
                // Vérifier si c'est le mentor de CE marin
                $isMentor = FcmMarin::where('mentor_id', $currentMarin->id)
                    ->where('rh_marin_id', $this->marinId)
                    ->exists();
            
                if (!$isMentor) {
                    \Log::warning('Mentor tentant d\'accéder à un marin qui n\'est pas le sien', [
                        'mentor_id' => $currentMarin->id,
                        'marin_id' => $this->marinId,
                    ]);
                }
            
                return $isMentor;
            }
        
            \Log::warning('Accès refusé - Aucune permission valide', [
                'user_id' => auth()->id(),
                'marin_id' => $this->marinId,
            ]);
        
            return false;
        
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la vérification d\'accès', [
                'message' => $e->getMessage(),
                'marin_id' => $this->marinId,
                'user_id' => auth()->id(),
            ]);
            return false;
        }
    }


    public function canCreateNote(): bool
    {
        return auth()->user()->can("fcmcentral::note.create");
    }

    public function canCreateEtat(): bool
    {
        return auth()->user()->can("fcmcentral::etat.create");
    }

    private function canValidateBCF(): bool
    {
        return auth()->user()->can("fcmcentral::note.valider");
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
    
    $savoirFairesValides = $savoirFaires->where('etat_valid', true)->count();
    $totalSavoirFaires = $savoirFaires->count();
    
    // ✅ CALCUL PAR TYPE (S/C/EAD) - SAVOIR-FAIRE
    $stages = $this->getStatsByType('S');
    $compagnonnage = $this->getStatsByType('C');
    $ead = $this->getStatsByType('EAD');

    // ✅ CALCUL PAR TYPE (S/C/EAD) - COMPÉTENCES
    $competencesStats = $this->getCompetencesStatsByType();

    $totalEtatValidParcours = new CalculHeuresParcoursService();
    $pourcentageGlobal = $this->parcours ? $totalEtatValidParcours->calculerPourcentageEtatValid($this->parcours) : 0;
    
    return [
        'total_savoirfaires' => $totalSavoirFaires,
        'savoirfaires_valides' => $savoirFairesValides,
        'pourcentage_global' => $pourcentageGlobal,
        'savoirfaires_complets' => $savoirFaires->where('pourcentage', 100)->count(),
        'savoirfaires_en_cours' => $savoirFaires->where('pourcentage', '>', 0)->where('pourcentage', '<', 100)->count(),
        'savoirfaires_non_commences' => $savoirFaires->where('pourcentage', 0)->count(),
        
        'total_objectifs' => $savoirFaires->sum('total_objectifs'),
        'objectifs_valides' => $savoirFaires->sum('objectifs_valides'),
        
        // ✅ STATS PAR TYPE - SAVOIR-FAIRE
        'total_stages' => $stages['total'],
        'stages_valides' => $stages['valides'],
        'stages_en_cours' => $stages['en_cours'],
        'stages_non_valides' => $stages['non_valides'],
        
        'total_compagnonnage' => $compagnonnage['total'],
        'compagnonnage_valides' => $compagnonnage['valides'],
        'compagnonnage_en_cours' => $compagnonnage['en_cours'],
        'compagnonnage_non_valides' => $compagnonnage['non_valides'],
        
        'total_ead' => $ead['total'],
        'ead_valides' => $ead['valides'],
        'ead_en_cours' => $ead['en_cours'],
        'ead_non_valides' => $ead['non_valides'],

        // ✅ STATS PAR TYPE - COMPÉTENCES
        'competences_stages_total' => $competencesStats['S']['total'],
        'competences_stages_valides' => $competencesStats['S']['valides'],
        'competences_stages_en_cours' => $competencesStats['S']['en_cours'],
        'competences_stages_non_valides' => $competencesStats['S']['non_valides'],
        
        'competences_compagnonnage_total' => $competencesStats['C']['total'],
        'competences_compagnonnage_valides' => $competencesStats['C']['valides'],
        'competences_compagnonnage_en_cours' => $competencesStats['C']['en_cours'],
        'competences_compagnonnage_non_valides' => $competencesStats['C']['non_valides'],
        
        'competences_ead_total' => $competencesStats['EAD']['total'],
        'competences_ead_valides' => $competencesStats['EAD']['valides'],
        'competences_ead_en_cours' => $competencesStats['EAD']['en_cours'],
        'competences_ead_non_valides' => $competencesStats['EAD']['non_valides'],
    ];
}

/**
 * Calcul des stats par type de savoir-faire (S/C/EAD)
 * Total = nombre de savoir-faire de ce type
 */
private function getStatsByType(string $typeCode): array
{
    if (!$this->parcours) {
        return ['total' => 0, 'valides' => 0, 'en_cours' => 0, 'non_valides' => 0];
    }

    $total = 0;
    $valides = 0;
    $enCours = 0;
    $nonValides = 0;

    foreach ($this->parcours['affectations'] ?? [] as $affectation) {
        foreach ($affectation['competences'] ?? [] as $competence) {
            foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                if (($csf['savoirfaire_type']['code'] ?? null) === $typeCode) {
                    $total++; // ✅ Total = nombre de savoir-faire
                    
                    $etatValid = $csf['etat_valid'] ?? false;
                    $totalObjectifs = count($csf['objectifs'] ?? []);
                    $objectifsValides = collect($csf['objectifs'] ?? [])
                        ->where('etat_valid', true)
                        ->count();

                    if ($etatValid) {
                        $valides++;
                    } elseif ($objectifsValides > 0 && $objectifsValides < $totalObjectifs) {
                        $enCours++;
                    } else {
                        $nonValides++;
                    }
                }
            }
        }
    }

    return [
        'total' => $total,
        'valides' => $valides,
        'en_cours' => $enCours,
        'non_valides' => $nonValides
    ];
}

/**
 * Calcul des stats par compétence, pour chaque type (S/C/EAD)
 * Total = nombre de compétences contenant au moins 1 savoir-faire de ce type
 */
private function getCompetencesStatsByType(): array
{
    if (!$this->parcours) {
        return [
            'S' => ['total' => 0, 'valides' => 0, 'en_cours' => 0, 'non_valides' => 0],
            'C' => ['total' => 0, 'valides' => 0, 'en_cours' => 0, 'non_valides' => 0],
            'EAD' => ['total' => 0, 'valides' => 0, 'en_cours' => 0, 'non_valides' => 0],
        ];
    }

    $stats = [
        'S' => ['total' => 0, 'valides' => 0, 'en_cours' => 0, 'non_valides' => 0],
        'C' => ['total' => 0, 'valides' => 0, 'en_cours' => 0, 'non_valides' => 0],
        'EAD' => ['total' => 0, 'valides' => 0, 'en_cours' => 0, 'non_valides' => 0],
    ];

    foreach ($this->parcours['affectations'] ?? [] as $affectation) {
        foreach ($affectation['competences'] ?? [] as $competence) {
            $competenceStats = [
                'S' => ['total' => 0, 'valides' => 0],
                'C' => ['total' => 0, 'valides' => 0],
                'EAD' => ['total' => 0, 'valides' => 0],
            ];

            // Compter les savoir-faire par type dans cette compétence
            foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                $typeCode = $csf['savoirfaire_type']['code'] ?? null;
                
                if (!isset($competenceStats[$typeCode])) {
                    continue;
                }

                $competenceStats[$typeCode]['total']++;
                if ($csf['etat_valid'] ?? false) {
                    $competenceStats[$typeCode]['valides']++;
                }
            }

            // Déterminer le statut de la compétence pour chaque type
            foreach (['S', 'C', 'EAD'] as $type) {
                if ($competenceStats[$type]['total'] > 0) {
                    $stats[$type]['total']++; // ✅ Total = nombre de compétences ayant ce type
                    
                    if ($competenceStats[$type]['valides'] === $competenceStats[$type]['total']) {
                        $stats[$type]['valides']++;
                    } elseif ($competenceStats[$type]['valides'] > 0) {
                        $stats[$type]['en_cours']++;
                    } else {
                        $stats[$type]['non_valides']++;
                    }
                }
            }
        }
    }

    return $stats;
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

    $activitesEnAttente = [];

    foreach ($this->parcours['affectations'] ?? [] as $affectation) {
        foreach ($affectation['competences'] ?? [] as $competence) {
            foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                // ✅ Vérifier aussi les savoir-faire proposés
                $csfEstPropose = !empty($csf['date_proposition']);
                $csfEstValide = ($csf['etat_valid'] ?? false) === true;
                
                if ($csfEstPropose && !$csfEstValide) {
                    $activitesEnAttente[] = [
                        'type' => 'savoirfaire',
                        'element' => $csf,
                        'savoirfaire' => $csf['savoirfaire']['libelle_court'] ?? '',
                        'competence' => $competence['libelle_court'] ?? '',
                        'affectation' => $affectation['libelle_court'] ?? ''
                    ];
                }

                // ✅ Vérifier les objectifs proposés
                foreach ($csf['objectifs'] ?? [] as $objectif) {
                    $objEstPropose = !empty($objectif['date_proposition']);
                    $objEstValide = ($objectif['etat_valid'] ?? false) === true;
                    
                    if ($objEstPropose && !$objEstValide) {
                        $activitesEnAttente[] = [
                            'type' => 'objectif',
                            'element' => $objectif,
                            'savoirfaire' => $csf['savoirfaire']['libelle_court'] ?? '',
                            'competence' => $competence['libelle_court'] ?? '',
                            'affectation' => $affectation['libelle_court'] ?? ''
                        ];
                    }
                }
            }
        }
    }

    return collect($activitesEnAttente);
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
        'FCM' => 'text-green-600',
        'SED' => 'text-blue-600',
        'GEL' => 'text-orange-600',
        'FIN' => 'text-purple-600',
        default => 'text-gray-600'
    };
}

public function getStatutBgColor(): string
{
    $code = $this->marin->getDernierTypeEtatCode();
   
    return match($code) {
        'FCM' => 'bg-green-50 border-green-200',
        'SED' => 'bg-blue-50 border-blue-200',
        'GEL' => 'bg-orange-50 border-orange-200',
        'FIN' => 'bg-purple-50 border-purple-200',
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

public function ouvrirJalonModal()
{
    $fcmMarin = $this->marin->complementsFcm;
    
    $this->jalonForm = [
        'date_sedimentation' => $fcmMarin?->date_sedimentation 
            ? \Carbon\Carbon::parse($fcmMarin->date_sedimentation)->format('Y-m-d')
            : null,
        'date_fcm' => $fcmMarin?->date_fcm 
            ? \Carbon\Carbon::parse($fcmMarin->date_fcm)->format('Y-m-d')
            : null,
        'date_competence' => $fcmMarin?->date_competence 
            ? \Carbon\Carbon::parse($fcmMarin->date_competence)->format('Y-m-d')
            : null,
    ];
    
    $this->showJalonModal = true;
    $this->dispatch('open-modal', id: 'jalon-modal');
}




public function sauvegarderJalons()
{
    $fcmMarin = $this->marin->complementsFcm;
    
    if ($fcmMarin) {
        if (!empty($this->jalonForm['date_sedimentation'])) {
           // $fcmMarin->date_sedimentation = \Carbon\Carbon::parse($this->jalonForm['date_sedimentation']);
           event(new UserGeneratedEvent(
            UserGeneratedEventTypes::DEFINIR_JALON_FCM,
            auth()->user()->uuid,
            ObjectClasses::MARIN,
            $this->marin->uuid,
            ["date_sedimentation" => $this->jalonForm['date_sedimentation']]
        ));
        }
        if (!empty($this->jalonForm['date_fcm'])) {
            /// $fcmMarin->date_fcm = \Carbon\Carbon::parse($this->jalonForm['date_fcm']);
            event(new UserGeneratedEvent(
                UserGeneratedEventTypes::DEFINIR_JALON_FCM,
                auth()->user()->uuid,
                ObjectClasses::MARIN,
                $this->marin->uuid,
                ["date_fcm" => $this->jalonForm['date_fcm']]
            ));
        }
        if (!empty($this->jalonForm['date_competence'])) {
           // $fcmMarin->date_competence = \Carbon\Carbon::parse($this->jalonForm['date_competence']);
           event(new UserGeneratedEvent(
            UserGeneratedEventTypes::DEFINIR_JALON_FCM,
            auth()->user()->uuid,
            ObjectClasses::MARIN,
            $this->marin->uuid,
            ["date_competence" => $this->jalonForm['date_competence']]
        ));
        }
        
        // $fcmMarin->save();
        
        // \Filament\Notifications\Notification::make()
        //     ->title('Jalons mis à jour')
        //     ->success()
        //     ->send();
    }
    $this->marin->refresh();
   // $this->showJalonModal = false;
   $this->dispatch('close-modal', id: 'jalon-modal');
    $this->dispatch('$refresh');
}

public function canEditJalons(): bool
{
    return auth()->user()->can("fcmcentral::definir_jalons_de_fcm");
}

public function getDateSedimentationFormatee(): string
{
    $date = $this->marin->complementsFcm?->date_sedimentation;
    return $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : 'Non renseigné';
}

public function getDateFcmFormatee(): string
{
    $date = $this->marin->complementsFcm?->date_fcm;
    return $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : 'Non renseigné';
}

public function getDateCompetenceFormatee(): string
{
    $date = $this->marin->complementsFcm?->date_competence;
    return $date ? \Carbon\Carbon::parse($date)->format('d/m/Y') : 'Non renseigné';
}




 

    // ============================================
    // 🔄 LIVEWIRE LISTENERS
    // ============================================

    

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
