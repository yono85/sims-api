<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\orders as tblOrders;
use App\order_items as tblOrderItems;
use App\products as tblProducts;
use App\product_prices as tblProductPrices;
use DB;

class orders extends Controller
{

    //
    public function main($request)
    {
        $Config = new Config;

        //
        $newid = tblOrders::count();
        $newid++;
        // $newid = $newid++;

        //getcustome 
        $customer = DB::table('customers')
        ->where([
            'id'        =>  $request['customer_id']
        ])->first();


        //create new id
        $newid = $Config->createnewid([
            'value'         =>  $newid,
            'length'        =>  15
        ]);

        // invoice
        $invoice = date('ymd', time() ) . $Config->createuniq(['length'=>6,'value'=>$newid]) . 'INV';

        //
        $newaddorders                       =   new tblOrders;
        $newaddorders->id                   =   $newid;
        $newaddorders->type                 =   $request['type'];
        $newaddorders->uniq                 =   $request['order_uniqnum'] === 1 ? $Config->uniqnum(100,299) : 0;
        $newaddorders->token                =   md5($newid);
        $newaddorders->invoice              =   $invoice;
        $newaddorders->customer_id          =   $request['customer_id'];
        $newaddorders->promo_id             =   0;
        $newaddorders->search               =   "";
        $newaddorders->field                =   "";
        $newaddorders->checkout             =   0;
        $newaddorders->payment              =   0;
        $newaddorders->verify               =   0;
        $newaddorders->paid                 =   0;
        $newaddorders->notes                =   "";
        $newaddorders->user_id              =   $request['user_id'];
        $newaddorders->company_id           =   $request['company_id'];
        $newaddorders->expire_payment_date  =   $request['expire_payment_date'];
        $newaddorders->bulking              =   $request['type'] === '4' ? 1 : 0;
        $newaddorders->bulking_keep         =   0;
        $newaddorders->bulking_paid         =   0;
        $newaddorders->status               =   1;
        $newaddorders->save();

        
        $data = [
            'id'            =>  $newid,
            'company_id'    =>  $request['company_id'],
            'company_type'  =>  $request['company_type'],
            'type'          =>  $request['type'],
            'order_item'    =>  $request['order_item']
        ];

        //add item
        $this->addnewitem($data);

        //log
        if( $request['type'] == '1' || $request['type'] == '2' || $request['type'] == '4')
        {
            //log customers
            $datalog = [
                'customer_id'       =>  $request['customer_id'],
                'user_id'           =>  $request['user_id'],
                'order_id'          =>  $newid,
                'invoice'           =>  $invoice
            ];

            $addLogs = new \App\Http\Controllers\log\customers\manage;
            $addLogs = $addLogs->orders($datalog);
        }

        return $newid;

    }


    public function addnewitem($request)
    {

        
        //config
        $Config = new Config;

        ///
        $order_id = $request['id'];
        $order_item = explode(",", $request['order_item']);

        foreach($order_item as $list)
        {
            $product = tblProducts::where([
                'id'        =>  $list
            ])->first();


            if( $request['company_type'] == '2')
            {
                $cekpp = tblProductPrices::where([
                    'product_id'        =>  $list,
                    'company_id'        =>  $request['company_id']
                ])->first();


                $price_reseller = $cekpp === null ? $product->price_reseller : $cekpp->price;
            }
            else
            {
                $price_reseller = $product->price_reseller;
            }

            //cek price reseller
            $cekprice = tblProductPrices::where([
                'product_id'        =>  $list,
                'company_id'        =>  $request['company_id']
            ])->first();

            

            //create new id
            $newid = tblOrderItems::count();
            $newid++;
            $newid = $newid++;

            $newid = $Config->createnewid([
                'value'         =>  $newid,
                'length'        =>  15
            ]);


            $newaddoi                       =   new tblOrderItems;
            $newaddoi->id                   =   $newid;
            $newaddoi->order_id             =   $order_id;
            $newaddoi->product_id           =   $list;
            $newaddoi->quantity             =   1;
            $newaddoi->weight               =   $product->weight;
            $newaddoi->weight_total         =   $product->weight;
            $newaddoi->price                =   $product->price;
            $newaddoi->price_reseller       =   $price_reseller;
            $newaddoi->price_total          =   $product->price;
            $newaddoi->price_total_reseller =   $price_reseller;
            $newaddoi->status               =   1;
            $newaddoi->save();

            
        }


        $upfield = new \App\Http\Controllers\orders\manage;
        $upfield = $upfield->updatefield(['order_id'=>$order_id]);



    }


