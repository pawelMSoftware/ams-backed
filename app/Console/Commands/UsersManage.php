<?php

namespace App\Console\Commands;

use App\Models\AMSUser as User;
use Illuminate\Console\Command;

class UsersManage extends Command
{
    /**
     * @todo do sth with this because there's no admin account already on AMS
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:manage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AMS2 users management';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->table(['AMS2 Users management'], []);
        $action = $this->choice(
            'Operation type',
            ['exit', 'set_admin_pass', 'cleaning'],
            0
        );
        if ($action === 'set_admin_pass') {
            $this->setAdminPass();
        } elseif ($action === 'cleaning') {
            $this->startCleaning();
        }
        return 0;
    }

    private function setAdminPass(): void
    {
        $password = $this->secret('Give new password for main admin (input is hidden)');
        $admin = User::where('login', 'admin')->get()->first();
        $admin->password = $password;
        $admin->save();
        $passwordHidden = $password[0] .  str_repeat('*', strlen($password) - 2) . $password[strlen($password) - 1];
        $this->info('main admin password has been changed into ' . $passwordHidden);
    }

    private function startCleaning(): void
    {
        if ($this->confirm('Are you sure you want to delete all the users except admin?')) {
            $removedNumber = User::where('login', '<>', 'admin')->delete();
            $this->info($removedNumber . ' records where deleted');
        }
    }
}
