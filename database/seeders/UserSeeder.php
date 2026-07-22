<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AMSUser as User;
use App\Models\Group;
use App\Models\GroupsUsers;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $groups = Group::all()->toArray();
        $groupsNumber = count($groups);
        $userAdmin = User::where('login', 'admin')->get()->first();
        if ($userAdmin === null) {
            $user = User::create([
                'login' => 'admin',
                'email' => 'admin@admin',
                'email_verified_at' => now(),
                'password' => 'adminadmin',
                'remember_token' => Str::random(10),
            ]);
            $groupAdmin = Group::where('name', 'Admins')->get()->first();
            GroupsUsers::create([
                'group_id' => $groupAdmin->group_id,
                'user_id' => $user->user_id,
            ]);
        }
        User::factory()->count(10)->create()->each(function ($user) use ($groups, $groupsNumber) {
            $randomGroup = $groups[rand(0, $groupsNumber - 1)];
            GroupsUsers::create([
                'group_id' => $randomGroup['group_id'],
                'user_id' => $user->user_id,
            ]);
        });
    }
}
