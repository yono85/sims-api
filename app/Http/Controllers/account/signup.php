<?php
namespace App\Http\Controllers\account;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\users as tblUsers;
use App\user_registers as tblUserRegisters;
use App\auto_senders as tblAutoSenders;
use App\Http\Controllers\config\index as Config;
use Illuminate\Support\Facades\Hash;
use App\lembagas as tblLembagas;
use DB;

class signup extends Controller
{
    //
    public function main($request)
    {
        //
        $Config = new Config;


        $datauser = [
            'name'          =>  $request['name'],
            'email'         =>  $request['email'],
            'password'      =>  $request['password'],
            'username'      =>  '',
            'level'         =>  $request['level'] ? $request['level'] : 0,
            'sub_level'     =>  $request['level'] ? $request['sub_level'] : 0,
            'company_id'       =>  $request['company_id'] ? $request['company_id'] : 0,
            'admin_id'      =>  $request['admin_id'] ? $request['admin_id'] : 0,
            'gender'        =>  0
        ];

        $addnewuser = new \App\Http\Controllers\models\users;
        $addnewuser = $addnewuser->new($datauser);
        // // end add user



        //add registers
        $dataregister = [
            'user_id'           =>  $addnewuser['id'],
            'info'              =>  $request['info'],
            'type'              =>  2    
        ];

        $addnewregister = new \App\Http\Controllers\models\users;
        $addnewregister = $addnewregister->registers($dataregister);



        //insert table automation
        //field info sender
        $infoautosender = [
            'user'          =>  [
                'id'                =>  $addnewuser['id'],
                'email'             =>  $request['email'],
                'name'              =>  $request['name']
            ],
            'apps'          =>  [
                'name'              =>  env("APP_NAMELABEL"),
                'url'               =>  env("URL_APP"),
                'url_help'          =>  env("URL_APP"),
                'url_logo'          =>  $Config->apps()['company']['url_logo'],
                'url_link'  =>  env("URL_APP") . '/account/verification?token=' . md5($addnewregister['id'])
            ]
        ];

        //
        $infotemplate = [
            'id'       =>  10001,
            'dir'      =>  'verifaccount'
        ];


        //
        $dataautosender = [
            'user_id'           =>  $addnewuser['id'],
            'type'              =>  1, //1. access
            'sub_type'          =>  1, //1. verif account,
            'sender_type'       =>  1, //1. send by email
            'sender_id'         =>  10001,
            'infotemplate'      =>  $infotemplate,
            'infosender'        =>  $infoautosender,
        ];
        

        $addnewautosender = new \App\Http\Controllers\models\autosenders;
        $addnewautosender = $addnewautosender->email($dataautosender);

        //
        
        try{
            
            return ['message'=>'','response'=>'/registers/success?token=' . md5($addnewuser['id']) ];
        }
        catch  (Exception $e)
        {
            return ['message'=>$e->getMessage()];
        }

        
        
    }



    //SIGN UP VER 2
    public function newmain(Request $request)
    {
        $Config = new Config;
        $name = trim($request->name);
        $register_type = trim($request->register_type);
        $type = trim($request->type);
        $npwp = $Config->number(trim($request->npwp));
        $kumham = trim($request->kumham_no);
        $phone = trim($request->phone);
        $email = trim($request->email);
        $adminemail = trim($request->admin_email);

        //check same name
        $checkname = tblLembagas::where([
            'name'      =>  $name
        ])->count();

        if( $checkname > 0 )
        {
            $data = [
                'message'   =>  'Nama Lembaga sudah ada sebelumnya',
                'focus'     =>  'name',
                'area'      =>  0
            ];

            return response()->json($data, 401);
        }

        
        
        //check same npwp
        if( $type != '2')
        {
            //CHECK KUMHAM
            $checkkumham = tblLembagas::where([
                'kumham'        =>  $kumham
            ])->count();

            if( $checkkumham > 0 )
            {
                $data = [
                    'message'   =>  'No. Akta Kumham sudah terdaftar',
                    'focus'     =>  'kumham_no',
                    // 'area'      =>  0
                ];
    
                return response()->json($data, 401);
            }

            // CHECK NPWP
            $checknpwp = tblLembagas::where([
                'npwp'      =>  $npwp
            ])->count();
    
            if( $checknpwp > 0 )
            {
                $data = [
                    'message'   =>  'NPWP sudah terdaftar',
                    'focus'     =>  'npwp',
                    // 'area'      =>  0
                ];
    
                return response()->json($data, 401);
            }
        }

        //check phone
        if( $phone != '')
        {

            $checkphone = tblLembagas::where([
                'phone'      =>  $phone
            ])->count();
    
            if( $checkphone > 0 )
            {
                $data = [
                    'message'   =>  'Nomor Telp atau HP sudah terdaftar',
                    'focus'     =>  'phone',
                    // 'area'      =>  1
                ];
    
                return response()->json($data, 401);
            }

        }

        if($email != '')
        {
            //check email
            $checkemail = tblLembagas::where([
                'email'      =>  $email
            ])->count();
    
            if( $checkemail > 0 )
            {
                $data = [
                    'message'   =>  'Email Lembaga sudah terdaftar',
                    'focus'     =>  'email',
                    // 'area'      =>  2
                ];
    
                return response()->json($data, 401);
            }

        }



        $checkemaillembaga = tblLembagas::where([
            'email'      =>  $adminemail
        ])->count();

        if( $checkemaillembaga > 0 )
        {
            $data = [
                'message'   =>  'Email sudah digunakan oleh Admin',
                'focus'     =>  'email',
                // 'area'      =>  4
            ];

            return response()->json($data, 401);
        }


        $checkemailuser = tblUsers::where([
            'email'      =>  $adminemail
        ])->count();

        if( $checkemailuser > 0 )
        {
            $data = [
                'message'   =>  'Email Admin sudah digunakan',
                'focus'     =>  'admin_email',
                // 'area'      =>  4
            ];

            return response()->json($data, 401);
        }


        //CREATE NEW 
        $create = new \App\Http\Controllers\models\lembaga;
        $create = $create->new($request);

        $data = [
            'message'       =>  'Data berhasil',
            'response'       =>  [
                'link'          =>  '/registers/success?token=' . $create['token']
            ]
        ];

        return response()->json($data,200);


    }


}