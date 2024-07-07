<?php

namespace Modules\FcmCentral\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\FcmCommun\DataObjects\ParcoursDto;
use Modules\FcmCentral\Models\Parcours;

class TestParcoursArchitecture extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fcmcentral:test-serialization';

    /**
     * The console command description.
     */
    protected $description = 'Teste la serialization du parcours';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function get_roots_for_treeview($arrs)
    {
        return Arr::pluck($arrs, "id");
    }

    public function transform_for_treeview($arrs)
    {
        $ret = [];

        foreach($arrs as $arr){
            $arr["text"] = $arr["libelle_long"];


            if (Arr::exists($arr, "fonctions")){
                
                $fonctions = Arr::pull($arr, "fonctions");
                $arr["children"]= Arr::pluck($fonctions, "id");

                $fonctions = $this->transform_for_treeview($fonctions);

                $ret[] = $arr;
                $ret = array_merge($ret, $fonctions);

            }
            elseif (Arr::exists($arr, "competences")){
                $competences = Arr::pull($arr, "competences");
                $arr["children"]= Arr::pluck($competences, "id");


                $competences = $this->transform_for_treeview($competences);

                $ret[] = $arr;
                $ret = array_merge($ret, $competences);

            }
            elseif (Arr::exists($arr, "savoirfaires")){
                $savoirfaires = Arr::pull($arr, "savoirfaires");
                $arr["children"]= Arr::pluck($savoirfaires, "id");


                $savoirfaires = $this->transform_for_treeview($savoirfaires);

                $ret[] = $arr;
                $ret = array_merge($ret, $savoirfaires);

            }
            elseif (Arr::exists($arr, "activites")){
                $activites = Arr::pull($arr, "activites");
                $arr["children"]= Arr::pluck($activites, "id");


                $activites = $this->transform_for_treeview($activites);

                $ret[] = $arr;
                $ret = array_merge($ret, $activites);

            }
            else {
                $ret[] = $arr;
            }
        }        

        return $ret;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $p=Parcours::with('fonctions.competences.savoirfaires.activites')->first();

        $dto = ParcoursDto::from($p);
        dd($this->transform_for_treeview([$dto->toArray()]));

        dd($this->get_roots_for_treeview([$dto->toArray()]));

        $arr = $dto->toArray();
        //dd($arr);

            // $dtoback = ParcoursDto::from($arr);
            // dd($dtoback);
    }

    /**
     * Get the console command arguments.
     */
    protected function getArguments(): array
    {
        return [
           // ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     */
    protected function getOptions(): array
    {
        return [
            //['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }
}
