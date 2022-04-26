<?php
namespace App\Http\Controllers\pelayanan\pengguna;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\lembagas as tblLembagas;
use App\users as tblUsers;

class manage extends Controller
{
    //
    public function create(Request $request)
    {
        $Config = new Config;

        //
        $email = trim($request->email);
        $phone = trim($request->phone);

        //
        $cekemillembaga = tblLembagas::where([
            'email'         =>  $email
        ])
        ->count();

        if( $cekemillembaga > 0 )
        {
            $data = [
                'response'  =>  [
                    'error'     =>  [
                        'message'   =>  'Email Admin sudah terdaftar pada Email lembaga',
                        'focus'     =>  'email'
                    ]
                ]
            ];

            return response()->json($data, 401);
        }

        //cek email 
        $cekemail = tblUsers::where([
            'email'         =>  $email
        ])
        ->count();

        if( $cekemail > 0 )
        {
            $data = [
                'response'  =>  [
                    'error'     =>  [
                        'message'   =>  'Email telah digunakan akun lain',
                        'focus'     =>  'email'
                    ]
                ]
            ];

            return response()->json($data, 401);
        }

        //check phone
        $cekphone = tblUsers::where([
            'phone'         =>  $phone
        ])
        ->count();

        if( $cekphone > 0 )
        {
            $data = [
                'response'  =>  [
                    'error'     =>  [
                        'message'   =>  'No Whatsapp telah digunakan akun lain',
                        'focus'     =>  'phone'
                    ]
                ]
            ];

            return response()->json($data, 401);
        }

        //GET LEMBAGA
        $gelembaga = tblLembagas::where([
            'id'        =>  trim($request['lembaga_id'])
        ])
        ->first();


        //CRETAE
        $dataaccount = [
            'email'         =>  trim($request->email),
            'name'          =>  trim($request->name),
            'phone'         =>  trim($request->phone),
            'phone_code'    =>  62,
            'gender'        =>  trim($request->gender),
            'level'         =>  2,
            'sub_level'     =>  $gelembaga->type,
            'type'          =>  2,
            'info'          =>  '',
            'company_id'    =>  $gelembaga->id,
            'password'      =>  '',
            'username'      =>  '',
            'page'          =>  '1',
            'user_id'       =>  trim($request->user_id)
        ];

        $create = new \App\Http\Controllers\models\users;
        $create = $create->create($dataaccount);

        //
        $data = [
            'message'       =>  'Data berhasil disimpan',
            'response'      =>  [
                'token'         =>  $create['token']
            ]
        ];

        return response()->json($data, 200);
    }
}