    public function updateoi($request)
    {
        $order_id = $request['order_id'];
        $order_item = explode(",", $request['order_item']);


        //cek jika order item ada maka tambah quantity
        foreach($order_item as $list)
        {
            $cekoi = tblOrderItems::where([
                'product_id'        =>  $list,
                'order_id'          =>  $order_id,
                'status'            =>  1
            ])->count();

            if( $cekoi > 0 )
            {
                //update quantity
                $dataupoi = [
                    'order_id'      =>  $order_id,
                    'order_item'    =>  $list
                ];

                $this->updateqty($dataupoi);
            }
            else
            {
                $dataaddoi = [
                    'order_item'        =>  $list,
                    'order_id'          =>  $order_id
                ];

                $this->additemsingle($dataaddoi);
            }
        }

        // $this->updatefield(['order_id'=>$order_id]);
        //update field
        $upfield = new \App\Http\Controllers\orders\manage;
        $upfield = $upfield->updatefield(['order_id'=>$order_id]);

        
    }


    //UPDATE QUANTITY === >
    public function updateqty($request)
    {
        $order_id = $request['order_id'];
        $order_item = $request['order_item'];



        $getoi = tblOrderItems::where([
            'product_id'        =>  $order_item,
            'order_id'          =>  $order_id,
            'status'            =>  1
        ])->first();

        $qty = ( 1 + $getoi->quantity);



        $upoi = tblOrderItems::where([
            'product_id'        =>  $order_item,
            'order_id'          =>  $order_id,
            'status'            =>  1
        ])
        ->update([
            'quantity'              =>  $qty,
            'weight_total'          =>  ($getoi->weight * $qty),
            'price_total'           =>  ($getoi->price * $qty),
            'price_total_reseller'  =>  ($getoi->price_reseller * $qty)
        ]);
    }

    //ADD SINGLE ITEM ====>
    public function additemsingle($request)
    {
        $Config = new Config;

        //
        $order_item = $request['order_item'];
        $order_id = $request['order_id'];

        $product = tblProducts::where([
            'id'        =>  $order_item
        ])->first();


        //
        $geto = tblOrders::where([
            'id'        =>  $order_id
        ])->first();


        $cekpp = tblProductPrices::where([
            'product_id'            =>  $order_item,
            'company_id'            =>  $geto->company_id
        ])->first();

        $price_reseller = $cekpp === null ? $product->price_reseller : $cekpp->price;

        //create new id
        $newid = tblOrderItems::count();
        $newid++;
        $newid = $newid++;

        $newid = $Config->createnewid([
            'value'         =>  $newid,
            'length'        =>  15
        ]);


        $newaddoi                       =   new tblOrderItems;
        $newaddoi->id                   =   $newid;
        $newaddoi->order_id             =   $order_id;
        $newaddoi->product_id           =   $order_item;
        $newaddoi->quantity             =   1;
        $newaddoi->weight               =   $product->weight;
        $newaddoi->weight_total         =   $product->weight;
        $newaddoi->price                =   $product->price;
        $newaddoi->price_reseller       =   $price_reseller;
        $newaddoi->price_total          =   $product->price;
        $newaddoi->price_total_reseller =   $price_reseller;
        $newaddoi->status               =   1;
        $newaddoi->save();


    }


