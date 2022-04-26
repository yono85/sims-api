<?php
namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\Http\Controllers\access\manage as Refresh;
use App\users as tblUsers;
use App\user_registers as tblUserRegisters;
use App\user_configs as tblUserConfigs;


class manage extends Controller
{
    //
    public function create(Request $request)
    {

        $type = trim($request->type);


        if( $type == 'add')
        {
            $data = $this->add($request);
        }
        else
        {
            $data = $this->edit($request);
        }


        return $data;

    }


    //ADD ACCOUNT
    public function add($request)
    {
        $Config = new Config;

        $account = new \App\Http\Controllers\account\index;
        $account = $account->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);

        //check email same
        $checkemail = tblUsers::where([
            'email'         =>  trim($request->email)
        ])->first();

        if( $checkemail != null )
        {
            return response()->json([
                'response'      =>  [
                    'error'         =>  [
                        'message'           =>  'Alamat Email telah terdaftar',
                        'focus'             =>  'email'
                    ]
                ],
                // 'refresh'       =>  $Refresh
            ],401);
        }

        // check phone same
        $checkphone = tblUsers::where([
            'phone'         =>  trim((int)$request->phone)
        ])->first();

        
        if( $checkphone != null )
        {
            return response()->json([
                'response'      =>  [
                    'error'         =>  [
                        'message'           =>  'Nomor Whatsapp telah terdaftar',
                        'focus'             =>  'phone'
                    ]
                ],
                // 'refresh'       =>  $Refresh
            ],401);
        }


        $data = [
            'name'          =>  $request['name'],
            'email'         =>  $request['email'],
            'password'      =>  '',
            'username'      =>  '',
            'level'         =>  $account['level'],
            'sub_level'     =>  $request['sublevel'],
            'gender'        =>  $request['gender'],
            'phone'         =>  $request['phone'],
            'phone_code'    =>  '62',
            'company_id'    =>  $account['config']['company_id'],
            'admin_id'      =>  $account['id'],
            'info'          =>  '',
            'type'          =>  1
        ];

        $create = new \App\Http\Controllers\models\users;
        $create = $create->create($data);

        //registers
        $response = [
            'message'       =>  'Data berhasil di Simpan',
            'response'      =>  $create
        ];

        return response()->json($response, 200);

    }


    

    public function edit(Request $request)
    {

        $Config = new Config;

        //
        // $Refresh = new Refresh;
        // $Refresh = $Refresh->refresh();


        $checkemail = tblUsers::where([
            ['id', '!=', $request->id],
            ['email', '=', trim($request->email)]
        ])->first();


        // check email same
        if( $checkemail != null )
        {
            return response()->json([
                'response'      =>  [
                    'error'         =>  [
                        'message'           =>  'Alamat Email telah terdaftar',
                        'focus'             =>  'email'
                    ]
                ],
                // 'refresh'       =>  $Refresh
            ],401);
        }


        $checkphone = tblUsers::where([
            ['id', '!=', $request->id],
            ['phone', '=', trim((int)$request->phone)]
        ])->first();

        
        if( $checkphone != null )
        {
            return response()->json([
                'response'      =>  [
                    'error'         =>  [
                        'message'           =>  'Nomor Whatsapp telah terdaftar',
                        'focus'             =>  'phone'
                    ]
                ],
                // 'refresh'       =>  $Refresh
            ],401);
        }


        //update
        $getupdate = tblUsers::where([
            'id'        =>  $request->id
        ])
        ->update([
            'name'              =>  trim($request->name),
            'gender'            =>  trim($request->gender),
            'phone'             =>  trim((int)$request->phone),
            'sub_level'         =>  trim($request->sublevel)
        ]);


        $response = [
            // 'refresh'       =>  $Refresh,
            'messsage'           =>  $getupdate ? 'Data berhasil disunting' : 'Data tidak ada perubahan',
            'id'            =>  $request->id
        ];

        return response()->json($response, 200);

    }



    public function changestatus(Request $request)
    {

        $admin_id = trim($request->admin_id);
        $status = trim($request->status);


        $upuser = tblUsers::where([
            'id'            =>  $admin_id
        ])
        ->update([
            'status'        =>  $status === 'on' ? 1 : 0
        ]);



        $getuser = tblUsers::where([
            'id'            =>  $admin_id
        ])
        ->first();

        
        $status = $getuser->status === 0 ? 'mp' : ( $getuser->registers === 0 ? 'mv' : 'sc');
        //
        $data = [
            'message'           =>  '',
            'status'            =>  $status,
            'admin'             =>  $admin_id
        ];


        return response()->json($data, 200);
    }


    public function view(Request $request)
    {
        $id = trim($request->id);

        $getdata = tblUsers::where([
            'id'            =>  $id
        ])
        ->first();

        if( $getdata == null)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];
    
            return response()->json($data, 404);

        }

        $data = [
            'message'       =>  '',
            'response'      =>  [
                'id'            =>  $getdata->id,
                'name'          =>  $getdata->name,
                'gender'        =>  $getdata->gender,
                'gender_label'  =>  $getdata->gender === 1 ? 'male' : 'female',
                'email'         =>  $getdata->email,
                'phone'         =>  $getdata->phone,
                'sublevel'      =>  $getdata->sub_level
            ]
        ];

        return response()->json($data, 200);
    }


    //resend verification
    public function resendverification(Request $request)
    {
        $Config = new Config;

        //
        $user_id = $request->id;

        //checking table reset password
        $check = tblUserRegisters::where([
            ['user_id', '=', $user_id],
            ['created_at','like', '%' . date('Y-m-d', time()). '%'] 
        ])->count();


        //
        if( $check >= 3 )
        {
            $data = [
                'message'       =>  'Permintaan kirim ulang verifikasi akun sudah mencapai batas (maks 3x) di hari ini.'
            ];

            return response()->json($data, 400);
        }
        // end check 

        $getaccount = new \App\Http\Controllers\account\index;
        $getaccount = $getaccount->viewtype([
            'type'      =>  'id',
            'id'        =>  $user_id
        ]);

        
        $dataresend = [
            'user_id'           =>  $user_id,
            'info'              =>  '',
            'level'             =>  $getaccount['level'],
            'name'              =>  $getaccount['name'],
            'email'             =>  $getaccount['email']
        ];

        $addresend = new \App\Http\Controllers\account\manage;
        $addresend = $addresend->resendverificationaccount($dataresend);

        //
        $data = [
            'message'       =>  'Permintaan ulang verifikasi akun berhasil dikirim ke alamat email ' . $getaccount['email']
        ];


        return response()->json($data, 200);
    }


    //USER ORDERS
    public function userOrders(Request $request)
    {
        $companyid = trim($request->id);

        $getlist = tblUsers::where([
            'company_id'    =>  $companyid,
            'registers'     =>  1
        ]);

        if( $getlist->count() > 0 )
        {
            $getlist = $getlist->orderBy('sub_level', 'asc')
            ->get();

            foreach($getlist as $row)
            {
                $list[] = [
                    'id'        =>  $row->id,
                    'name'      =>  $row->name,
                    'sublevel'  =>  $row->sub_level
                ];
            }

            $data =[
                'message'   =>  '',
                'list'      =>  $list
            ];

            return response()->json($data, 200);
        }

        return response()->json([
            'message'       =>  'Data tidak ditemukan'
        ], 404);
        
    }

}