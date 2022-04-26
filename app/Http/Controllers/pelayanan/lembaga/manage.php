<?php
namespace App\Http\Controllers\pelayanan\lembaga;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\lembagas as tblLembagas;

class manage extends Controller
{

    // CREATE
    public function create(Request $request)
    {
        $Config = new Config;

        $type = trim($request->type);

        //
        $name = trim($request->name);
        $npwp = $Config->number(trim($request->npwp));
        $phone = trim($request->phone);
        $email = trim($request->email);

        //CREATE NEW
        if( $type === 'add')
        {
            //check same name
            $checkname = tblLembagas::where([
                'name'      =>  $name
            ])->count();

            if( $checkname > 0 )
            {
                $data = [
                    'response'  =>  [
                        'error'     =>  [
                            'focus'     =>  'name',
                            'message'   =>  'Nama Lembaga sudah ada sebelumnya'
                        ]
                    ]
                ];

                return response()->json($data, 401);
            }


            //check same npwp
            $checknpwp = tblLembagas::where([
                'npwp'      =>  $npwp
            ])->count();

            if( $checknpwp > 0 )
            {

                $data = [
                    'response'  =>  [
                        'error'     =>  [
                            'focus'     =>  'npwp',
                            'message'   =>  'NPWP sudah terdaftar'
                        ]
                    ]
                ];
                return response()->json($data, 401);
            }

            //check phone
            $checkphone = tblLembagas::where([
                'phone'      =>  $phone
            ])->count();

            if( $checkphone > 0 )
            {
                $data = [
                    'response'  =>  [
                        'error'     =>  [
                            'focus'     =>  'phone',
                            'message'   =>  'Nomor Telp atau HP sudah terdaftar'
                        ]
                    ]
                ];
                return response()->json($data, 401);
            }


            //check email
            $checkemail = tblLembagas::where([
                'email'      =>  $email
            ])->count();

            if( $checkemail > 0 )
            {
                $data = [
                    'response'  =>  [
                        'error'     =>  [
                            'focus'     =>  'email',
                            'message'   =>  'Email Lembaga sudah terdaftar'
                        ]
                    ]
                ];

                return response()->json($data, 401);
            }

            //END CLEAR ERRORS

            //CREATE
            $create = new \App\Http\Controllers\models\lembaga;
            $create = $create->new($request);

            $data = [
                'message'       =>  'Lembaga berhasil ditambahkan'
            ];

            return response()->json($data,200);

        }


        //SUNTING
        // CHECK BEFORE SUNTING
        $update = $this->sunting($request);
        return $update;

    }


    public function sunting($request)
    {
        $Config = new Config;
        $id = trim($request->id);

        $name = trim($request->name);
        $npwp = $Config->number(trim($request->npwp));
        $phone = trim($request->phone);
        $email = trim($request->email);

        //check same name
        $checkname = tblLembagas::where([
            ['id', '!=', $id],
            ['name', '=', $name]
        ])->count();

        if( $checkname > 0 )
        {
            $data = [
                'response'  =>  [
                    'error'     =>  [
                        'focus'     =>  'name',
                        'message'   =>  'Nama Lembaga sudah ada sebelumnya'
                    ]
                ]
            ];

            return response()->json($data, 401);
        }


        //check same npwp
        $checknpwp = tblLembagas::where([
            ['id', '!=', $id],
            ['npwp', '=', $npwp]
        ])->count();

        if( $checknpwp > 0 )
        {

            $data = [
                'response'  =>  [
                    'error'     =>  [
                        'focus'     =>  'npwp',
                        'message'   =>  'NPWP sudah terdaftar'
                    ]
                ]
            ];
            return response()->json($data, 401);
        }

        //check phone
        $checkphone = tblLembagas::where([
            ['id', '!=', $id],
            ['phone', '=', $phone]
        ])->count();

        if( $checkphone > 0 )
        {
            $data = [
                'response'  =>  [
                    'error'     =>  [
                        'focus'     =>  'phone',
                        'message'   =>  'Nomor Telp atau HP sudah terdaftar'
                    ]
                ]
            ];
            return response()->json($data, 401);
        }


        //check email
        $checkemail = tblLembagas::where([
            ['id', '!=', $id],
            ['email', '=', $email]
        ])->count();

        if( $checkemail > 0 )
        {
            $data = [
                'response'  =>  [
                    'error'     =>  [
                        'focus'     =>  'email',
                        'message'   =>  'Email Lembaga sudah terdaftar'
                    ]
                ]
            ];

            return response()->json($data, 401);
        }


        $update = new \App\Http\Controllers\models\lembaga;
        $update = $update->update($request);

        $data = [
            'message'       =>  'Data berhasil diperbaharui'
        ];

        return response()->json($data, 200);

    }

    //
    public function view(Request $request)
    {
        $Config = new Config;

        //
        $getdata = tblLembagas::where([
            'id'        =>  $request->id
        ]);

        $count = $getdata->count();

        //
        if( $count == 0)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }

        //
        $getview = $getdata->first();

        //
        $data =[
            'message'   =>  '',
            'response'  =>  [
                'id'        =>  $getview->id,
                'type'      =>  $getview->type,
                'name'      =>  $getview->name,
                'npwp'      =>  $getview->npwp,
                'phone'     =>  $getview->phone,
                'email'     =>  $getview->email,
                'owner'         =>  json_decode($getview->owner),
                'address'       =>  json_decode($getview->address),
                'address_array' =>  $getview->provinsi . ',' . $getview->city . ',' . $getview->kecamatan,
                'verify'        =>  $getview->verify,
            ]
        ];

        return response()->json($data, 200);

    }
}