<?php
namespace App\Http\Controllers\account;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\user_registers as tblUserRegisters;
use App\users as tblUsers;
use App\Http\Controllers\account\index as Account;
use App\auto_senders as tblAutoSenders;
use App\user_configs as tblUserConfigs;
use App\user_companies as tblUserCompanies;
use App\reset_passwords as tblResetPasswords;
use App\user_logins as tblUserlogins;
use App\Http\Controllers\config\index as Config;
use Illuminate\Support\Facades\Hash;
// use App\Http\Controllers\access\manage as Refresh;
use DB;

class manage extends Controller
{
    //register success
    public function registersuccess(Request $request)
    {

        $ceking = tblUsers::select(
            'id','name','level','sub_level','email'
        )
        ->where([
            'token'         =>  $request->header('key'),
            'registers'     =>  0
        ])
        ->first();

        if( $ceking == null )
        {

            $countreg = 0;
            
        }
        else
        {
            $countreg = tblUserRegisters::where([
                    ['user_id', '=', $ceking->id],
                    ['created_at',  'like', '%' . date('Y-m-d', time()). '%']
                ])->count();
        }

        $count = $countreg > 2 ? 'on' : 'off';
        $messagecount = $countreg > 2 ? 'Permintaan verifikasi Akun dibatasi maksimal 3x dalam 1 hari' : '';


        //first sending
        $getsender = DB::table("auto_senders")->where([
            'type'      =>  1,
            'sub_type'  =>  1,
            'user_id'   =>  $ceking->id
        ]);

        $countsender = $getsender->count();
        
        if( $countsender === 1)
        {
            $datasender = $getsender->first();
            $firstsender = $datasender->sender_email === 0 && $datasender->status === 1 ? 'true' : 'false';
            $tokensender = $datasender->token;
        }
        else
        {
            $firstsender = 'false';
            $tokensender = '';
        }

        //RESPONSE
        $response = $ceking === null ? '' : [
            'id'=>$ceking->id,
            'name'=>$ceking->name,
            'email'=>$ceking->email, 
            'count'=>$count,
            'message'=> $messagecount,
            'firstsender'   =>  [
                'status'        =>  $firstsender,
                'token'         =>  $tokensender
            ]
        ];
        
        
        //
        $data = [
            'message'           =>  $ceking === null ? 'Key/Token not found' : '',
            'response'          =>  $response
        ];

        $status = $ceking === null ? 404 : 200;
        
        return response()->json($data, $status);
    }


    // // resend verification account
    // public function reverifaccount(Request $request)
    // {

    //     //
    //     $Config = new Config;

    //     //
    //     $thisday = date('Y-m-d', time());

    //     //
    //     $ceking = tblUsers::where([
    //         'token'         =>  $request->token,
    //         'registers'     =>  0
    //     ])->first();


    //     //hendling if null
    //     if( $ceking == null )
    //     {
    //         $data = [
    //             'message'           =>  'Token tidak valid'
    //         ];

    //         return response()->json($data, 401);
    //     }



    //     $cekregisters = tblUserRegisters::where([
    //         ['user_id', '=',    $ceking->id],
    //         ['created_at',  'like', '%' . $thisday. '%']
    //     ])->count();

    //     if( $cekregisters >= 3 )
    //     {
    //         $data = [
    //             'message'       =>  'Permintaan verifikasi Akun dibatasi maksimal 3x dalam 1 hari'
    //         ];

    //         return response()->json($data, 401);
    //     }


    //     //
    //     // call registers
    //     $dataregisters = [
    //         'user_id'       =>  $ceking->id,
    //         'type'          =>  2,
    //         'info'          =>  $request->info
    //     ];

    //     $addregisters = new \App\Http\Controllers\models\users;
    //     $addregisters = $addregisters->registers($dataregisters);
        

        

    //     //call autosenders
    //     // $infosender = [
    //     //     'user'          =>  [
    //     //         'id'                =>  $ceking->id,
    //     //         'email'             =>  $ceking->email,
    //     //         'name'              =>  $ceking->name
    //     //     ],
    //     //     'apps'          =>  [
    //     //         'name'              =>  $Config->apps()['crm']['name'],
    //     //         'url'               =>  $Config->apps()['crm']['url'],
    //     //         'url_help'          =>  $Config->apps()['crm']['url_help'],
    //     //         'url_logo'          =>  $Config->apps()['company']['url_logo'],
    //     //         'url_link'  =>  $Config->apps()['crm']['url'] . '/account/verification?token=' . md5($addregisters['id'])
    //     //     ]
    //     // ];

