<?php

namespace Modules\FcmCentral\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\FcmCommun\DataObjects\ParcoursDto;
use Modules\FcmCentral\Models\Parcours;
use Modules\FcmCentral\Models\ParcoursSerialise;

use Modules\FcmCentral\Services\ParcoursService;



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
        $p1=Parcours::query()->get()->first();
        $p2=Parcours::query()->get()->last();

        $dto1 = ParcoursDto::from($p1);
        //dd($dto1->toArray());
        ParcoursService::serialize_parcours($dto1);
        return;
        
        $dto2=ParcoursDto::from(ParcoursSerialise::first()->parcours);
        dd($dto2);

        $dto2 = ParcoursDto::from($p2);
        dd(ParcoursService::transform_for_treeview([$dto1->toArray(), $dto2->toArray()]));

        dd(ParcoursService::get_roots_for_treeview([$dto1->toArray(), $dto2->toArray()]));

        $arr = $dto1->toArray();

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
