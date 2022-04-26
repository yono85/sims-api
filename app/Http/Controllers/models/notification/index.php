<?php
namespace App\Http\Controllers\models\notification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\notifications as tblNotifications;
use App\Http\Controllers\config\index as Config;


class index extends Controller
{
    //
    public function main($request)
    {
        //
        $Config = new Config;

        $newid = $Config->createnewidnew([
            'value'         =>  tblNotifications::count(),
            'length'        =>  15
        ]);

        $token = md5($newid);

        $addnew             =   new tblNotifications;
        $addnew->id         =   $newid;
        $addnew->token      =   $token;
        $addnew->type       =   $request['type']; //1 sdm, 2. consumable, 3. doc emp
        $addnew->level      =   $request['level']; //
        $addnew->from_id    =   $request['from'];
        $addnew->to_id      =   $request['to'];
        $addnew->content    =   json_encode($request['content']);
        $addnew->open       =   0;
        $addnew->status     =   1;
        $addnew->save();

        $data = [
            'id'        =>  $newid,
            'token'     =>  $token
        ];

        return $data;
    }


    //public function 
    public function open($request)
    {
        $update = tblNotifications::where([
            'token'     =>  $request['token'],
            'open'      =>  0
        ])
        ->update([
            'open'      =>  1
        ]);
    }
}