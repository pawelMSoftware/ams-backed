<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class GenerateSettingsMeta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'settings:meta';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate PhpStorm meta file';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $stub = File::get(__DIR__.'/stubs/settings_meta.stub');

        $names = DB::table('settings')
            ->pluck('name')
            ->sort();

        $stub = str_replace('{{ names }}', $names->implode(",\n\t\t"), $stub);

        if (! File::isDirectory('.phpstorm.meta.php')) {
            File::makeDirectory('.phpstorm.meta.php');
        }

        File::put(base_path('.phpstorm.meta.php/settings.php'), $stub);

        $this->info('.phpstorm.meta.php/settings.php Generated');

    }
}
