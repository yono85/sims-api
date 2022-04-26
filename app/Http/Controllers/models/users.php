<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\user_registers as tblUserRegisters;
use App\users as tblUsers;
use App\user_configs as tblUserConfigs;
use Illuminate\Support\Facades\Hash;

class users extends Controller
{
    public function create($request)
    {
        $Config = new Config;

        //create users
        $users = $this->new($request);

        //add user config
        $dataconfig = [
            'user_id'       =>  $users['id'],
            'request'       =>  $request
        ];

        $adduserconfig = $this->userconfig($dataconfig);

        //add data registers
        $dataregisters = [
            'user_id'       =>  $users['id'],
            'info'          =>  $request['info'],
            'type'          =>  $request['type']
        ];

        // create registers
        $registers = $this->registers($dataregisters);
    
        
        $datanotif = [
            'user'      =>  [
                'id'        =>  $users['id'],
                'email'     =>  $request['email'],
                'name'      =>  $request['name'],
                'register_id'  =>  $registers['id']
            ],
            'apps'      =>  [
                'root'          =>  $Config->rootapps($request['level'])
            ]
        ];

        // notif
        $notif = $this->notifsender($datanotif);
        return $notif;
    }

    public function createnew($request)
    {
        $Config = new Config;

        //create users
        $users = $this->new($request);

        //add user config
        $dataconfig = [
            'user_id'       =>  $users['id'],
            'request'       =>  $request
        ];

        $adduserconfig = $this->userconfig($dataconfig);

        //add data registers
        $dataregisters = [
            'user_id'       =>  $users['id'],
            'info'          =>  $request['info'],
            'type'          =>  $request['type']
        ];

        // create registers
        $registers = $this->registers($dataregisters);
    
        
        $datanotif = [
            'user'      =>  [
                'id'        =>  $users['id'],
                'email'     =>  $request['email'],
                'name'      =>  $request['name'],
                'register_id'  =>  $registers['id']
            ],
            'apps'      =>  [
                'root'          =>  $Config->rootapps($request['level'])
            ]
        ];

        // notif
        $notif = $this->notifsender($datanotif);
        
        return $users;
    }

    //new users
    public function new($request)
    {
        $Config = new Config;

        //
        $newidusers = tblUsers::count();
        $newidusers++;
        $newidusers = '9' . sprintf('%010s', $newidusers++);

        $token = md5($newidusers);
        //
        $users = new tblUsers;
        $users->id          =   $newidusers;
        $users->token       =   $token;
        $users->search      =   trim($request['name']) .';'.trim($request['email']).';'.(int)trim($request['phone']);
        $users->name        =   trim($request['name']);
        $users->email       =   trim($request['email']);
        $users->password    =   trim($request['password']) == '' ? '' : Hash::make(trim($request['password']));
        $users->username    =   trim($request['username']);
        $users->company_id  =   trim($request['company_id']);
        $users->level       =   trim($request['level']);
        $users->sub_level   =   trim($request['sub_level']);
        $users->gender      =   trim($request['gender']);
        $users->phone       =   trim($request['phone']);
        $users->phone_code  =   trim($request['phone_code']);
        $users->registers   =   0;
        $users->status      =   1;
        $users->save();

        $data = [
            'id'        =>  $newidusers,
            'token'     =>  $token,
            'email'     =>  $request['email'],
            'name'      =>  $request['name']
        ];

        return $data;

    }


    public function userconfig($request)
    {
        //config
        $Config = new Config;

        //request
        $user_id = $request['user_id'];
        $request = $request['request'];

        //
        $newidconfig = tblUserConfigs::count();
        $newidconfig++;
        $newidconfig = $newidconfig++;

        // if( $request['level'] != 0)
        // {
            $dataaside = [
                'user_id'   =>  $user_id,
                'level'     =>  $request['level'],
                'sublevel'  =>  $request['sub_level']
            ];

            $aside = new \App\Http\Controllers\config\aside;
            $aside = $aside->createaside($dataaside);
            $aside_menu = json_encode($aside);

        // }
        // else
        // {
        //     $aside_menu = '';
        // }

        //create new id
        $newidconfig = $Config->createnewid([
            'value'         =>  $newidconfig,
            'length'        =>  11
        ]);

        $newaddconfig = new tblUserConfigs;
        $newaddconfig->id               =   $newidconfig;
        $newaddconfig->type             =   $request['level'];
        $newaddconfig->user_id          =   $user_id;
        $newaddconfig->company_id       =   $request['company_id'];
        $newaddconfig->homepage         =   '/dashboard';
        $newaddconfig->aside_id         =   $request['level'];
        $newaddconfig->aside_menu       =   $aside_menu;
        $newaddconfig->admin_id         =   $request['level'] === '1' ? $request['admin_id'] : $user_id;
        $newaddconfig->terms            =   0;
        $newaddconfig->terms_date       =   "";
        $newaddconfig->status           =   1;
        $newaddconfig->save();

    }

