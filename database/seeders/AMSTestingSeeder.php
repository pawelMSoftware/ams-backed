<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;

class AMSTestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \Eloquent::unguard();
        $path = database_path('fixtures/ams_db_structure.sql');
        $appEnv = App::environment();
        if (in_array($appEnv, ['testing', 'production'])) {
            // do not run seed again when db is already populated
            if (Schema::hasTable('users')) {
                return;
            }
        }
        \DB::unprepared(file_get_contents($path));
    }
}
