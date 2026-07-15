<?php

namespace Modules\FcmCentral\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;
use Modules\FcmCentral\Database\Factories\ParcoursFactory;
use Modules\FcmCentral\Traits\HasTablePrefix;

class Parcours extends Model
{
    use HasFactory;

    use HasTablePrefix;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'uuid',
        'code',
        'libelle_long',
        'libelle_court',
        'parcours_type_id',
        'ordre',
        'color',
        'livret_pdf',
    ];

    protected $with = [
        'affectations.competences.competenceSavoirfaires.objectifs',
    ];

    // //////////////////
    // / Parcours      //
    // //////////////////

    /**
     * Les affectations qui constituent un parcours.
     */
    public function affectations(): BelongsToMany
    {
        return $this->belongsToMany(
            Affectation::class,
            'fcmcentral_affectation_parcours',
            'parcours_id',
            'affectation_id'
        )
            ->withPivot('ordre')
            ->withTimestamps()
            ->orderBy('fcmcentral_affectation_parcours.ordre')
        ;
    }

    public function competences()
    {
        $affectationIds = $this->affectations()->pluck('fcmcentral_affectations.id');

        return $this->hasMany(
            Competence::class,
            'affectation_id'
        )
            ->whereIn('affectation_id', $affectationIds)
            ->where('parcours_type_id', $this->parcours_type_id)
        ;
    }

    public function competenceSavoirfaires()
    {
        $affectationIds = $this->affectations()->pluck('fcmcentral_affectations.id');

        $competenceIds = Competence::whereIn('affectation_id', $affectationIds)
            ->where('parcours_type_id', $this->parcours_type_id)
            ->pluck('id')
        ;

        return $this->hasMany(
            CompetenceSavoirfaire::class,
            'competence_id'
        )->whereIn('competence_id', $competenceIds);
    }

    // / PDF
    public function getLivretPdfUrlAttribute(): ?string
    {
        return $this->livret_pdf
            ? Storage::disk('public')->url($this->livret_pdf)
            : null;
            
    }

    // //////////////////
    // / Serialisation //
    // //////////////////

    public function parcoursSerialise()
    {
        return $this->hasMany(ParcoursSerialise::class, 'parcours_id', 'id');
    }

    public function getLatestSerializedVersion()
    {
        $last_version = $this->parcoursSerialise()->orderBy('version')->get()->last();
        if (null == $last_version) {
            return 0;
        }

        return $last_version->version;
    }

    //  Fonction pour serialise
    public function parcoursType()
    {
        return $this->belongsTo(ParcoursType::class, 'parcours_type_id');
    }

    // protected $casts = [
    //     'parcours' => 'array',
    // ];

    protected static function newFactory(): ParcoursFactory
    {
        return ParcoursFactory::new();
    }
}
