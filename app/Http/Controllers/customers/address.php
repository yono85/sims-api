<?php
namespace App\Http\Controllers\customers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\customer_address as tblCustomerAddress;
use App\order_shipings as tblOrderShipings;
use App\app_shiping_origins as tblAppShipingOrigins;
use App\Http\Controllers\access\manage as Refresh;
use App\Http\Controllers\account\index as Account;
use DB;

class address extends Controller
{
    // main address
    public function modal(Request $request)
    {
        $Config = new Config;
    }

    //list address
    public function list(Request $request)
    {
        $Config = new Config;

        $customer_id = $request->customerid;
        $type = $request->type;

        if( isset($type) && $type == '3' )
        {
            $getlist = tblAppShipingOrigins::from('app_shiping_origins as asp')
            ->select(
                'asp.id', 'asp.label', 'asp.name', 'asp.address', 'asp.kodepos', 'asp.phone',
                'p.name as provinsi_name', 
                'c.name as city_name', 'c.type_label as city_label',
                DB::raw('IFNULL(k.name, "") as kecamatan_name')
            )
            ->leftJoin('app_origin_provinsis as p', function($join)
            {
                $join->on('p.id', '=', 'asp.provinsi');
            })
            ->leftJoin('app_origin_cities as c', function($join)
            {
                $join->on('c.id', '=', 'asp.city');
            })
            ->leftJoin('app_origin_kecamatans as k', function($join)
            {
                $join->on('k.id', '=', 'asp.kecamatan');
            })
            ->where([
                'asp.company_id'            =>  $customer_id,
                'asp.status'                =>  1
            ])
            ->orderBy('asp.id', 'desc')
            ->get();


            foreach($getlist as $row)
            {
                $list[] = [
                    'id'                =>  $row->id,
                    'label'             =>  $row->label,
                    'name'              =>  $row->name,
                    'phone'             =>  $row->phone,
                    'keep'              =>  0,
                    'address'           =>  $row->address,
                    'kodepos'           =>  $row->kodepos,
                    'provinsi'          =>  ucwords($row->provinsi_name),
                    'city'              =>  $row->city_label . '. ' . ucwords($row->city_name),
                    'kecamatan'         =>  ucwords($row->kecamatan_name)
                ];


            }


            $data = [
                'message'           =>  '',
                'response'          =>  $list
            ];

            return response()->json($data, 200);


        }




        // else 

        $getlist = tblCustomerAddress::from('customer_addresses as ca')
        ->select(
            'ca.id', 'ca.label', 'ca.name', 'ca.keep', 'ca.address', 'ca.kodepos', 'ca.phone',
            'p.name as provinsi_name', 
            'c.name as city_name', 'c.type_label as city_label',
            DB::raw('IFNULL(k.name, "") as kecamatan_name')
        )
        ->leftJoin('app_origin_provinsis as p', function($join)
        {
            $join->on('p.id', '=', 'ca.provinsi');
        })
        ->leftJoin('app_origin_cities as c', function($join)
        {
            $join->on('c.id', '=', 'ca.city');
        })
        ->leftJoin('app_origin_kecamatans as k', function($join)
        {
            $join->on('k.id', '=', 'ca.kecamatan');
        })
        ->where([
            'ca.customer_id'       =>  $customer_id,
            'ca.status'            =>  1
        ])
        ->orderBy('ca.id', 'desc')
        ->get();

        if( count($getlist) > 0 )
        {
            $status = 200;
            
            foreach($getlist as $row)
            {
                $list[] = [
                    'id'                =>  $row->id,
                    'label'             =>  $row->label,
                    'name'              =>  $row->name,
                    'phone'             =>  $row->phone,
                    'keep'              =>  $row->keep,
                    'address'           =>  $row->address,
                    'kodepos'           =>  $row->kodepos,
                    'provinsi'          =>  ucwords($row->provinsi_name),
                    'city'              =>  $row->city_label . '. ' . ucwords($row->city_name),
                    'kecamatan'         =>  ucwords($row->kecamatan_name)
                ];


            }
        }
        else
        {
            $status = 404;
        }


        $data = [
            'message'           =>  $status === 404 ? 'Alamat tidak ditemukan' : '',
            'response'          =>  $status === 404 ? '' : $list
        ];

        return response()->json($data, $status);
    }


