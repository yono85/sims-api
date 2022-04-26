<?php
namespace App\Http\Controllers\manage\pengguna;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
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
        $type = trim($request->type);

        if( $type == 'add')
        {
            //CHECK EMAIL
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
    
    
            // CHECK PHONE
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
    
            //CRETAE
            $dataaccount = [
                'email'         =>  trim($request->email),
                'name'          =>  trim($request->name),
                'phone'         =>  trim($request->phone),
                'phone_code'    =>  62,
                'gender'        =>  trim($request->gender),
                'level'         =>  1,
                'sub_level'     =>  trim($request->sublevel),
                'type'          =>  1,
                'info'          =>  '',
                'company_id'    =>  trim($request->company_id),
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

        //SUNTING
        $id = trim($request->id);
        $cekemail = tblUsers::where([
            ['id',  '!=', $id],
            ['email', '=', $email]
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


        // CHECK PHONE
        $cekphone = tblUsers::where([
            ['id', '!=', $id],
            ['phone', '=', $phone]
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

        //update users
        $update = new \App\Http\Controllers\models\users;
        $update = $update->updateusers($request);

        $data = [
            'message'       =>  'Data berhasil diperbaharui'
        ];

        return response()->json($data, 200);
        
    }

    //view
    public function view(Request $request)
    {
        $Config = new Config;

        //
        $getdata = tblUsers::where([
            'id'    =>  $request->id
        ]);

        $count = $getdata->count();

        if($count == 0)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 200);
        }


        $viewdata = $getdata->first();

        //
        $data = [
            'message'       =>  '',
            'response'      =>  [
                'id'            =>  $viewdata->id,
                'name'          =>  $viewdata->name,
                'gender'        =>  $viewdata->gender,
                'email'         =>  $viewdata->email,
                'phone'         =>  $viewdata->phone,
                'sublevel'      =>  $viewdata->sub_level
            ]
        ];

        return response()->json($data, 200);
    }
}