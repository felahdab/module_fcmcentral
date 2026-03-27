<?php

namespace Modules\FcmCentral\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use Illuminate\Support\Facades\File;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Services\SauveChargeParcoursService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\search;

class SauvegardeParcours extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     */
    protected $name = 'fcmcentral:sauvegarde-parcours';

    /**
     * The console command description.
     */
    protected $description = 'Sauvegarde la définition d\'un parcours dans un fichier.';

    /**
     * Create a new command instance.
     */
    public function __construct(public SauveChargeParcoursService $service)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $parcours_uuid = $this->argument('parcours_uuid');
        $force = $this->option('force');

        $parcours = Parcours::where('uuid', $parcours_uuid)->first();
        if (null == $parcours) {
            $this->fail("Le parcours indiqué n'existe pas!");
        }
        $filename = $this->argument('filename');
        if (File::exists($filename) && !$force) {
            $this->fail("Le fichier de destination existe déjà. Rajouter --force si vous souhaitez l'écraser.");
        }

        $this->service->sauvegarde($parcours, $filename);
    }

    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'parcours_uuid' => fn () => search(
                label: 'Rechercher un parcours:',
                placeholder: 'DEASM',
                options: fn ($value) => strlen($value) > 0
                    ? Parcours::where('libelle_long', 'like', "%{$value}%")->pluck('libelle_long', 'uuid')->all()
                    : []
            ),
            'filename' => 'Veuillez indiquer le nom du fichier dans lequel enregistrer la sauvegarde.',
        ];
    }

    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Ecraser le fichier s\'il existe déjà.'],
        ];
    }

    protected function getArguments()
    {
        return [
            ['parcours_uuid', InputArgument::REQUIRED, 'L\'UUID du parcours à sauvegarder.'],
            ['filename', InputArgument::REQUIRED, 'Le nom du fichier de destination.'],
        ];
    }
}
