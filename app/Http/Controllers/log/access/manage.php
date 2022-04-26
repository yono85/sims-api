<?php
namespace App\Http\Controllers\log\access;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\user_logins as tblUserlogins;
use DB;

class manage extends Controller
{
    //user logins
    public function logins($request)
    {

        $Config = new Config;


        //update logins
        $updatelogins = tblUserlogins::where([
            'user_id'       =>  $request['account']['id'],
            'status'        =>  1
        ])
        ->update([
            'logout'        =>  1,
            'logout_date'   =>  $Config->date(),
            'status'        =>  0
        ]);


        //create new id
        $newid = tblUserlogins::count();
        $newid++;
        // $newidlogins = $newid;
        $datacreatenewid = [
            'value'             =>  $newid++,
            'length'            =>  16
        ];
        $newidlogins = $Config->createnewid($datacreatenewid);

        $geoip = $request['info'] === '' ? '' : json_decode($request['info'], true)['geoip'];
        $uagent = $request['info'] === '' ? '' : json_decode($request['info'],true)['uagent'];

        $logins = new tblUserlogins;
        $logins->id         =   $newidlogins;
        $logins->user_id    =   $request['account']['id'];
        $logins->token      =   md5($newidlogins);
        $logins->token_jwt  =   $request['token'];
        $logins->device_type    =   $request['info'] === '' ? '' : $uagent['device'];
        $logins->ip_address     =   $request['info'] === '' ? '' : $geoip['ip'];
        $logins->info           =   $request['info'];
        $logins->logout         =   0;
        $logins->logout_date    =   '';
        $logins->status         =   1;
        $logins->save();
    }



    //logout
    public function logout($request)
    {
        $token = $request;

        $uplogins = tblUserlogins::where([
            'token_jwt'         =>  $token
        ])->update([
            'logout'        =>  1,
            'logout_date'   =>  date('Y-m-d H:i:s', time()),
            'status'        =>   0
        ]);
    }
}