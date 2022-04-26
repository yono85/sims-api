<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\user_registers;

class UserRegisters extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $users = new user_registers;
        $users->id      =   101;
        $users->user_id   =   101;
        $users->token = md5('101');
        $users->code = '123123';
        $users->device_type   =   1; // 1 desktop, 2. tablet, 3 phone
        $users->ip_address        =   '1.0.0.0';
        $users->info       =   'info';
        $users->status = 1;
        $users->save();
    }
}
