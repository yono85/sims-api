<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\lembagas as tblLembagas;
use App\Http\Controllers\config\index as Config;
use App\users as tblUsers;

class lembaga extends Controller
{
    //CREATE NEW
    public function new($request)
    {
        $Config = new Config;
        $bps = explode(",", $request->address_array);
        $bpslabel = explode(", ", $request->city);

        //
        $newid = $Config->createnewidnew([
            'value'         =>  tblLembagas::count(),
            'length'        =>  9
        ]);

        $token = md5($newid);

        //owner
        $owner = [
            'ketua'     =>  [
                'name'      =>  trim($request->ketua),
                'phone'     =>  trim($request->ketua_phone)
            ],
            'sekertaris'    =>  trim($request->sekertaris),
            'bendahara'     =>  trim($request->bendahara)
        ];
        

        //adddress
        $address = [
            'name'      =>  trim($request->address),
            'provinsi'  =>  $bpslabel[2],
            'city'      =>  $bpslabel[1],
            'kecamatan' =>  $bpslabel[0],
            'kodepos'   =>  trim($request->kodepos),
            'kelurahan' =>  trim($request->kelurahan)
        ];

        //field
        $field = [
            'domisili'      =>  [
                'no'            =>  '',
                'date'          =>  ''
            ],
            'sertif'        =>  [
                'no'            =>  '',
                'date'          =>  ''
            ],
            'operasional'   =>  [
                'no'            =>  '',
                'date'          =>  ''
            ],
            'bank'          =>  [
                'name'          =>  '',
                'norek'         =>  '',
                'owner'         =>  ''
            ]
        ];

        if(trim($request->page == '1'))
        {
            $verify = $request->verification;
            $verif_user = ($request->verification == '0' ? 0 : $request->user_id);
            $verif_date = ($request->verification == '0' ? '' : date('Y-m-d', time()));
        }
        else
        {
            $verify = 0;
            $verif_user = 0;
            $verif_date = '';
        }



        // ADD NEW
        $addnew                 =   new tblLembagas;
        $addnew->id             =   $newid;
        $addnew->token          =   $token;
        $addnew->type           =   trim($request->type);
        $addnew->search         =   trim($request->name) . ';' . $Config->number(trim($request->npwp)) . ';' . trim($request->email);
        $addnew->name           =   trim($request->name);
        $addnew->kumham         =   trim($request->kumham_no);
        $addnew->kumham_tgl     =   trim($request->kumham_tgl);
        $addnew->npwp           =   $Config->number(trim($request->npwp));
        $addnew->phone          =   trim($request->phone);
        $addnew->email          =   trim($request->email);
        $addnew->owner          =   json_encode($owner);
        $addnew->provinsi       =   $bps[0];
        $addnew->city           =   $bps[1];
        $addnew->kecamatan      =   $bps[2];
        $addnew->address        =   json_encode($address);
        $addnew->field          =   json_encode($field);
        $addnew->complete       =   0;
        $addnew->verify         =   $verify;
        $addnew->verify_user    =   $verif_user;
        $addnew->verify_date    =   $verif_date;
        $addnew->user_id        =   trim($request->page) ===  '0' ? 0 : trim($request->user_id);
        $addnew->status         =   1;
        $addnew->save();

        //create account if page = 0
        if($request->admin_email != '')        
        {
            $cat = $request->type;
            $cat = $cat === '1' ? '1' : ($cat === '2' ? '2' : '3');

            $datacreateaccount = [
                'email'     =>  trim($request->admin_email),
                'name'          =>  trim($request->admin_name),
                'phone'         =>  0,
                'gender'        =>  1,
                'sub_level'     =>  trim($request->type),
                'register_type'          =>  trim($request->register_type),
                'info'          =>  trim($request->info),
                'lembaga_id'     =>  $newid,
                'categori'      =>  $cat,
                'type'          =>  trim($request->type)
            ];

            $createaccount = $this->createaccount($datacreateaccount);
            return $createaccount;
        }

    }


    //UPDATE
    public function update($request)
    {
        $Config = new Config;

        //
        $bps = explode(",", $request->address_array);
        $bpslabel = explode(", ", $request->city);

        //
        $owner = [
            'ketua'     =>  [
                'name'          =>  trim($request->ketua),
                'phone'         =>  trim($request->ketua_phone)
            ],
            'sekertaris'    =>  trim($request->sekertaris),
            'bendahara'     =>  trim($request->bendahara)
        ];

        //adddress
        $address = [
            'name'      =>  trim($request->address),
            'provinsi'  =>  $bpslabel[2],
            'city'      =>  $bpslabel[1],
            'kecamatan' =>  $bpslabel[0],
            'kodepos'   =>  trim($request->kodepos),
            'kelurahan' =>  trim($request->kelurahan)
        ];

        $verify = $request->verification;
        $verif_user = ($request->verification == '0' ? 0 : $request->user_id);
        $verif_date = ($request->verification == '0' ? '' : date('Y-m-d', time()));

        $update = tblLembagas::where([
            'id'        =>  $request->id
        ])
        ->update([
            'type'      =>  trim($request->categori),
            'search'    =>  trim($request->name) . ';' . $Config->number(trim($request->npwp)) . ';' . trim($request->email),
            'name'      =>  trim($request->name),
            'kumham'    =>  trim($request->kumham),
            'kumham_tgl'    =>  trim($request->kumham_tgl),
            'npwp'      =>  $Config->number(trim($request->npwp)),
            'phone'          =>   trim($request->phone),
            'email'          =>   trim($request->email),
            'owner'          =>   json_encode($owner),
            'provinsi'       =>   $bps[0],
            'city'           =>   $bps[1],
            'kecamatan'      =>   $bps[2],
            'address'        =>   json_encode($address),
            'verify'         =>   $verify,
            'verify_user'    =>   $verif_user,
            'verify_date'    =>   $verif_date
            
        ]);
    }

    //CREATE ACCOUNT
    public function createaccount($request)
    {
        //create account
        $dataaccount = [
            'email'         =>  $request['email'],
            'name'          =>  $request['name'],
            'phone'         =>  $request['phone'],
            'phone_code'    =>  62,
            'gender'        =>  $request['gender'],
            'level'         =>  2,
            'sub_level'     =>  $request['categori'],
            'type'          =>  $request['type'],
            'info'          =>  $request['info'],
            'company_id'    =>  $request['lembaga_id'],
            'register_type' =>  $request['register_type'],
            'password'      =>  '',
            'username'      =>  ''
        ];

        $createaccount = new \App\Http\Controllers\models\users;
        $createaccount = $createaccount->createnew($dataaccount);

        $updatelembaga = tblLembagas::where([
            'id'        =>  $request['lembaga_id']
        ])
        ->update([
            'user_id'       =>  $createaccount['id']
        ]);
        
        return $createaccount;
    }

}   