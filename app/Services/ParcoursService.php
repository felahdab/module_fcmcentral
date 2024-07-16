<?php

namespace Modules\FcmCentral\Services;

use Illuminate\Support\Arr;

use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\UserParcours;

use Modules\FcmCommun\DataObjects\ParcoursDto;

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
        }
        else {
            $newversion = 1;
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

    // Dans un premier temps, travail sur ParcoursSerialize. Mais devra être le parcours d'un user en vrai.
    public static function applyChanges(UserParcours $parcours, $changes){
        //ddd($changes);

        $dto = ParcoursDto::from($parcours->parcours);
        
        foreach($dto->fonctions as $fonction){
            foreach($fonction->competences as $competence){
                foreach ($competence->savoirfaires as $savoirfaire){
                    foreach($savoirfaire->activites as $activite){
                        if (Arr::exists($changes, $activite->id)){
                            //dump('updating parcours: ');
                            //dump($changes[$activite->id]["state"]);
                            $activite->state = $changes[$activite->id]["state"]["checked"] == "set" ? ["checked"=> true] : ["checked" => false ] ;
                            // Raise an event for the modification of the state of the parcours.
                        }
                        //if ($changes)
                    }
                }
            }
        }
        $parcours->parcours=$dto;
        $parcours->save();
        //ddd($dto);


    }
}