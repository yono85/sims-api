<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use App\Http\Controllers\config\index as Config;
use Illuminate\Http\Request;
use App\reset_passwords as tblResetPasswords;
use App\auto_senders as tblAutoSenders;

class access extends Controller
{


    //reset password
    public function resetpassword($request)
    {
        //
        $Config = new Config;

        //get count row
        $newid = tblResetPasswords::count();
        $newid++;
        $newid = $newid++;


        //create new id
        $newid = $Config->createnewid([
            'value'         =>  $newid,
            'length'        =>  14
        ]);


        $geopip = json_decode($request['info'], true)['geoip'];
        $uagent = json_decode($request['info'], true)['uagent'];


        //update
        $upresetpassword = tblResetPasswords::where([
            'user_id'           =>  $request['user_id'],
            'status'            =>  1
        ])->update(['status'=>0]);

        $tokenReset = md5($newid);
        $addresetpassword = new tblResetPasswords;
        $addresetpassword->id           =   $newid;
        $addresetpassword->token        =   $tokenReset;
        $addresetpassword->user_id      =   $request['user_id'];
        $addresetpassword->ip_address   =   $geopip['ip'];
        $addresetpassword->device       =   $uagent['device'];
        $addresetpassword->info         =   $request['info'];
        $addresetpassword->status       =   1;
        $addresetpassword->save();

        $levelroot = $Config->rootapps($request['user_level']);

        //
        $infoautosender = [
            'user'          =>  [
                'id'                =>  $request['user_id'],
                'email'             =>  $request['email'],
                'name'              =>  $request['name']
            ],
            'apps'          =>  [
                'name'              =>  env("APP_NAMELABEL"), //$Config->apps()[$request['apps']['root']]['name'],
                'url'               =>  env("URL_APP"),
                'url_help'          =>  env("URL_APP"),
                'url_logo'          =>  $Config->apps()['company']['url_logo'],
                'url_link'  =>  env("URL_APP") . '/resetpassword?token=' . $tokenReset
            ]
        ];

        //get template
        $gettemplate = new \App\Http\Controllers\template\email\index;
        $gettemplate = $gettemplate->main([
            'id'=>'10003'
        ]);
        
        $content = $gettemplate['content'];
        $content = str_replace('{url_home}', $infoautosender['apps']['url'], $content);
        $content = str_replace('{apps_name}', $infoautosender['apps']['name'], $content);
        $content = str_replace('{name}', $request['name'], $content);
        $content = str_replace('{url}', $infoautosender['apps']['url_link'], $content );
        $content = str_replace('{url_help}', $infoautosender['apps']['url_help'], $content);
        $content = str_replace('{url_logo}', $infoautosender['apps']['url_logo'], $content);

        $template = [
            'header'    =>  [
                'title'     =>  $gettemplate['title'],
                'subject'   =>  $gettemplate['subject']
            ],
            'content'   =>  $content
        ];

        //
        $dataautosender = [
            'user_id'           =>  $request['user_id'],
            'type'              =>  1, //1. access
            'sub_type'          =>  3, //1. reset password,
            'sender_type'       =>  1, //1. send by email
            'sender_id'         =>  10001,
            'template'          =>  $template,
            'infosender'        =>  $infoautosender,
        ];

        $addautosender = new \App\Http\Controllers\models\autosenders;
        $addautosender = $addautosender->email($dataautosender);

        return $newid;
    }
}