    //     // $infotemplate = [
    //     //     'id'            =>  10002,
    //     //     'dir'           =>  'verifaccount'
    //     // ];

    //     // $dataautoemail = [
    //     //     'user_id'           =>  $ceking->id,
    //     //     'type'              =>  1, //1. access
    //     //     'sub_type'          =>  2, //vresen erification account
    //     //     'sender_type'       =>  1, //1 send by email
    //     //     'sender_id'         =>  10001,
    //     //     'infotemplate'      =>  $infotemplate,
    //     //     'infosender'        =>  $infosender,
    //     // ];


    //     // autosender
    //     $levelroot = $Config->rootapps($request['level']);
    //     //
    //     $infoautosender = [
    //         'user'          =>  [
    //             'id'                =>  $ceking->id,
    //             'email'             =>  $ceking->email,
    //             'name'              =>  $ceking->name
    //         ],
    //         'apps'          =>  [
    //             'name'              =>  $Config->apps()['company']['name'], //$Config->apps()[$request['apps']['root']]['name'],
    //             'url'               =>  $Config->apps()[$levelroot]['url'],
    //             'url_help'          =>  $Config->apps()[$levelroot]['url_help'],
    //             'url_logo'          =>  $Config->apps()['company']['url_logo'],
    //             'url_link'  =>  $Config->apps()[$levelroot]['url'] . '/account/verification?token=' . md5($addregisters['id'])
    //         ]
    //     ];



    //     //
    //     $upautosendermail = tblAutoSenders::where([
    //         'user_id'           =>  $ceking->id,
    //         'type'              =>  1,
    //         'sub_type'          =>  1,
    //         'sender_email'      =>  0,
    //         'status'            =>  1
    //     ])
    //     ->update(['status'=>0]);
        
        

    //     //get template
    //     $gettemplate = new \App\Http\Controllers\template\email\index;
    //     $gettemplate = $gettemplate->main([
    //         'id'        =>  10002
    //     ]);
    //     $content = $gettemplate['content'];
    //     $content = str_replace('{url_home}', $infoautosender['apps']['url'], $content);
    //     $content = str_replace('{apps_name}', $infoautosender['apps']['name'], $content);
    //     $content = str_replace('{name}', $ceking->name, $content);
    //     $content = str_replace('{url}', $infoautosender['apps']['url_link'], $content );
    //     $content = str_replace('{url_help}', $infoautosender['apps']['url_help'], $content);
    //     $content = str_replace('{url_logo}', $infoautosender['apps']['url_logo'], $content);

    //     $template = [
    //         'header'    =>  [
    //             'title'     =>  $gettemplate['title'],
    //             'subject'   =>  $gettemplate['subject']
    //         ],
    //         'content'   =>  $content
    //     ];


    //     $dataautoemail = [
    //         'user_id'           =>  $ceking->id,
    //         'type'              =>  1, //1. access
    //         'sub_type'          =>  2, //vresen erification account
    //         'sender_type'       =>  1, //1 send by email
    //         'sender_id'         =>  10001,
    //         'template'          =>  $template,
    //         'infosender'        =>  $infoautosender,
    //     ];


    //     $addautosendermail = new \App\Http\Controllers\models\autosenders;
    //     $addautosendermail = $addautosendermail->email($dataautoemail);


    //     //count
    //     $getcount = tblUserRegisters::where([
    //         ['user_id', '=', $ceking->id],
    //         ['created_at',   'like', '%' . date('Y-m-d', time()) . '%']
    //     ])->count();

    //     //
    //     return response()->json([
    //         'message'=> $getcount > 2 ? 'Permintaan verifikasi Akun dibatasi maksimal 3x dalam 1 hari' : '',
    //         'response'=>'Berhasil',
    //         'count'     =>  $getcount > 2 ? 'on' : 'off',
    //         'countmessage'  =>  $getcount > 2 ? 'Permintaan verifikasi Akun dibatasi maksimal 3x dalam 1 hari' : ''
    //     ],200);
        

    // }


