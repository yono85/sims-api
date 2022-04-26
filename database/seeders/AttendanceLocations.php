<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\attendance_locations as tblAttendanceLocations;
use App\Http\Controllers\config\index as Config;
use DB;

class AttendanceLocations extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $Config = new Config;

        //
        $newid = $Config->createnewidnew([
            'value'     =>  tblAttendanceLocations::count(),
            'length'    =>  7
        ]);

        $newadd                     =   new tblAttendanceLocations;
        $newadd->id                 =   $newid;
        $newadd->token              =   md5($newid);
        $newadd->token_static       =   $Config->randString(50,$newid);
        $newadd->token_dinamis      =   $Config->randString(50,strtotime(date('Y-m-d H:i:s', time())) );
        $newadd->name               =   'Pabrik Kranggan';//'Pabrik Huda'; //'Kantor Lengkong';
        $newadd->address            =   'Jl. Cendana Blok 5 No.24 RT.015/RW.015, Jatisampurna';//'Gg. Huda Rt.001/008, Jatiraden';//'Gang Lengkong no. 68, Cilangkap Baru';
        $newadd->kodepos            =   17433;
        $newadd->kecamatan          =   3275011; //32,3275,3275011
        $newadd->city               =   3275;
        $newadd->provinsi           =   32;
        $newadd->description        =   '';
        $newadd->field              =   '';
        $newadd->diff_minute        =   -11;
        $newadd->company_id         =   100000001;
        $newadd->user_id            =   0;
        $newadd->status             =   1;
        $newadd->save();
    }
}