    // view address
    public function view(Request $request)
    {
        $Config = new Config;

        $addressid = $request->addressid;


        $getaddr = tblCustomerAddress::from('customer_addresses as ca')
        ->select(
            'ca.id', 'ca.label', 'ca.name', 'ca.provinsi', 'ca.city', 'ca.kecamatan', 'ca.address', 'ca.kodepos', 'ca.keep', 'ca.phone',
            'p.name as provinsi_name',
            'c.name as city_name', 'c.type_label as city_label',
            'k.name as kecamatan_name'
        )
        ->leftJoin('app_origin_provinsis as p', function($join)
        {
            $join->on('p.id', '=', 'ca.provinsi');
        })
        ->leftJoin('app_origin_cities as c', function($join)
        {
            $join->on('c.id', '=', 'ca.city');
        })
        ->leftJoin('app_origin_kecamatans as k', function($join)
        {
            $join->on('k.id', '=', 'ca.kecamatan');
        })
        ->where([
            'ca.id'            =>  $addressid
        ])
        ->first();

        if( $getaddr == null )
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];
    
    
            return response()->json($data, 404);

        }
        $dataaddress = [
            'id'            =>  $getaddr->id,
            'label'         =>  $getaddr->label,
            'name'          =>  $getaddr->name,
            'phone'         =>  $getaddr->phone,
            'address_array' =>  $getaddr->provinsi . ',' . $getaddr->city . ',' . $getaddr->kecamatan,
            'address_label' =>  $getaddr->provinsi_name . ', ' . $getaddr->city_label . '.' . $getaddr->city_name. ', Kec. ' . $getaddr->kecamatan_name, 
            'address'       =>  $getaddr->address,
            'kodepos'       =>  $getaddr->kodepos,
            'keep'          =>  $getaddr->keep
        ];


        $data = [
            'message'       =>  '',
            'response'      =>  $dataaddress
        ];


        return response()->json($data, 200);


    }

    // CREATE

    public function create(Request $request)
    {
        

        if( $request->type === 'add')
        {
            $data = $this->add($request);
        }
        else
        {
            $data = $this->edit($request);
        }



        return response()->json($data, $data['status']);


    }

    //add 
    public function add($request)
    {
        $Config = new Config;

        $account = new Account;
        $account = $account->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);

        //check if first address for customer
        $check = tblCustomerAddress::where([
            'customer_id'       =>  $request->customer_id,
            'status'            =>  1
        ])->count();


        //
        $destination = explode(',', $request->address_array);

        //create new id
        $newid = $Config->createnewidnew([
            'value'         =>  tblCustomerAddress::count(),
            'length'        =>  14
        ]);

        if($request->keep == '1' )
        {
            $update = tblCustomerAddress::where([
                'customer_id'           =>  $request->customer_id
            ])
            ->update([
                'keep'          =>  0
            ]);
        }

        //
        $newdata                    =   new tblCustomerAddress;
        $newdata->id                =   $newid;
        $newdata->customer_id       =   trim($request->customer_id);
        $newdata->label             =   trim($request->label);
        $newdata->name              =   trim($request->name);
        $newdata->phone             =   trim($request->phone);
        $newdata->provinsi          =   trim($destination[0]);
        $newdata->city              =   trim($destination[1]);
        $newdata->kecamatan         =   trim($destination[2]);
        $newdata->address           =   trim($request->address);
        $newdata->kodepos           =   trim($request->kodepos);
        $newdata->keep              =   $check === 0 ? 1 : trim($request->keep);
        $newdata->user_id           =   $account['id'];
        $newdata->status            =   1;
        $newdata->save();

        
        // jika order id tidak sama dengan 0
        // maka insert logs CBO
        if($request['order_id'] != '0')
        {
            $this->updateShiping([
                'order_id'      =>  $request['order_id'],
                'destination_id'    =>  $newid
            ]);

            //
            $cekcount = tblCustomerAddress::where(['customer_id'=>trim($request->customer_id)])
            ->count();
            if( $cekcount == 1)
            {
                //log customers CBO
                $datalog = [
                    'customer_id'       =>  $request->customer_id,
                    'user_id'           =>  $account['id'],
                    'address_id'        =>  $newid,
                    'label'             =>  $request->label
                ];

                $addLogs = new \App\Http\Controllers\log\customers\manage;
                $addLogs = $addLogs->CBO($datalog);
            }

        }
        else
        {

            //history log customer
            $datalog = [
                'customer_id'       =>  $request->customer_id,
                'user_id'           =>  $account['id'],
                'title'             =>  'Menambah Alamat',
                'body'              =>  'Menambahkan alamat Customer',
                'type'              =>  2,
                'sub_type'          =>  4
            ];
    
            $addLogs = new \App\Http\Controllers\log\customers\manage;
            $addLogs = $addLogs->update($datalog);
        }


        //
        $data = [
            'status'        =>  200,
            'message'       =>  'Data berhasil ditambahkan',
            'response'      =>  [
                'id'                =>  $newid
            ]
        ];


        return $data;
    }


    //edit
    public function edit($request)
    {

        $Config = new Config;

        // $Refresh = new Refresh;
        // $Refresh = $Refresh->refresh();

        //
        $destination = explode(',', $request->address_array);

        $check = tblCustomerAddress::where([
            'id'            =>  $request->address_id,
            'keep'          =>  0
        ])->count();

        if( $check > 0 )
        {

            if( $request->keep == '1')
            {
                $update = tblCustomerAddress::where([
                    'customer_id'           =>  $request->customer_id
                ])
                ->update([
                    'keep'          =>  0
                ]);

                $keep = 1;
            }
            else
            {
                $keep = 0;
            }
        }
        else
        {
            if( $request->keep == '0')
            {
                $data = [
                    'message'       =>  'Gagal merubah data, harap tentukan Alamat Utama',
                    'status'        =>  401
                ];

                return $data;
            }
            
            $keep = 1;
        }



        //
        $updatedata = tblCustomerAddress::where([
            'id'            =>  $request->address_id
        ])
        ->update([
            'label'         =>  trim($request->label),
            'name'          =>  trim($request->name),
            'phone'         =>  trim($request->phone),
            'provinsi'      =>  $destination[0],
            'city'          =>  $destination[1],
            'kecamatan'     =>  $destination[2],
            'address'       =>  trim($request->address),
            'kodepos'       =>  trim($request->kodepos),
            'keep'          =>  $keep
        ]);



        //
        $data = [
            'status'        =>  200,
            // 'refresh'       =>  $Refresh,
            'message'       =>  'Data berhasil diubah'
        ];


        return $data;
    }


    //set address
    public function set(Request $request)
    {

    }


    //set keep / utama
    public function keep(Request $request)
    {

    }

    //UPDATE SHIPING
    public function updateShiping($request)
    {
        $order_id = $request['order_id'];
        $destination_id = $request['destination_id'];

        $upshiping = tblOrderShipings::where([
            'order_id'          =>  $order_id
        ])
        ->update([
            'destination_id'        =>  $destination_id
        ]);
        
        $updatefield = new \App\Http\Controllers\orders\manage;
        $updatefield = $updatefield->updatefield(['order_id'=>$order_id]);
    }

}