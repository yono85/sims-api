<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\auto_senders as tblAutoSenders;

class autosenders extends Controller
{
    //
    public function email($request)
    {
        
        //
        $Config = new Config;

        //insert table automation
        $newidauto = tblAutoSenders::count();
        $newidauto++;
        $newidauto = $Config->createnewid([
            'value'         =>  $newidauto++,
            'length'        =>  15
        ]);


        $token = md5($newidauto);

        $autosender = new tblAutoSenders;
        $autosender->id             =   $newidauto;
        $autosender->token          =  $token;
        $autosender->type           =   $request['type']; //1 email, 2 whatsapp
        $autosender->sub_type       =   $request['sub_type']; //1 verifiy
        $autosender->sender_type    =   $request['sender_type']; //1 single, 2 miltiple
        $autosender->sender_id      =   $request['sender_id']; //relasi tbl_email_sender
        $autosender->template       =   json_encode($request['template']['header']);
        $autosender->template_body  =   $request['template']['content'];
        $autosender->info           =   json_encode($request['infosender']);
        $autosender->user_id        =   $request['user_id'];
        $autosender->sender_email   =   0;
        $autosender->status_email   =   '';
        $autosender->sender_wa      =   0;
        $autosender->status_wa      =   '';
        $autosender->status         =   1;
        $autosender->save();

        return $data = [
            'id'            =>  $newidauto,
            'token'         =>  $token
        ];
    }


    

    //automation sender wa
    public function whatsapp($request)
    {

    }

}