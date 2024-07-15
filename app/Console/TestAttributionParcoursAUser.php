<?php

namespace Modules\FcmCentral\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Modules\FcmCommun\DataObjects\ParcoursDto;
use Modules\FcmCentral\Models\ParcoursSerialise;
use Modules\FcmCentral\Models\UserParcours;

use App\Models\User;

use Modules\FcmCentral\Services\ParcoursService;

class TestAttributionParcoursAUser extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fcmcentral:test-set-user-parcours';

    /**
     * The console command description.
     */
    protected $description = 'Attribue un parcours a un utilisateur';

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
        $p=ParcoursSerialise::query()->get()->first();
        $u=User::query()->get()->first();

        $up = new UserParcours();
        $up->user_id     = $u->uuid;
        $up->parcours_id = $p->id;
        $up->parcours    = $p->parcours;

        $up->save();
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
