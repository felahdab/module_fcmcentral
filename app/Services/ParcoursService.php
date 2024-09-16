<?php

namespace Modules\FcmCentral\Services;

use Carbon\Carbon;

use Modules\FcmCommun\Services\ParcoursService as BaseService;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\Marin;
use Modules\FcmCentral\Events\UserGeneratedEvent;

class ParcoursService extends BaseService
{
    public static $UserGeneratedEvent = UserGeneratedEvent::class;
    public static $ParcoursSerialise = ParcoursSerialise::class;
    public static $Marin = Marin::class;

    public static function serialize_parcours($parcoursdto, $date_de_debut){
        $previous = static::$ParcoursSerialise::where('uuid', $parcoursdto->id)->orderBy('version')->get()->last();
        $newversion = null;
        if ($previous){
            
            $previousversion = intval($previous->version);
            $newversion = $previousversion + 1;

            if ((new Carbon($previous->date_debut))->isAfter((new Carbon ($date_de_debut)))  ){
                return null; 
            }
        }
        else {
            $newversion = 1;

            if (Carbon::now()->isAfter((new Carbon ($date_de_debut)))  ){
                return null; 
            }
        }

        $p=static::$ParcoursSerialise::create([
            "uuid" => $parcoursdto->id,
            "libelle_long" => $parcoursdto->libelle_long,
            "libelle_court"=> $parcoursdto->libelle_court, 
            "version" => $newversion, 
            "date_debut" => $date_de_debut, 
            "date_fin" => null,
            "parcours" => $parcoursdto->toArray()
        ]);

        static::$ParcoursSerialise::where('uuid', $parcoursdto->id)
            ->where('version', '<', $newversion)
            ->where('date_fin', null)
            ->update(['date_fin' => $date_de_debut->add(-1, 'day')]);

        return $p;

    }

    public static function firstPossibleNewVersionDate($parcours){
        $uuid = $parcours->id;
        $previous = static::$ParcoursSerialise::where('uuid', $uuid)->orderBy('version')->get()->last();
        if ($previous){
            return (new Carbon($previous->date_debut))->addDay()->startOfDay();
        }
        else {
            return Carbon::now()->addDay()->startOfDay();
        }
    }
}