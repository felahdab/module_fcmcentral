<?php

namespace Modules\FcmCentral\Filament\Fcmcentral\Resources\MarinResource\Pages\Concerns\FicheBilan;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Modules\FcmCommun\Services\CalculHeuresParcoursService;

trait HasSavoirFaireProgression
{
    // ✅ Filtres d'affichage de la progression par savoir-faire
    public string $savoirFaireTypeFilter = 'tous'; // tous | S | EAD | C
    public string $savoirFaireStatutFilter = 'tous'; // tous | valide | non_valide

    public function filtrerParStatutSavoirFaire(string $statut): void
    {
        $this->savoirFaireStatutFilter = $statut;
    }

    public function filtrerParTypeSavoirFaire(string $type): void
    {
        $this->savoirFaireTypeFilter = $type;
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

                    // Récupère le type de savoir-faire
                    $typeCode = $csf['savoirfaire_type']['code'] ?? 'C';

                    if ('C' === $typeCode) {
                        $pourcentage = $totalObjectifs > 0
                            ? round(($objectifsValides / $totalObjectifs) * 100, 0)
                            : 0;
                    } elseif ('S' === $typeCode || 'EAD' === $typeCode) {
                        $pourcentage = ($csf['etat_valid'] ?? false) ? 100 : 0;
                    } else {
                        $pourcentage = 0;
                    }

                    $couleurClasse = $this->getCouleurSavoirFaire($pourcentage, $csf['etat_valid'] ?? false);

                    // ✅ Filtre par type (tous / S / EAD / C)
                    if ('tous' !== $this->savoirFaireTypeFilter && $typeCode !== $this->savoirFaireTypeFilter) {
                        continue;
                    }

                    // ✅ Filtre par statut (tous / validé / non validé)
                    $estValide = ($csf['etat_valid'] ?? false) === true;
                    if ('valide' === $this->savoirFaireStatutFilter && !$estValide) {
                        continue;
                    }
                    if ('non_valide' === $this->savoirFaireStatutFilter && $estValide) {
                        continue;
                    }

                    $pieceJointe = $csf['piece_jointe'] ?? null;

                    $savoirFairesArray[] = [
                        'uuid' => $csf['savoirfaire']['uuid'] ?? $csf['uuid'] ?? '',
                        'csf_uuid' => $csf['uuid'] ?? null, // ✅ identifiant utilisé pour valider/dévalider/remplacer la pièce jointe
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
                        'objectifs' => $objectifs->toArray(),
                        'piece_jointe' => $pieceJointe,
                        'piece_jointe_url' => $pieceJointe ? route('fcmcentral.piece-jointe.telecharger', [
                            'marinUuid' => $this->marin->uuid,
                            'type' => 'savoirfaire',
                            'uuid' => $csf['uuid'] ?? '',
                        ]) : null,
                    ];
                }
            }
        }

        return collect($savoirFairesArray);
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

    public function hasMultipleNonZeroStats(): bool
    {
        $nonZeroCount = 0;

        if ($this->statistiques['savoirfaires_valides'] > 0) {
            ++$nonZeroCount;
        }
        if ($this->statistiques['savoirfaires_en_cours'] > 0) {
            ++$nonZeroCount;
        }
        if ($this->statistiques['savoirfaires_non_commences'] > 0) {
            ++$nonZeroCount;
        }

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
                            'affectation' => $affectation['libelle_court'] ?? '',
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
                                'affectation' => $affectation['libelle_court'] ?? '',
                            ];
                        }
                    }
                }
            }
        }

        return collect($activitesEnAttente);
    }

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
                    if (($csf['savoirfaire_type']['code'] ?? null) === 'C') {
                        ++$totalCompagnonnage;

                        if ($csf['etat_valid'] ?? false) {
                            ++$compagnonnageValide;
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

        $totalStages = 0;
        $stagesValides = 0;

        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    if (($csf['savoirfaire_type']['code'] ?? null) === 'S') {
                        ++$totalStages;

                        if ($csf['etat_valid'] ?? false) {
                            ++$stagesValides;
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

        $totalStages = 0;
        $stagesValides = 0;

        foreach ($this->parcours['affectations'] ?? [] as $affectation) {
            foreach ($affectation['competences'] ?? [] as $competence) {
                foreach ($competence['competenceSavoirfaires'] ?? [] as $csf) {
                    if (($csf['savoirfaire_type']['code'] ?? null) === 'EAD') {
                        ++$totalStages;

                        if ($csf['etat_valid'] ?? false) {
                            ++$stagesValides;
                        }
                    }
                }
            }
        }

        return $totalStages > 0
            ? round(($stagesValides / $totalStages) * 100, 1)
            : 0;
    }

    private function getCouleurSavoirFaire(float $pourcentage, bool $etatValid = false): array
    {
        if ($etatValid && 100 == $pourcentage) {
            // Validé
            return [
                'bg' => 'bg-green-100 dark:bg-green-900',
                'text' => 'text-green-800 dark:text-green-200',
                'progress' => 'bg-green-500 dark:bg-green-400',
                'border' => 'border-green-500 dark:border-green-400',
            ];
        }
        if ($pourcentage > 0 && $pourcentage < 100) {
            // En cours
            return [
                'bg' => 'bg-blue-100 dark:bg-blue-900',
                'text' => 'text-blue-800 dark:text-blue-200',
                'progress' => 'bg-blue-500 dark:bg-blue-400',
                'border' => 'border-blue-500 dark:border-blue-400',
            ];
        }

        // Non commencé
        return [
            'bg' => 'bg-gray-100 dark:bg-gray-800',
            'text' => 'text-gray-800 dark:text-gray-200',
            'progress' => 'bg-gray-500 dark:bg-gray-400',
            'border' => 'border-gray-500 dark:border-gray-400',
        ];
    }

    /**
     * Calcul des stats par type de savoir-faire (S/C/EAD)
     * Total = nombre de savoir-faire de ce type.
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
                        ++$total; // ✅ Total = nombre de savoir-faire

                        $etatValid = $csf['etat_valid'] ?? false;
                        $totalObjectifs = count($csf['objectifs'] ?? []);
                        $objectifsValides = collect($csf['objectifs'] ?? [])
                            ->where('etat_valid', true)
                            ->count()
                        ;

                        if ($etatValid) {
                            ++$valides;
                        } elseif ($objectifsValides > 0 && $objectifsValides < $totalObjectifs) {
                            ++$enCours;
                        } else {
                            ++$nonValides;
                        }
                    }
                }
            }
        }

        return [
            'total' => $total,
            'valides' => $valides,
            'en_cours' => $enCours,
            'non_valides' => $nonValides,
        ];
    }

    /**
     * Calcul des stats par compétence, pour chaque type (S/C/EAD)
     * Total = nombre de compétences contenant au moins 1 savoir-faire de ce type.
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

                    ++$competenceStats[$typeCode]['total'];
                    if ($csf['etat_valid'] ?? false) {
                        ++$competenceStats[$typeCode]['valides'];
                    }
                }

                // Déterminer le statut de la compétence pour chaque type
                foreach (['S', 'C', 'EAD'] as $type) {
                    if ($competenceStats[$type]['total'] > 0) {
                        ++$stats[$type]['total']; // ✅ Total = nombre de compétences ayant ce type

                        if ($competenceStats[$type]['valides'] === $competenceStats[$type]['total']) {
                            ++$stats[$type]['valides'];
                        } elseif ($competenceStats[$type]['valides'] > 0) {
                            ++$stats[$type]['en_cours'];
                        } else {
                            ++$stats[$type]['non_valides'];
                        }
                    }
                }
            }
        }

        return $stats;
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
                        $date = Carbon::parse($csf['date_validation']);
                        if (!$derniereDate || $date->gt($derniereDate)) {
                            $derniereDate = $date;
                        }
                    }

                    foreach ($csf['objectifs'] ?? [] as $objectif) {
                        if (!empty($objectif['date_validation'])) {
                            $date = Carbon::parse($objectif['date_validation']);
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
