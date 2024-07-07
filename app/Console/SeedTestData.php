<?php

namespace Modules\FcmCentral\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\FcmCommun\DataObjects\ParcoursDto;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\Fonction;
use Modules\FcmCentral\Models\Competence;
use Modules\FcmCentral\Models\SavoirFaire;
use Modules\FcmCentral\Models\Activite;

class SeedTestData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fcmcentral:seed-data';

    /**
     * The console command description.
     */
    protected $description = 'Genere des donnees de parcours pour les tests';

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
        $a = Activite::factory(2)->create();
        $b = SavoirFaire::factory(1)->create();
        $b->first()->activites()->attach($a, ['coeff' => "1", "duree" => "1 semaine", "ordre" => 1]);

        $c = Competence::factory(1)->create();
        $c->first()->savoirfaires()->attach($b);

        $d = Fonction::factory(1)->create();
        $d->first()->competences()->attach($c);

        $e=Parcours::factory(1)->create();
        $e->first()->fonctions()->attach($d);
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
