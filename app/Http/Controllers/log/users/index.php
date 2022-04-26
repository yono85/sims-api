<?php
namespace App\Http\Controllers\log\users;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config as Config;
use DB;

class index extends Controller
{
    //
    public function main(Request $request)
    {
        $Config = new Config;

        $newid = DB::table('user_logins')->count();
        $newid++;


        //create new id in config 
        $datanewid = [
            'value'         =>  $newid++,
            'length'        =>  15
        ];
        $newidcreate = $Config->createnewid($datanewid);

        

    }
}