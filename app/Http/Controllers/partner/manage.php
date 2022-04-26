<?php
namespace App\Http\Controllers\partner;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\user_companies as tblUserCompanies;
use App\users as tblUsers;
use App\product_prices as tblProductPrices;
use App\product_stocks as tblProductStocks;
use App\Http\Controllers\account\index as account;
use DB;


class manage extends Controller
{
    //
    public function create(Request $request)
    {
        $name = trim($request->name);
        $partner_type = trim($request->partner_type);
        $expire = trim($request->expire_days);

        //admin
        $admin_name = trim($request->admin_name);
        $admin_email = trim($request->admin_email);
        $admin_phone = trim($request->admin_phone);

        //
        $owner_name = trim($request->owner_name);
        $owner_phone = trim($request->owner_phone);
        $owner_email = trim($request->owner_email);
        
        //
        $contact_phone = trim($request->contact_phone);
        $contact_email = trim($request->contact_email);
        $contact_address = trim($request->contact_address);
        $contact_addressarr = trim($request->address_array);
        $contact_kodepos = trim($request->contact_kodepos);

        //check partner name
        $cekpartner = tblUserCompanies::where([
            'name'          =>  $name
        ])
        ->count();

        if( $cekpartner > 0 )
        {
            $response = [
                'error'     =>  [
                    'focus'         =>  'name',
                    'message'       =>  'Nama Partner sudah terdaftar sebelumnya'
                ]
            ];

            $data = [
                'message'           =>  '',
                'response'          =>  $response
            ];
    
    
            return response()->json($data, 401);
        }


        //cek admin email
        $cekadminemail =  tblUsers::where([
            'email'         =>  $admin_email
        ])->count();

        if( $cekadminemail > 0 )
        {
            //
            $response = [
                'error'     =>  [
                    'focus'         =>  'admin_email',
                    'message'       =>  'Email Admin telah digunakan akun lain'
                ]
            ];

            $data = [
                'message'           =>  '',
                'response'          =>  $response
            ];
    
            return response()->json($data, 401);
        }

        //cek admin phone
        $cekadminphone =  tblUsers::where([
            'phone'         =>  (int)$admin_phone
        ])->count();

        if( $cekadminphone > 0 )
        {
            
            $response = [
                'error'     =>  [
                    'focus'         =>  'admin_phone',
                    'message'       =>  'Nomor Whatsapp admin telah digunakan sebelumnya'
                ]
            ];

            $data = [
                'message'           =>  '',
                'response'          =>  $response
            ];
    
            return response()->json($data, 401);
        }

        $data = $this->addpartner($request);

        return response()->json($data, 200);
    }


    public function addpartner($request)
    {
        //
        $Config = new Config;

        $account = new account;
        $account = $account->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);

        //
        $newid = $Config->createnewidnew([
            'value'         =>  tblUserCompanies::count(),
            'length'        =>  9
        ]);
    
        //
        $address = explode(',', $request['address_array']);
        $contact = [
            'phone'         =>  $request['contact_phone'],
            'email'         =>  $request['contact_email']
        ];
        $owner_contact = [
            'phone'         =>  $request['owner_phone'],
            'email'         =>  $request['owner_email']
        ];

        //
        $new                =   new tblUserCompanies;
        $new->id            =   $newid;
        $new->token         =   md5($newid);
        $new->type          =   $request['partner_type'];
        $new->produsen_id   =   $account['config']['company_id'];
        $new->name          =   $request['name'];
        $new->nosurat       =   $request['nosurat'];
        $new->address       =   $request['contact_address'];
        $new->provinsi      =   $address[0];
        $new->city          =   $address[1];
        $new->kecamatan     =   $address[2];
        $new->kodepos       =   $request['contact_kodepos'];
        $new->contact       =   json_encode($contact);
        $new->owner         =   $request['owner_name'];
        $new->owner_contact =   json_encode($owner_contact);
        $new->order_uniqnum     =   1;
        $new->expire_payment    =   $request['expire_days'];
        $new->user_id           =   $account['id'];
        $new->verify            =   0;
        $new->status            =   1;
        $new->save();

