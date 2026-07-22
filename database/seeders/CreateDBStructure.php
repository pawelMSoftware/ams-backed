<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDBStructure extends Seeder
{
    /**
     * Legacy db structure and data population
     *
     * @return void
     */
    public function run(): void
    {
        \Eloquent::unguard();
        $paths = ['database/fixtures/ams_db_structure.sql', 'database/fixtures/ams_db_categories.sql'];
        $appEnv = App::environment();
        if (in_array($appEnv, ['testing', 'production'])) {
            // do not run seed again when db is already populated
            if (Schema::hasTable('users')) {
                return;
            }
        }
        foreach ($paths as $path) {
            \DB::unprepared(file_get_contents($path));
        }
    }
}
