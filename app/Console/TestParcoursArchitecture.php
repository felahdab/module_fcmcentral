<?php

namespace Modules\FcmCentral\Console;

use Illuminate\Console\Command;
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

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $p=Parcours::with('fonctions.competences.savoirfaires.stages', 'fonctions.competences.savoirfaires.objectifs')->first();

        $dto = ParcoursDto::from($p);
        //dd($dto);

        $arr = $dto->toArray();

        $dtoback = ParcoursDto::from($arr);
        dd($dtoback);
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