    //insert registers
    public function registers($request)
    {
        //
        $Config = new Config;

        //
        $upregisters = tblUserRegisters::where([
            'user_id'       =>  $request['user_id']
        ])->update([
            'status'        =>  0
        ]);

        //
        $newidregisters = tblUserRegisters::count();
        $newidregisters++;
        $newidregisters = $newidregisters++;

        //create new code
        $newcode = $Config->createuniqnum([
            'value'         =>  $newidregisters,
            'length'        =>  4
        ]);
        
        //create new id
        $newidregisters = $Config->createnewid([
            'value'         =>  $newidregisters,
            'length'        =>  11
        ]);

        //
        $geoip = $request['info'] === '' ? '' : json_decode($request['info'], true)['geoip'];
        $uagent = $request['info'] === '' ? '' : json_decode($request['info'],true)['uagent'];

        //
        $registers = new tblUserRegisters;
        $registers->id              =   $newidregisters;
        $registers->type            =   $request['type'];
        $registers->user_id         =   $request['user_id'];
        $registers->token           =   md5($newidregisters);
        $registers->code            =   $newcode;
        $registers->ip_address      =   $request['info'] === '' ? '' : $geoip['ip'];
        $registers->device          =   $request['info'] === '' ? '' : $uagent['device'];
        $registers->info            =   $request['info'] === '' ? '' : $request['info'];
        $registers->status          =   1;
        $registers->save();

        $data = [
            'id'        =>  $newidregisters
        ];

        return $data;
    }


    public function notifsender($request)
    {
        $Config = new Config;

        //
        $infoautosender = [
            'user'          =>  [
                'id'                =>  $request['user']['id'],
                'email'             =>  $request['user']['email'],
                'name'              =>  $request['user']['name']
            ],
            'apps'          =>  [
                'name'              =>  env("APP_NAMELABEL"), //$Config->apps()[$request['apps']['root']]['name'],
                'url'               =>  env("URL_APP"),
                'url_help'          =>  env("URL_APP"),
                'url_logo'          =>  $Config->apps()['company']['url_logo'],
                'url_link'          =>  env("URL_APP") . '/account/verification?token=' . md5($request['user']['register_id'])
            ]
        ];

        //get template
        $gettemplate = new \App\Http\Controllers\template\email\index;
        $gettemplate = $gettemplate->main(['id'=>'10001']);
        
        //
        $content = $gettemplate['content'];
        $content = str_replace('{url_home}', $infoautosender['apps']['url'], $content);
        $content = str_replace('{apps_name}', $infoautosender['apps']['name'], $content);
        $content = str_replace('{name}', $request['user']['name'], $content);
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
            'user_id'           =>  $request['user']['id'],
            'type'              =>  1, //1. access
            'sub_type'          =>  1, //1. verif account,
            'sender_type'       =>  1, //1. send by email
            'sender_id'         =>  10001,
            'template'          =>  $template,
            'infosender'        =>  $infoautosender,
        ];
        
        $addnewautosender = new \App\Http\Controllers\models\autosenders;
        $addnewautosender = $addnewautosender->email($dataautosender);

        return $addnewautosender;
    }


    //EMPLOYE ACCOUNT
    public function createEmploye($request)
    {
        $Config = new Config;

        //ADD USER
        $addUser = $this->new($request);

        //ADD REGISTER
        $dataReg = [
            'user_id'       =>  $addUser['id'],
            'info'          =>  '',
            'type'          =>  1
        ];
        $addReg = $this->registers($dataReg);

        //create user config
        $dataConfig = [
            'user_id'       =>  $addUser['id'],
            'request'       =>  $request
        ];

        $this->userconfig($dataConfig);

        //NOTIF EMAIL SENDER
        $dataSender = [
            'user'      =>  [
                'id'        =>  $addUser['id'],
                'name'      =>  $addUser['name'],
                'email'     =>  $addUser['email'],
                'register_id'   =>  $addReg['id']
            ],
            'apps'      =>  [
                'root'          =>  $Config->rootapps('9')
            ]
        ];

        $notif = $this->notifsender($dataSender);

        return $addUser;
    }

    //UPDATE USERS
    public function updateusers($request)
    {
        $update = tblUsers::where([
            'id'        =>  trim($request->id)
        ])
        ->update([
            'search'        =>  trim($request['name']) .';'.trim($request['email']).';'.(int)trim($request['phone']),
            'name'          =>  trim($request->name),
            'email'         =>  trim($request->email),
            'phone'         =>  trim($request->phone),
            'gender'        =>  trim($request->gender),
            'sub_level'     =>  trim($request->sublevel)
        ]);
    }
}