    // resend verification account
    public function reverifaccount(Request $request)
    {

        //
        $Config = new Config;

        //
        $thisday = date('Y-m-d', time());

        //
        $ceking = tblUsers::where([
            'token'         =>  $request->token,
            'registers'     =>  0
        ])->first();


        //hendling if null
        if( $ceking == null )
        {
            $data = [
                'message'           =>  'Token tidak valid'
            ];

            return response()->json($data, 401);
        }



        $cekregisters = tblUserRegisters::where([
            ['user_id', '=',    $ceking->id],
            ['created_at',  'like', '%' . $thisday. '%']
        ])->count();

        if( $cekregisters >= 3 )
        {
            $data = [
                'message'       =>  'Permintaan verifikasi Akun dibatasi maksimal 3x dalam 1 hari'
            ];

            return response()->json($data, 401);
        }


        $dataresend = [
            'user_id'           =>  $ceking->id,
            'info'              =>  $request->info,
            'level'             =>  $request->level,
            'name'              =>  $ceking->name,
            'email'             =>  $ceking->email
        ];

        $resend = $this->resendverificationaccount($dataresend);

        
        //count
        $getcount = tblUserRegisters::where([
            ['user_id', '=', $ceking->id],
            ['created_at',   'like', '%' . date('Y-m-d', time()) . '%']
        ])->count();

        //
        return response()->json([
            'message'=> $getcount > 2 ? 'Permintaan verifikasi Akun dibatasi maksimal 3x dalam 1 hari' : '',
            'response'=>'Berhasil',
            'count'     =>  $getcount > 2 ? 'on' : 'off',
            'countmessage'  =>  $getcount > 2 ? 'Permintaan verifikasi Akun dibatasi maksimal 3x dalam 1 hari' : '',
            'token'         =>  $resend['token']
        ],200);

    }
    
    
    public function resendverificationaccount($request)
    {

        //
        $Config = new Config;

        //
        // call registers
        $dataregisters = [
            'user_id'       =>  $request['user_id'],
            'type'          =>  2,
            'info'          =>  $request['info']
        ];

        $addregisters = new \App\Http\Controllers\models\users;
        $addregisters = $addregisters->registers($dataregisters);
        

        // autosender
        $levelroot = $Config->rootapps($request['level']);
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
                'url_link'          =>  env("URL_APP"). '/account/verification?token=' . md5($addregisters['id'])
            ]
        ];



        //
        $upautosendermail = tblAutoSenders::where([
            'type'              =>  1,
            'user_id'           =>  $request['user_id'],
            'sender_email'      =>  0,
            'status'            =>  1
        ])
        ->whereIn('sub_type', [1,2])
        ->update(['status'=>0]);
        
        

        //get template
        $gettemplate = new \App\Http\Controllers\template\email\index;
        $gettemplate = $gettemplate->main([
            'id'        =>  10002
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


        $dataautoemail = [
            'user_id'           =>  $request['user_id'],
            'type'              =>  1, //1. access
            'sub_type'          =>  2, //vresen erification account
            'sender_type'       =>  1, //1 send by email
            'sender_id'         =>  10001,
            'template'          =>  $template,
            'infosender'        =>  $infoautosender,
        ];


        $addautosendermail = new \App\Http\Controllers\models\autosenders;
        $addautosendermail = $addautosendermail->email($dataautoemail);

    
        return $addautosendermail;

    }
    
    
    // verification account
    public function verification(Request $request)
    {
        $Config = new Config;

        //
        $key = trim($request->header('key') ) ;
        // $page = trim($request->page);


        //
        $ceking = tblUserRegisters::select(
            'u.id', 'u.level', 'u.sub_level', 'u.name', 'u.registers'
        )
        ->join('users as u', 'u.id', '=', 'user_registers.user_id')
        ->where([
            'user_registers.token'         =>  $key,
            'user_registers.status'        =>  1,
            'u.registers'                  =>   0,
            'u.status'                     =>  1
        ])
        ->first();

        

        if( $ceking )
        {

            //jika di akses bukan dari halaman apps
            // if( $page != '9' )
            // {

            //     if( $ceking->level != $page )
            //     {
            //         $data = [
            //             'message'       =>  'Level tidak di ijinkan!!!'
            //         ];
    
            //         return response()->json($data, 401);
            //     }
            // }

            // //jika di akse dari halaman apps
            // if( $page == '9' && $ceking->level != 1)
            // {
            //     $data = [
            //         'message'       =>  'Level tidak di ijinkan!!!'
            //     ];

            //     return response()->json($data, 401);
            // }
            

            // if( $ceking->level > 0 ) //level not account personal
            // {
                
            //     $response = $this->verifaccountcomp($ceking);
            // }
            // else
            // {
                $response = $this->verifaccountpersonal($ceking);
            // }

            //true
            $data = [
                'message'           =>  '',
                'response'          =>  $response
            ];

            $status = 200;

        }
        else
        {
            $data = [
                'message'           =>  'Data tidak ditemukan'
            ];

            $status = 404;
        }


        return response()->json($data, $status);
    }


    //personal level
    public function verifaccountpersonal($request)
    {
        $data = [ 
            'id'                =>  $request['id'],
            'level'             =>  $request['level'],
            'sub_level'         =>  $request['sub_level'],
            'name'              =>  $request['name']
        ];

        return $data;
    }

    // company level
    public function verifaccountcomp($request)
    {

        $getconfig = tblUserConfigs::select(
            'uc.type', 'uc.name'
        )
        ->leftJoin('user_companies as uc', function($join)
        {
            $join->on('uc.id', '=', 'user_configs.company_id');
        })
        ->where([
            'user_configs.user_id'      =>  $request['id']
        ])
        ->first();

        //
        $company = [
            'type'          =>  $getconfig->type === 1 ? 'Apps' : ( $getconfig->type === 2 ? 'Produsen' : 'Distributor'),
            'name'        =>  $getconfig->name
        ];


        $data = [ 
            'id'                =>  $request['id'],
            'level'             =>  $request['level'],
            'sub_level'         =>  $request['sub_level'],
            'name'              =>  $request['name'],
            'company'           =>  $company
        ];

        return $data;
    }


    public function sendverification(Request $request)
    {
        $Config = new Config;

        // $page = trim($request->page);

        $ceking = tblUserRegisters::select(
            'u.id', 'u.level', 'u.sub_level', 'u.name', 'u.registers'
        )
        ->join('users as u', 'u.id', '=', 'user_registers.user_id')
        ->where([
            'user_registers.token'         =>  trim($request->token),
            'user_registers.status'        =>  1,
            'u.registers'                  =>  0,
            'u.status'                     =>  1
        ])
        ->first();



        if( $ceking )
        {

            //jjika page bukan 9
            // if( $page != '9')
            // {

            //     if( $ceking->level != $page )
            //     {
            //         $data = [
            //             'message'       =>  'Level tidak di ijinkan!!!'
            //         ];
    
            //         return response()->json($data, 401);
            //     }
            // }

            // // jika page 9
            // if( $page == '9' && $ceking->level != 1)
            // {
            //     $data = [
            //         'message'       =>  'Level tidak di ijinkan!!!'
            //     ];

            //     return response()->json($data, 401);
            // }

            //PROCESS
            // if( $ceking->level > 0 ) //level not account personal
            // {
                
                $response = $this->sendverificationcomp($request);
            // }
            // else
            // {
                // $response = $this->sendverificationuser($request);
            // }


            return $response;
        }
        else
        {
            $data = [
                'message'           =>  'Data tidak ditemukan'
            ];


            return response()->json($data, 404); //$verificatin;
        }

        

        
    }


    //send verification company
    public function sendverificationcomp($request)
    {
        $username = trim($request->username);
        $password = trim($request->password);
        // $page = trim($request->page);
        // $terms = trim($request->terms);


        $checkuname = tblUsers::where([
            'username'          =>  trim($username)
        ])->count();

        if( $checkuname > 0 )
        {
            $data = [
                'message'       =>  'Username telah digunakan Account lain',
                'focus'         =>  'username'
            ];

            return response()->json($data, 403);
        }


        $getuser = tblUserRegisters::select(
            'u.id', 'u.email', 'u.sub_level', 'u.company_id'
        )
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'user_registers.user_id');
        })
        ->where([
            'user_registers.token'         =>  trim($request->token)
        ])
        ->first();


        //update table user
        $upuser = tblUsers::where([
            'id'        =>  $getuser->id
        ])
        ->update([
            'username'          =>  $username,
            'password'          =>  Hash::make($password),
            'registers'         =>  1
        ]);


        // update table user config
        $upuserconfig = tblUserConfigs::where([
            'user_id'           =>  $getuser->id
        ])->update([
            'terms'             =>  1,
            'terms_date'        =>  date('Y-m-d H:i:s', time())
        ]);

        //if this level administrator
        // if( $getuser->sub_level == '1')
        // {
        //     $upcompany = tblUserCompanies::where([
        //         'id'            =>  $getuser->company_id
        //     ])
        //     ->update([
        //         'verify'        =>  1
        //     ]);
        // }

        //update tbl user registers
        $upregisters = tblUserRegisters::where([
            'user_id'       =>  $getuser->id,
            'status'        =>  1
        ])->update([
            'status'        =>  0
        ]);
        
        // login
        $datalogin = [
            'email'         =>  $getuser->email,
            'password'      =>  $password,
            'info'          =>  $request->info,
            // 'apps'          =>  $page
        ];

        $login = new \App\Http\Controllers\access\manage;
        $login = $login->truelogin($datalogin);


        return response()->json($login, 200);

    }


    public function sendverificationuser($request)
    {
        return "user";
    }



    //change password
    public function sendchangepassword(Request $request)
    {
        
        //
        $Config = new Config;

        //
        $key = $request->header('key');
        $password = trim($request->password);


        $ceking = tblResetPasswords::select(
            'u.id', 'u.email'
        )
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'reset_passwords.user_id');
        })
        ->where([
            'reset_passwords.token'         =>  $key,
            'reset_passwords.status'        =>  1
        ])->first();


        //error
        if( $ceking == null )
        {
            return response()->json([
                'message'   =>  'Key/Token tidak valid atau kadaluwarsa',
                'focus'     =>  '.fcs1'
            ], 404);
        }

        //update reset passord
        $upresetpassword = tblResetPasswords::where([
            'token'         =>  $key,
            'status'        =>  1
        ])->update([
            'reset'         =>  1,
            'status'        =>  0
        ]);

        //update table login
        $upuser = tblUsers::where([
            'id'            =>  $ceking->id
        ])
        ->update([
            'password'      =>  Hash::make($password)
        ]);


        // login
        $datalogin = [
            'email'         =>  $ceking->email,
            'password'      =>  $password,
            'apps'          =>  $request->level,
            'info'          =>  $request->info
        ];

        $login = new \App\Http\Controllers\access\manage;
        $login = $login->truelogin($datalogin);

        return $login;

    }



    public function ChangePassword(Request $request)
    {

        $email = trim($request->email);
        $password = trim($request->password);

        $check = tblUsers::from('users as u')
        ->where([
            'u.email'       =>  $email,
            'u.status'      =>  1
        ])->first();

        if( $check == null )
        {
            $data = [
                'message'           =>  'Account tidak ditemukan'
            ];

            return response()->json($data, 404);
        }


        if( $check->registers == 0 )
        {
            $data = [
                'message'           =>  'Account belum verifikasi'
            ];

            return response()->json($data, 401);
        }

        if( strlen( $password ) < 6 )
        {
            $data = [
                'message'           =>  'Password 6 - 16 karakter'
            ];

            return response()->json($data, 401);
        }


        $updatepass = tblUsers::where([
            'email'         =>  trim($request->email)
        ])
        ->update([
            'password'          =>  Hash::make($password)
        ]);


        $data = [
            'message'   =>  '',
        ];

        return response()->json($data, 200);
    }

    
    //change user account
    public function ChangeBio(Request $request)
    {

        

        $CekAccount = new Account;
        $getaccount = $CekAccount->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);
        $gender = trim($request->gender);
        $name = trim($request->name);


        //upload image
        $update = DB::table('users')
        ->where([
            'id'        =>  $getaccount['id']
        ])
        ->update([
            'name'          =>  $name,
            'gender'        =>  $gender
        ]);

        
        if( $update == null )
        {
            $status = 404;
        }
        else
        {

            $viewAccount = $CekAccount->viewtype([
                'type'      =>  'id',
                'id'        =>  $getaccount['id']
            ]);

            $gettoken = tblUserlogins::where([
                'user_id'       =>  $getaccount['id'],
                'status'        =>  1
            ])->first();
    
            $view = [
                'account'           =>  $viewAccount,
                'token'             =>  $gettoken->token_jwt,
            ];
            $status = 200;
        }


        $data = [
            'message'       =>  $status === 200 ? 'Biodata berhasil diperbaharui' : '',
            'refresh'       =>  $status === 200 ? $view : ''
        ];


        return response()->json($data, $status);
    }


    //change user password
    public function ChangeUserPassword(Request $request)
    {

        $getaccount = tblUsers::where([
            'token'     =>  $request->header('key')
        ])
        ->first();


        $old = trim($request->old);
        $password = trim($request->new);

        $cekpwd = Hash::check($old, $getaccount->password) ? 1 : 0;


        if( $cekpwd == 0)
        {
            $data = [
                'message'       =>  'Password yang Anda masukan salah',
                'focus'         =>  'old'
            ];

            return response()->json($data, 401);
        }
        //update password
        $update = DB::table('users')
        ->where([
            'id'        =>  $getaccount['id']
        ])
        ->update([
            'password'          =>  Hash::make($password),
        ]);
        
        if( $update == null )
        {
            $status = 404;
        }
        else
        {
            $status = 200;
        }


        $data = [
            'message'       =>  'Password Akun Anda berhasil di ubah'
        ];

        return response()->json($data, $status);
    }
}