        //register admin
        $dataregister = [
            'name'          =>  $request['admin_name'],
            'email'         =>  $request['admin_email'],
            'password'      =>  '',
            'username'      =>  '',
            'level'         =>  $request['partner_type'],
            'sub_level'     =>  1,
            'gender'        =>  1,
            'phone'         =>  $request['admin_phone'],
            'phone_code'    =>  '62',
            'company_id'    =>  $newid,
            'admin_id'      =>  $request['create_admin_id'],
            'info'          =>  '',
            'type'          =>  1
        ];
        

        $create = new \App\Http\Controllers\models\users;
        $create = $create->create($dataregister);

        //
        $data = [
            'message'       =>  'Data berhasil ditambah'
        ];

        return $data;
    }


    public function view(Request $request)
    {

        $Config = new Config;

        //
        $token = trim($request->token);

        //
        $cek = tblUserCompanies::where([
            'token'     =>  $token
        ])
        ->count();

        if( $cek == 0)
        {
            $data = [
                'message'           =>  'Data Not Found'
            ];

            return response()->json($data, 404);
        }


        $getcomp = tblUserCompanies::from('user_companies as uc')
        ->select(
            'uc.id', 'uc.name', 'uc.nosurat', 'uc.created_at as date', 'uc.expire_payment', 'uc.verify', 'uc.contact',
            'uc.owner', 'uc.owner_contact',
            'ut.name as type_name',
            'uc.address', 'uc.provinsi','uc.city', 'uc.kecamatan', 'uc.kodepos',
            'u.name as admin',
            'aop.name as provinsi_name',
            'aoc.name as city_name', 'aoc.type as city_type',
            'aok.name as kecamatan_name',
            'adm.id as admin_id', 'adm.level as admin_level', 'adm.sub_level as admin_sublevel', 'adm.name as admin_name', 'adm.email as admin_email', 'adm.phone as admin_phone', 'adm.registers as admin_register'
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
            'uc.token'          =>  $token
        ])
        ->first();

        $admin = explode(' ', $getcomp->admin);


        $listproduct = $this->viewLists($getcomp->id);
        $listpdistributor = $this->viewListPDistributor($getcomp->id);
        // $getupdateprice = tblProductPrices::from('product_prices as pp')
        // ->select(
        //     'pp.id', 'pp.price as uprice',
        //     'p.name as product_name', 'p.price_reseller as price'
        // )
        // ->leftJoin('products as p', function($join)
        // {
        //     $join->on('p.id', '=', 'pp.product_id');
        // })
        // ->where([
        //     'pp.company_id'    =>  $getcomp->id
        // ])
        // ->get();

        // if( count($getupdateprice) > 0 )
        // {
        //     foreach($getupdateprice as $row)
        //     {
        //         $listproduct[] = [
        //             'id'        =>  $row->id,
        //             'uprice'    =>  $row->uprice,
        //             'name'      =>  $row->product_name,
        //             'price'     =>  $row->price
        //         ];
        //     }
        // }
        // else
        // {
        //     $listproduct = '';
        // }

        $datacompanies = [
            'id'            =>  $getcomp->id,
            'type'          =>  $getcomp->type_name,
            'name'          =>  $getcomp->name,
            'nosurat'       =>  $getcomp->nosurat,
            'admin'         =>  $admin[0],
            'contact'       =>  json_decode($getcomp->contact),
            'owner'         =>  $getcomp->owner,
            'owner_contact' =>  json_decode($getcomp->owner_contact),
            'date'          =>  $Config->timeago($getcomp->date),
            'address'       =>  [
                'name'          =>  $getcomp->address,
                'kodepos'       =>  $getcomp->kodepos,
                'provinsi'      =>  $getcomp->provinsi_name,
                'city'          =>  $getcomp->city_type . '. ' . ucwords(strtolower($getcomp->city_name)),
                'kecamatan'     =>  'Kec. ' . ucwords(strtolower($getcomp->kecamatan_name)),
                'array'     =>  $getcomp->provinsi . ',' . $getcomp->city . ',' . $getcomp->kecamatan,
            ],
            'expire'        =>  $getcomp->expire_payment,
            'verify'        =>  $getcomp->verify,
            'administrator' =>  [
                'id'            =>  $getcomp->admin_id,
                'name'          =>  $getcomp->admin_name,
                'email'         =>  $getcomp->admin_email,
                'phone'         =>  $getcomp->admin_phone,
                'level'         =>  $getcomp->admin_level,
                'sublevel'      =>  $getcomp->admin_sublevel,
                'register'      =>  $getcomp->admin_register
            ],
            'listproduct'       =>  $listproduct,
            'listpdistributor'  =>  $listpdistributor
        ];


        $data = [
            'message'       =>  '',
            'response'      =>  [
                'comp'      =>  $datacompanies
            ]
        ];


        return response()->json($data, 200);
    }


    public function suntingLabel(Request $request)
    {
        $Config = new Config;

        //
        $partner_id = trim($request->partner_id);
        $name = trim($request->name);
        $nosurt = trim($request->nosurat);
        $expire = trim($request->expire_days);

        //update
        $update = DB::table('user_companies')
        ->where(['id'=>$partner_id])
        ->update([
            'name'      =>  $name,
            'nosurat'   =>  $nosurt,
            'expire_payment'     =>  $expire
        ]);


        $status = $update === 1 ? 'update' : '';

        $data = [
            'message'       =>  $update === 1 ? 'Data berhasil di perbaharui' : ''
        ];

        return response()->json($data, 200);

    }

    // sunting Contact
    public function suntingContact(Request $request)
    {
        $Config = new Config;

        //
        $partner_id = trim($request->partner_id);

        $contact = [
            'phone'         =>  trim($request->contact_phone),
            'email'         =>  trim($request->contact_email)
        ];

        //update
        $update = DB::table('user_companies')
        ->where(['id'=>$partner_id])
        ->update([
            'contact'       =>  json_encode($contact)
        ]);


        $status = $update === 1 ? 'update' : '';

        $data = [
            'message'       =>  $update === 1 ? 'Data berhasil di perbaharui' : ''
        ];

        return response()->json($data, 200);
    }


    // sunting Owner
    public function suntingOwner(Request $request)
    {
        $Config = new Config;

        //
        $partner_id = trim($request->partner_id);

        //
        $contact = [
            'phone'         =>  trim($request->owner_phone),
            'email'         =>  trim($request->owner_email)
        ];

        //update
        $update = DB::table('user_companies')
        ->where(['id'=>$partner_id])
        ->update([
            'owner'               =>    trim($request->owner_name),
            'owner_contact'       =>  json_encode($contact)
        ]);

        //
        $status = $update === 1 ? 'update' : '';

        $data = [
            'message'       =>  $update === 1 ? 'Data berhasil di perbaharui' : ''
        ];

        return response()->json($data, 200);

    }

    // sunting address
    public function suntingAddress(Request $request)
    {
        $Config = new Config;

        //
        $partner_id = trim($request->partner_id);

        //
        $address_arr = explode(',', trim($request->address_array));
        
        //update
        $update = DB::table('user_companies')
        ->where(['id'=>$partner_id])
        ->update([
            'address'           =>  trim($request->address),
            'provinsi'          =>  $address_arr[0],
            'city'              =>  $address_arr[1],
            'kecamatan'         =>  $address_arr[2],
            'kodepos'           =>  trim($request->address_kodepos)
        ]);


        $status = $update === 1 ? 'update' : '';

        $data = [
            'message'       =>  $update === 1 ? 'Data berhasil di perbaharui' : ''
        ];

        return response()->json($data, 200);
    }


    // sunting administartor
    public function suntingAdmin(Request $request)
    {
        $Config = new Config;

        //
        $admin_id = trim($request->admin_id);

        //checkemail
        $checkemail = tblUsers::where([
            ['email', '=', $request->admin_email],
            ['id',  '!=', $admin_id]
        ])
        ->count();

        if( $checkemail > 0 )
        {
            $data = [
                'focus'         =>  'admin_email',
                'message'       =>  'Alamat email sudah digunakan akun lain!'
            ];

            return response()->json($data, 401);
        }

        //check nomor whatsapp
        $checkphone = tblUsers::where([
            ['phone', '=', (int)$request->admin_phone],
            ['id',  '!=', $admin_id]
        ])
        ->count();

        if( $checkphone > 0 )
        {
            $data = [
                'focus'         =>  'admin_phone',
                'message'       =>  'Nomor Whatsapp sudah digunakan akun lain!'
            ];

            return response()->json($data, 401);
        }

        //update
        $update = DB::table('users')
        ->where(['id'=>$admin_id])
        ->update([
            'name'      =>  trim($request->admin_name),
            'email'     =>  trim($request->admin_email),
            'phone'     =>  (int)$request->admin_phone
        ]);

        $status = $update === 1 ? 'update' : '';

        $data = [
            'message'       =>  $update === 1 ? 'Data berhasil di perbaharui' : ''
        ];

        return response()->json($data, 200);
    }

    //
    public function createPrice(Request $request)
    {

        if( $request->type == 'add' )
        {
            //
            $data = $this->addPrice($request);
        }
        else
        {
            //
            $data = $this->editPrice($request);
        }

        return $data;
    }


    public function addPrice($request)
    {
        $account = new account;
        $account = $account->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);

        $check = tblProductPrices::where([
            'company_id'        =>  $request->partner_id,
            'product_id'        =>  $request->distributor_product,
            'status'            =>  1
        ])
        ->count();

        if( $check > 0 )
        {
            $data = [
                'message'       =>  'Produk sudah ditambahkan sebalumnya'
            ];

            return response()->json($data, 401);
        }


        //
        $Config = new Config;

        $newid = $Config->createnewidnew([
            'value'         =>  tblProductPrices::count(),
            'length'        =>  9
        ]);


        $price = str_replace('.', '', trim($request->distributor_price));

        //
        $add                    =   new tblProductPrices;
        $add->id                =   $newid;
        $add->product_id        =   trim($request->distributor_product);
        $add->price             =   $price;
        $add->company_id        =   trim($request->partner_id);
        $add->user_id           =   $account['id'];
        $add->status            =   1;
        $add->save();


        $data = [
            'message'       =>  'Data berhasil ditambahkan',
            'list'          =>  $this->viewLists($request->partner_id)
        ];

        return response()->json($data, 200);
    }


    public function editPrice($request)
    {

        $price = str_replace('.', '', trim($request->distributor_price));
        
        $update = DB::table('product_prices')
        ->where([
            'id'        =>  $request->distributor_product
        ])
        ->update([
            'price'         =>  $price
        ]);

        $data = [
            'message'       =>  $update === 1 ? 'Data berhasil di perbaharui' : '',
            'list'          =>  $this->viewLists($request->partner_id)
        ];

        return response()->json($data, 200);
    }


    public function deletePriceDistributor(Request $request)
    {
        $update = DB::table('product_prices')
        ->where([
            'id'            =>  $request->id,
            'status'        =>  1
        ])
        ->update([
            'status'        =>  0
        ]);

        $vlist = $this->viewLists($request->partner_id);

        
        $count = tblProductPrices::where([
            'company_id'            =>  $request->partner_id,
            'status'                =>  1
        ])->count();

        if( $update == 0 )
        {
            $data = [
                'message'           =>  '404 Opss.. Data tidak ditemukan',
                'list'              =>  $vlist,
                'count'             =>  $count
            ];

            return response()->json($data, 404);
        }
       
        $data = [
            'message'           =>  '',
            'list'              =>  $vlist,
            'count'             =>  $count
        ];


        return response()->json($data, 200);
    }

    //list porduct distributor
    public function viewListPDistributor($request)
    {
        $getproduct = tblProductStocks::from('product_stocks as ps')
        ->select(
            'ps.id', 'p.name', 'p.price', 'ps.stock', 'ps.product_id as product_id',
            DB::raw('IFNULL(pi.token, "") as images')
        )
        ->leftJoin('products as p', function($join)
        {
            $join->on('p.id', '=', 'ps.product_id');
        })
        ->leftJoin('product_images as pi', function($join)
        {
            $join->on('pi.product_id', '=', 'ps.product_id')
            ->where(['pi.status'=>1]);
        })
        ->where([
            'ps.company_id'     =>  trim($request),
            'ps.status'         =>  1
        ])
        ->get();

        if( count($getproduct) > 0)
        {
            foreach($getproduct as $row)
            {
                $list[] = [
                    'id'            =>  $row->id,
                    'product_id'    =>  $row->product_id,
                    'name'          =>  $row->name,
                    'price'         =>  $row->price,
                    'stock'         =>  $row->stock
                ];
            }

            
        }
        else
        {
            $list = '';
        }

        return $list;
    }

    //list harga produk
    public function viewLists($request)
    {
        $getupdateprice = tblProductPrices::from('product_prices as pp')
        ->select(
            'pp.id', 'pp.price as uprice',
            'p.name as product_name', 'p.price_reseller as price'
        )
        ->leftJoin('products as p', function($join)
        {
            $join->on('p.id', '=', 'pp.product_id');
        })
        ->where([
            'pp.company_id'    =>  trim($request),
            'pp.status'         =>  1
        ])
        ->get();

        if( count($getupdateprice) > 0 )
        {
            foreach($getupdateprice as $row)
            {
                $list[] = [
                    'id'        =>  $row->id,
                    'uprice'    =>  $row->uprice,
                    'name'      =>  $row->product_name,
                    'price'     =>  $row->price
                ];
            }
        }
        else
        {
            $list = '';
        }
        
        return $list;
    }

    public function CreatePDistributor(Request $request)
    {

        $Config = new Config;

        // 
        $account = new \App\Http\Controllers\account\index;
        $account = $account->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);

        //
        $productid = trim($request->product_selected);
        $partnerid = trim($request->partner_id);


        $inst = explode(',', $productid);
        foreach($inst as $row)
        {

            $cekpd = tblProductStocks::where([
                ['product_id', '=', $row],
                ['company_id',  '=', $partnerid],
                ['status',  '=', 1]
            ])->count();
            if( $cekpd == 0 )
            {
                $newid = $Config->createnewidnew([
                    'value'=>tblProductStocks::count(),
                    'length'=>9
                ]);
                $newadd             =   new tblProductStocks;
                $newadd->id         =   $newid;
                $newadd->type       =   2;
                $newadd->product_id     =   $row;
                $newadd->stock          =   0;
                $newadd->company_id     =   $partnerid;
                $newadd->produsen_id    =   $account['config']['company_id'];
                $newadd->user_id        =   $account['id'];
                $newadd->status         =   1;
                $newadd->save();
            }

        }

        $del = explode(',', trim($request->product_deleted));
        foreach($del as $row)
        {
            $delete = tblProductStocks::where([
                'product_id'        =>  $row,
                'company_id'        =>  $partnerid,
                'status'            =>  1
            ])->update([
                'status'        =>  0
            ]);
        }
        
        $data = [
            'message'       =>  '',
            'list'          =>  $this->viewListPDistributor($partnerid)
        ];

        return response()->json($data, 200);
    }


}