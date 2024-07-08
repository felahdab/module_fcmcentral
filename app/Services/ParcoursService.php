<?php

namespace Modules\FcmCentral\Services;

use Illuminate\Support\Arr;
use Carbon\Carbon;

use Modules\FcmCentral\Models\ParcoursSerialise;

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
                $arr["state"] = [];

                $fonctions = static::transform_for_treeview($fonctions);

                $ret[] = $arr;
                $ret = array_merge($ret, $fonctions);

            }
            elseif (Arr::exists($arr, "competences")){
                $competences = Arr::pull($arr, "competences");
                $arr["children"]= Arr::pluck($competences, "id");
                $arr["state"] = [];

                $competences = static::transform_for_treeview($competences);

                $ret[] = $arr;
                $ret = array_merge($ret, $competences);

            }
            elseif (Arr::exists($arr, "savoirfaires")){
                $savoirfaires = Arr::pull($arr, "savoirfaires");
                $arr["children"]= Arr::pluck($savoirfaires, "id");
                $arr["state"] = [];

                $savoirfaires = static::transform_for_treeview($savoirfaires);

                $ret[] = $arr;
                $ret = array_merge($ret, $savoirfaires);

            }
            elseif (Arr::exists($arr, "activites")){
                $activites = Arr::pull($arr, "activites");
                $arr["children"]= Arr::pluck($activites, "id");
                $arr["state"] = [];

                $activites = static::transform_for_treeview($activites);

                $ret[] = $arr;
                $ret = array_merge($ret, $activites);

            }
            else {
                $arr["state"] = [];
                $ret[] = $arr;
            }
        }        

        return $ret;
    }

    public static function get_roots_for_treeview($arrs)
    {
        return Arr::pluck($arrs, "id");
    }

    public static function serialize_parcours($parcoursdto){
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
            "date_debut" => Carbon::now(), 
            "date_fin" => Carbon::tomorrow(), 
            "parcours" => $parcoursdto->toArray()
        ]);

    }
}