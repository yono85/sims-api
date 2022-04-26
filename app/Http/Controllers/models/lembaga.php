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
            'ketua'     =>  trim($request->ketua),
            'sekertaris'    =>  trim($request->sekertaris),
            'bendahara'     =>  trim($request->bendahara)
        ];

        //adddress
        $address = [
            'name'      =>  trim($request->address),
            'provinsi'  =>  $bpslabel[2],
            'city'      =>  $bpslabel[1],
            'kecamatan' =>  $bpslabel[0],
            'kodepos'   =>  trim($request->kodepos)
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
        $addnew->type           =   trim($request->categori);
        $addnew->search         =   trim($request->name) . ';' . $Config->number(trim($request->npwp)) . ';' . trim($request->email);
        $addnew->name           =   trim($request->name);
        $addnew->npwp           =   $Config->number(trim($request->npwp));
        $addnew->phone          =   trim($request->phone);
        $addnew->email          =   trim($request->email);
        $addnew->owner          =   json_encode($owner);
        $addnew->provinsi       =   $bps[0];
        $addnew->city           =   $bps[1];
        $addnew->kecamatan      =   $bps[2];
        $addnew->address        =   json_encode($address);
        $addnew->verify         =   $verify;
        $addnew->verify_user    =   $verif_user;
        $addnew->verify_date    =   $verif_date;
        $addnew->user_id        =   trim($request->page) ===  '0' ? 0 : trim($request->user_id);
        $addnew->status         =   1;
        $addnew->save();

        //create account if page = 0
        if($request->type == '0')        
        {

            $datacreateaccount = [
                'useremail'     =>  trim($request->useremail),
                'name'          =>  trim($request->username),
                'phone'         =>  trim($request->userphone),
                'gender'        =>  trim($request->gender),
                'sub_level'     =>  trim($request->categori),
                'type'          =>  trim($request->type),
                'info'          =>  trim($request->info),
                'lembaga_id'     =>  $newid
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
            'ketua'     =>  trim($request->ketua),
            'sekertaris'    =>  trim($request->sekertaris),
            'bendahara'     =>  trim($request->bendahara)
        ];

        //adddress
        $address = [
            'name'      =>  trim($request->address),
            'provinsi'  =>  $bpslabel[2],
            'city'      =>  $bpslabel[1],
            'kecamatan' =>  $bpslabel[0],
            'kodepos'   =>  trim($request->kodepos)
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
            'email'         =>  $request['useremail'],
            'name'          =>  $request['username'],
            'phone'         =>  $request['userphone'],
            'phone_code'    =>  62,
            'gender'        =>  $request['gender'],
            'level'         =>  2,
            'sub_level'     =>  $request['categori'],
            'type'          =>  $request['type'],
            'info'          =>  $request['info'],
            'company_id'    =>  $request['lembaga_id'],
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