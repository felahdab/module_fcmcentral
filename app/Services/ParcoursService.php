<?php

namespace Modules\FcmCentral\Services;

use Illuminate\Support\Arr;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\UserParcours;
use Modules\FcmCentral\Models\User;


use Modules\FcmCommun\DataObjects\ParcoursDto;
use Modules\FcmCommun\DataObjects\UserGeneratedEvent;

use Carbon\Carbon;

class ParcoursService
{
    public static function transform_for_treeview($arrs)
    {
        $ret = [];

        foreach($arrs as $arr){
            $arr["text"] = $arr["libelle_long"];

            if (Arr::exists($arr, "fonctions")){
                
                $fonctions = Arr::pull($arr, "fonctions");
                $arr["children"]= Arr::pluck($fonctions, "id");
                if (! array_key_exists("state", $arr)){
                    $arr["state"] = [
                        "checked" => false,
                        "opened" => false
                    ];
                }

                $fonctions = static::transform_for_treeview($fonctions);

                $ret[] = $arr;
                $ret = array_merge($ret, $fonctions);

            }
            elseif (Arr::exists($arr, "competences")){
                $competences = Arr::pull($arr, "competences");
                $arr["children"]= Arr::pluck($competences, "id");
                if (! array_key_exists("state", $arr)){
                    $arr["state"] = [
                        "checked" => false,
                        "opened" => false
                    ];
                }

                $competences = static::transform_for_treeview($competences);

                $ret[] = $arr;
                $ret = array_merge($ret, $competences);

            }
            elseif (Arr::exists($arr, "savoirfaires")){
                $savoirfaires = Arr::pull($arr, "savoirfaires");
                $arr["children"]= Arr::pluck($savoirfaires, "id");
                if (! array_key_exists("state", $arr)){
                    $arr["state"] = [
                        "checked" => false,
                        "opened" => false
                    ];
                }

                $savoirfaires = static::transform_for_treeview($savoirfaires);

                $ret[] = $arr;
                $ret = array_merge($ret, $savoirfaires);

            }
            elseif (Arr::exists($arr, "activites")){
                $activites = Arr::pull($arr, "activites");
                $arr["children"]= Arr::pluck($activites, "id");
                if (! array_key_exists("state", $arr)){
                    $arr["state"] = [
                        "checked" => false,
                        "opened" => false
                    ];
                }

                $activites = static::transform_for_treeview($activites);

                $ret[] = $arr;
                $ret = array_merge($ret, $activites);

            }
            else {
                if (! array_key_exists("state", $arr)){
                    $arr["state"] = [
                        "checked" => false,
                        "opened" => false
                    ];
                }
                $ret[] = $arr;
            }
        }        

        return $ret;
    }

    public static function get_roots_for_treeview($arrs)
    {
        return Arr::pluck($arrs, "id");
    }

    public static function serialize_parcours($parcoursdto, $date_de_debut){
        $previous = ParcoursSerialise::where('uuid', $parcoursdto->id)->orderBy('version')->get()->last();
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

        

        $p=ParcoursSerialise::create([
            "uuid" => $parcoursdto->id,
            "libelle_long" => $parcoursdto->libelle_long,
            "libelle_court"=> $parcoursdto->libelle_court, 
            "version" => $newversion, 
            "date_debut" => $date_de_debut, 
            "date_fin" => null,
            "parcours" => $parcoursdto->toArray()
        ]);

        ParcoursSerialise::where('uuid', $parcoursdto->id)
            ->where('version', '<', $newversion)
            ->where('date_fin', null)
            ->update(['date_fin' => $date_de_debut->add(-1, 'day')]);

        return $p;

    }

    public static function firstPossibleNewVersionDate($parcours){
        $uuid = $parcours->id;
        $previous = ParcoursSerialise::where('uuid', $uuid)->orderBy('version')->get()->last();
        if ($previous){
            return (new Carbon($previous->date_debut))->addDay()->startOfDay();
        }
        else {
            return Carbon::now()->addDay()->startOfDay();
        }
    }

    public static function applyChanges(UserParcours $userparcours, $changes, $commentaire){
        $dto = ParcoursDto::from($userparcours->parcours);
        
        foreach($dto->fonctions as $fonction){
            foreach($fonction->competences as $competence){
                foreach ($competence->savoirfaires as $savoirfaire){
                    foreach($savoirfaire->activites as $activite){
                        if (Arr::exists($changes, $activite->id)){
                            $activite->state = $changes[$activite->id]["state"]["checked"] == "set" ? ["checked"=> true] : ["checked" => false ] ;
                            // Raise an event for the modification of the state of the parcours.

                            $event = new UserGeneratedEvent(
                                event_type: $changes[$activite->id]["state"]["checked"] == "set" ? "activite_validee" : "activite_devalidee",
                                user_id: auth()->user()->uuid,
                                object_class: User::class, 
                                object_uuid: $userparcours->user_id,
                                detail: [
                                    "userparcours_id" => $userparcours->id,
                                    "parcours_id" => $userparcours->parcours_id,
                                    "activite_id" => $activite->id,
                                    "commentaire" => $commentaire
                                ]
                            );

                            event($event);
                        }
                    }

                    if (Arr::exists($changes, $savoirfaire->id)){
                        $savoirfaire->state = $changes[$savoirfaire->id]["state"]["checked"] == "set" ? ["checked"=> true] : ["checked" => false ] ;
                        // Raise an event for the modification of the state of the parcours.

                        $event = new UserGeneratedEvent(
                            event_type: $changes[$savoirfaire->id]["state"]["checked"] == "set" ? "savoirfaire_valide" : "savoirfaire_devalide",
                            user_id: auth()->user()->uuid,
                            object_class: User::class, 
                            object_uuid: $userparcours->user_id,
                            detail: [
                                "userparcours_id" => $userparcours->id,
                                "parcours_id" => $userparcours->parcours_id,
                                "savoirfaire_id" => $savoirfaire->id,
                                "commentaire" => $commentaire
                            ]
                        );

                        event($event);
                    }

                }
            }
        }
        $userparcours->parcours=$dto;
        $userparcours->save();
    }

    public static function attribuer_parcours_a_un_user($user, $parcours)
    {
        $event = new UserGeneratedEvent(
            event_type: "parcours_attribue",
            user_id: auth()->user()->uuid,
            object_class: get_class($user),
            object_uuid: $user->uuid,
            detail: [
                "parcoursserialise" => $parcours->id,
                // "parcours" => $parcours->uuid,
                // "version" => $parcours->version,
            ]
        );

        event($event);
    }

    public static function retirer_parcours_a_un_user($user, $parcours)
    {
        $event = new UserGeneratedEvent(
            event_type: "parcours_retire",
            user_id: auth()->user()->uuid,
            object_class: get_class($user),
            object_uuid: $user->uuid,
            detail: [
                "parcoursserialise" => $parcours->id,
                // "parcours" => $parcours->uuid,
                // "version" => $parcours->version,
            ]
        );

        event($event);
    }
}