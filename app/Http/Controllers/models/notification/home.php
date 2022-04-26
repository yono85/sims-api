<?php
namespace App\Http\Controllers\models\notification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\home_notifications as tblHomeNotifications;
use App\Http\Controllers\config\index as Config;

class home extends Controller
{
    //
    public function main($request)
    {
        $Config = new Config;

        $newid = $Config->createnewidnew([
            'value'         =>  tblHomeNotifications::count(),
            'length'        =>  15
        ]);

        $token = md5($newid);

        //
        $newadd             =   new tblHomeNotifications;
        $newadd->id         =   $newid;
        $newadd->token      =   $token;
        $newadd->type       =   $request['type'];
        $newadd->to_id      =   $request['to_id'];
        $newadd->from_id    =   $request['from_id'];
        $newadd->groups     =   $request['groups'];
        $newadd->label      =   $request['label'];
        $newadd->content    =   $request['content'];
        $newadd->link       =   $request['link'];
        $newadd->open       =   0;
        $newadd->open_date  =   "";
        $newadd->read_status = 0;
        $newadd->read_date  =   "";
        $newadd->status     =   1;
        $newadd->save();

        return $data = [
            "id"        =>  $newid,
            "token"     =>  $token
        ];

    }
}