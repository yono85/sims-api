<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Users;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $users = new Users;
        $users->id      =   101;
        $users->level   =   1;
        $users->sub_level   =   1;
        $users->name        =   'Yono Cahyono';
        $users->email       =   'yono@gmail.com'; //Str::random(10) . '@gmail.com';
        $users->password    =   Hash::make('test');
        $users->save();
    }
}