    // UPDATE FIELD =====>
    // public function updatefield($request)
    // {
    //     //
    //     $order_id = $request['order_id'];

    //     //
    //     // update field orders
    //     $total = tblOrderItems::select(
    //         DB::raw('sum(weight) as weight'),
    //         DB::raw('sum(price_total) as price_total'),
    //         DB::raw('sum(price_total_reseller) as price_total_reseller')
    //     )
    //     ->where([
    //         'order_id'          =>  $order_id,
    //         'status'            =>  1
    //     ])->first();

    //     $geto = tblOrders::from('orders as o')
    //     ->select(
    //         'o.id as order_id',
    //         'o.invoice',
    //         'c.name as customer_name',
    //         'c.gender as customer_gender',
    //         'c.phone as customer_phone',
    //         DB::raw('IFNULL(ca.address, "") as destination_address'),
    //         DB::raw('IFNULL(aoc.name, "") as destination_city'),
    //         DB::raw('IFNULL(aop.name, "") as destination_provinsi'),
    //     )
    //     ->leftJoin('customers as c', function($join)
    //     {
    //         $join->on('c.id', '=', 'o.customer_id');
    //     })
    //     ->leftJoin('order_shipings as os', function($join)
    //     {
    //         $join->on('os.order_id', '=', 'o.id')
    //         ->where(['os.status'=>1]);
    //     })
    //     ->leftJoin('customer_addresses as ca', function($join)
    //     {
    //         $join->on('ca.id', '=', 'os.destination_id');
    //     })
    //     ->leftJoin('app_origin_cities as aoc', function($join)
    //     {
    //         $join->on('aoc.id', '=', 'ca.city');
    //     })
    //     ->leftJoin('app_origin_provinsis as aop', function($join)
    //     {
    //         $join->on('aop.id', '=', 'aoc.provinsi_id');
    //     })
    //     ->where([
    //         'o.id'        =>  $order_id
    //     ])->first();


    //     //
    //     $field = [
    //         'customers' =>  [
    //             'name'      =>  $geto->customer_name,
    //             'gender'    =>  $geto->customer_gender === 1 ? 'male' : 'female',
    //             'phone'     =>  $geto->customer_phone
    //         ],
    //         'payment'   =>  [
    //             'method'        =>  '',
    //             'method_type'   =>  '',
    //             'bank'          =>  '',
    //             'total'             =>  $total->price_total,
    //             'total_reseller'    =>  $total->price_total_reseller
    //         ],
    //         'destination'   =>  [
    //             'address'       =>  $geto->destination_address,
    //             'kota'          =>  $geto->destination_city,
    //             'provinsi'      =>  $geto->destination_provinsi
    //         ]
    //     ];

    //     $search = $geto->invoice . ',' . $geto->customer_name . ',' . $geto->customer_phone . $geto->destinatino_address != '' ? ',' . $geto->destinatino_address : '';

    //     $updateorder = tblOrders::where([
    //         'id'        =>  $order_id
    //     ])
    //     ->update([
    //         'search'            =>  $search,
    //         'field'             =>  json_encode($field)
    //     ]);
    // }



    // UPDATE ADD ITEM ======>

    public function updateadditem(Request $request)
    {


        $order_id = $request->order_id;
        $order_item = $request->order_item;

        $cekorders = tblOrders::from('orders as o')
        ->select(
            'o.company_id', 'uc.type'
        )
        ->where([
            'o.id'        =>  $order_id
        ])
        ->leftJoin('user_companies as uc', function($join)
        {
            $join->on('uc.id', '=', 'o.company_id');
        })
        ->first();


        $dataadd = [
            'id'                =>  $order_id,
            'order_item'        =>  $order_item,
            'company_id'        =>  $cekorders->company_id,
            'company_type'      =>  $cekorders->type
        ];

        //additem 
        $this->addnewitem($dataadd);

        return response()->json(['message'=>''], 200);
        
    }

}