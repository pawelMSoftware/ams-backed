<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DBPopulate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    //    protected $signature = 'db:populate {--db_conf=mysql:to specify not default database config name} {--force}';
    protected $signature = 'db:populate
        {--dbconf=mysql : database configuration to use}
        {--force : force db population even if db is not empty}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populates database with random data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $appEnv = App::environment();
        if (in_array($appEnv, ['testing', 'production'])) {
            $this->line('cannot run on production or testing env');

            return 0;
        }

        $this->info('DB dev data population');
        $database = 'mysql';
        if ($this->hasOption('dbconf') && ! empty($this->option('dbconf'))) {
            $database = $this->option('dbconf');
            $databaseName = config('database.connections.'.$database.'.database');
            $this->info('for database configuration: '.$database.' ['.$databaseName.']');
            DB::setDefaultConnection($database);
        }
        // check if db is not empty
        $dbTables = DB::select('SHOW TABLES');
        if (! empty($dbTables) && ! $this->option('force')) {
            $this->warn('given DB is not empty. db:populate should be run on empty database.');
            $this->line('if you would like to continue anyway try with `--force` argument');

            return 0;
        }

        $commandsToRun = [
            'db:seed --class=CreateDBStructure',
            'migrate',
            'redis:populate',
            'db:seed --class=UserSeeder',
            'db:seed --class=ResourceSeeder',
        ];
        $this->withProgressBar($commandsToRun, function ($command) use ($database) {
            if ($database !== '' && str_starts_with($command, 'db:')) {
                $command .= ' --database='.$database;
            }
            $this->line(' run: '.$command);
            Artisan::call($command);
        });

        $this->line(PHP_EOL);

        return parent::SUCCESS;
    }
}
