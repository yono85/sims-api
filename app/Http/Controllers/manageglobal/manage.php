<?php
namespace App\Http\Controllers\manageglobal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\user_companies as tblUserCompanies;
use App\app_metode_payments as tblAppMetodePayments;
use App\app_bank_lists as tblAppBankLists;
use App\app_shiping_origins as tblAppShipingOrigins;
use App\users as tblUsers;
use App\Http\Controllers\data\orders\index as ComponentOrders;
use App\Http\Controllers\account\index as account;
use DB;

class manage extends Controller
{
    //
    public function detail(Request $request)
    {

        $Config = new Config;
        $ComponentOrders = new ComponentOrders;

        ///
        $id = trim($request->id);
        $type = trim($request->type);


        //
        $cek = tblUserCompanies::where([
            'id'     =>  $id
        ])
        ->count();

        if( $cek == 0)
        {
            $data = [
                'message'           =>  'Data Not Found'
            ];

            return response()->json($data, 404);

        }


        $getdata = tblUserCompanies::from('user_companies as uc')
        ->select(
            'uc.id', 'uc.type', 'uc.name', 'uc.nosurat', 'uc.created_at as date', 'uc.expire_payment', 'uc.verify', 'uc.contact',
            'uc.owner', 'uc.owner_contact', 'uc.order_uniqnum as uniqnum',
            'ut.name as type_name',
            'uc.address', 'uc.provinsi','uc.city', 'uc.kecamatan', 'uc.kodepos',
            'u.name as admin',
            'aop.name as provinsi_name',
            'aoc.name as city_name', 'aoc.type as city_type',
            'aok.name as kecamatan_name',
            'adm.id as admin_id', 'adm.level as admin_level', 'adm.sub_level as admin_sublevel', 'adm.name as admin_name', 'adm.email as admin_email', 'adm.phone as admin_phone'
        )
        ->leftJoin('company_types as ut', function($join)
        {
            $join->on('ut.id', '=', 'uc.type');
        })
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'uc.user_id');
        })
        ->leftJoin('app_origin_provinsis as aop', function($join)
        {
            $join->on('aop.id', '=', 'uc.provinsi');
        })
        ->leftJoin('app_origin_cities as aoc', function($join)
        {
            $join->on('aoc.id', '=', 'uc.city');
        })
        ->leftJoin('app_origin_kecamatans as aok', function($join)
        {
            $join->on('aok.id', '=', 'uc.kecamatan');
        })
        ->leftJoin('users as adm', function($join)
        {
            $join->on('adm.company_id', '=', 'uc.id')
            ->where(['adm.sub_level'=>1,'adm.status'=>1]);
        })
        ->where([
            'uc.id'          =>  $id
        ])
        ->first();

        $admin = explode(' ', $getdata->admin);


        $response = [
            'id'            =>  $getdata->id,
            'type'          =>  $getdata->type,
            'type_name'          =>  $getdata->type_name,
            'name'          =>  $getdata->name,
            'nosurat'       =>  $getdata->nosurat,
            'admin'         =>  $admin[0],
            'contact'       =>  json_decode($getdata->contact),
            'owner'         =>  $getdata->owner,
            'owner_contact' =>  json_decode($getdata->owner_contact),
            'date'          =>  $Config->timeago($getdata->date),
            'uniqnum'      =>  $getdata->uniqnum,  
            'address'       =>  [
                'name'          =>  $getdata->address,
                'kodepos'       =>  $getdata->kodepos,
                'provinsi'      =>  $getdata->provinsi_name,
                'city'          =>  $getdata->city_type . '. ' . ucwords(strtolower($getdata->city_name)),
                'kecamatan'     =>  'Kec. ' . ucwords(strtolower($getdata->kecamatan_name)),
                'array'     =>  $getdata->provinsi . ',' . $getdata->city . ',' . $getdata->kecamatan,
            ],
            'expire'        =>  $getdata->expire_payment,
            'verify'        =>  $getdata->verify,
            'administrator' =>  [
                'id'            =>  $getdata->admin_id,
                'name'          =>  $getdata->admin_name,
                'email'         =>  $getdata->admin_email,
                'phone'         =>  $getdata->admin_phone,
                'level'         =>  $getdata->admin_level,
                'sublevel'      =>  $getdata->admin_sublevel
            ],
            'bank_list'         =>  $ComponentOrders->BankList(['company_id'=>$id]),
            'origin_list'       =>   $ComponentOrders->OriginList(['company_id'=>$id]),
            // 'distributor_price'       =>   $ComponentOrders->PriceDistributorList(['company_id'=>$id]) 
        ];

        $banklist = new \App\Http\Controllers\data\orders\index;
        $data = [
            'message'           =>  '',
            'response'          =>  $response
        ];


        return response()->json($data, 200);
    }


    public function bank(Request $request)
    {
        $type = trim($request->type);

        if( $type === 'add' )
        {
            $data = $this->AddBank($request);
        }
        else
        {
            $data = $this->EditBank($request);
        }

        return $data;

    }


    public function AddBank($request)
    {
        $account = new account;
        $account = $account->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);

        //
        $Config = new Config;

        //
        $cekbank = tblAppMetodePayments::where([
            'account_norek' =>  $request->bank_norek,
            'status'        =>  1
        ])
        ->count();

        if( $cekbank > 0 )
        {
            $data = [
                'message'       =>  'Nomor Rekening sudah digunakan',
                'focus'         =>  'bank_norek'
            ];

            return response()->json($data, 401);
        }

        


        $newid = $Config->createnewidnew([
            'value'         =>  tblAppMetodePayments::count(),
            'length'        =>  7
        ]);

        //
        $add                    =   new tblAppMetodePayments;
        $add->id                =   $newid;
        $add->type              = 1;
        $add->bank_id           =   $request->bank_select;
        $add->account_name      =   trim($request->bank_account);
        $add->account_norek     =   trim($request->bank_norek);
        $add->description       =   'Transfer Tunai';
        $add->user_id           =   $account['id'];
        $add->company_id        =   $request->partner_id;
        $add->status            =   1;
        $add->save();


        $ComponentOrders = new ComponentOrders;

        $data = [
            'message'       =>  'Data berhasil ditambahkan',
            'list'          =>  $ComponentOrders->BankList(['company_id'=>$request->partner_id])
        ];

        return response()->json($data, 200);

    }


    public function EditBank($request)
    {

    
        $cekpayment = tblAppMetodePayments::where([
            ['id','<>',$request->payment_id],
            ['account_norek','=',$request->bank_norek],
            ['status','=',1]
        ])
        ->count();
        
        if( $cekpayment > 0 )
        {
            $data = [
                'message'       =>  'Nomor Rekening sudah digunakan',
                'focus'         =>  'bank_norek'
            ];

            return response()->json($data, 401);
        }


        $update = DB::table('app_metode_payments')
        ->where([
            'id'        =>  $request->payment_id
        ])
        ->update([
            'account_name'          =>  $request->bank_account,
            'account_norek'         =>  $request->bank_norek
        ]);


        $ComponentOrders = new ComponentOrders;

        $data = [
            'message'       =>  $update === 1 ? 'Data berhasil di perbaharui' : '',
            'list'          =>  $ComponentOrders->BankList(['company_id'=>$request->partner_id])
        ];
        
        return response()->json($data, 200);
    }

    public function gudang(Request $request)
    {
        $type = trim($request->type);

        if( $type === 'add' )
        {
            $data = $this->AddGudang($request);
        }
        else
        {
            $data = $this->EditGudang($request);
        }

        return $data;

    }


    public function AddGudang($request)
    {
        //
        $Config = new Config;

        //
        $account = new account;
        $account = $account->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);

        //
        $address_arr = explode(',', $request->address_array);

        //
        $check = tblAppShipingOrigins::where([
            'kecamatan'     =>  $address_arr[2],
            'city'          =>  $address_arr[1],
            'provinsi'      =>  $address_arr[0],
            'company_id'    =>  $account['config']['company_id'],
            'status'        =>  1
        ])
        ->count();

        if( $check > 0 )
        {
            $data = [
                'message'       =>  'Alamat dengan Kecamatan yang sama sudah ada',
                'focus'         =>  'city'
            ];

            return response()->json($data, 401);
        }

        


        $newid = $Config->createnewidnew([
            'value'         =>  tblAppShipingOrigins::count(),
            'length'        =>  7
        ]);

        //
        $add                    =   new tblAppShipingOrigins;
        $add->id                =   $newid;
        $add->label             =   'Gudang';
        $add->name              =   $request->gudang_label;
        $add->provinsi          =   $address_arr[0];
        $add->city              =   $address_arr[1];
        $add->kecamatan         =   $address_arr[2];
        $add->address           =   trim($request->gudang_address);
        $add->kodepos           =   trim($request->gudang_kodepos);
        $add->phone             =   trim($request->gudang_phone);
        $add->user_id           =   $account['id'];
        $add->company_id        =   $account['config']['company_id'];
        $add->status            =   1;
        $add->save();


        $ComponentOrders = new ComponentOrders;

        $data = [
            'message'       =>  'Data berhasil ditambahkan',
            'list'          =>  $ComponentOrders->OriginList(['company_id'=>$request->partner_id])
        ];

        return response()->json($data, 200);

    }


    public function EditGudang($request)
    {

    
        //
        $Config = new Config;

        //
        $account = new account;
        $account = $account->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);

        //
        $address_arr = explode(',', $request->address_array);

        $check = tblAppShipingOrigins::where([
            ['id', '<>', $request->gudang_id],
            ['kecamatan', '=', $address_arr[2]],
            ['city', '=', $address_arr[1]],
            ['provinsi', '=', $address_arr[0]],
            ['company_id', '=', $account['config']['company_id']],
            ['status', '=', 1]
        ])
        ->count();
        
        if( $check > 0 )
        {
            $data = [
                'message'       =>  'Alamat dengan Kecamatan yang sama sudah ada',
                'focus'         =>  'city'
            ];

            return response()->json($data, 401);
        }


        $update = DB::table('app_shiping_origins')
        ->where([
            'id'        =>  $request->gudang_id
        ])
        ->update([
            'name'          =>  trim($request->gudang_label),
            'provinsi'      =>  $address_arr[0],
            'city'          =>  $address_arr[1],
            'kecamatan'     =>  $address_arr[2],
            'address'       =>  trim($request->gudang_address),
            'kodepos'       =>  trim($request->gudang_kodepos),
            'phone'         =>  trim($request->gudang_phone)
        ]);


        $ComponentOrders = new ComponentOrders;

        $data = [
            'message'       =>  $update === 1 ? 'Data berhasil di perbaharui' : '',
            'list'          =>  $ComponentOrders->OriginList(['company_id'=>$request->partner_id])
        ];
        
        return response()->json($data, 200);
    }


    // delete bank
    public function deletebank(Request $request)
    {


        $cek = tblAppMetodePayments::where([
            'id'        =>  $request->id,
            'status'    =>  1
        ])->count();

        //
        $ComponentOrders = new ComponentOrders;

        if( $cek == 0 )
        {
            $data = [
                'message'       =>  'Data yang akan diproses tidak ditemukan',
                'list'          =>  $ComponentOrders->BankList(['company_id'=>$request->partner_id])
            ];

            return response()->json($data, 404);
        }

        $delete = DB::table('app_metode_payments')
        ->where([
            'id'        =>  $request->id,
            'status'    =>  1
        ])
        ->update(['status'=>0]);

        

        $data = [
            'message'   =>  'Bank Pembayaran berhasil dihapus',
            'list'      =>  $ComponentOrders->BankList(['company_id'=>$request->partner_id])
        ];

        return response()->json($data, 200);

    }


    // delete gudang
    public function deletegudang(Request $request)
    {


        $cek = tblAppShipingOrigins::where([
            'id'        =>  $request->id,
            'status'    =>  1
        ])->count();

        //
        $ComponentOrders = new ComponentOrders;

        if( $cek == 0 )
        {
            $data = [
                'message'       =>  'Data yang akan diproses tidak ditemukan',
                'list'          =>  $ComponentOrders->OriginList(['company_id'=>$request->partner_id])
            ];

            return response()->json($data, 404);
        }

        $delete = DB::table('app_shiping_origins')
        ->where([
            'id'        =>  $request->id,
            'status'    =>  1
        ])
        ->update(['status'=>0]);

        

        $data = [
            'message'   =>  'Alamat Gudang berhasil dihapus',
            'list'      =>  $ComponentOrders->OriginList(['company_id'=>$request->partner_id])
        ];

        return response()->json($data, 200);

    }


    public function changeuniqnum(Request $request)
    {


        $update = DB::table('user_companies')
        ->where(['id'=>trim($request->company_id)])
        ->update(['order_uniqnum'=>trim($request->status) === 'off' ? 1 : 0]);

        $data = [
            'message'       =>  'Data berhasil diperbahrui',
            'status'        =>  trim($request->status),
            'company_id'    =>  trim($request->company_id)
        ];

        return response()->json($data, 200);
    }

}