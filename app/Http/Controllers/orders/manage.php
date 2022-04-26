<?php
namespace App\Http\Controllers\orders;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\orders as tblOrders;
use App\order_items as tblOrderItems;
use App\customer_address as tblCustomerAddress;
use App\Http\Controllers\access\manage as Refresh;
use App\order_checkouts as tblOrderCheckouts;
use App\users as tblUsers;
use App\order_shipings as tblOrderShipings;
use App\order_images as tblOrderImages;
use App\user_companies as tblUserCompanies;
use App\Http\Controllers\account\index as Account;
use DB;

class manage extends Controller
{
    //
    public function new(Request $request)
    {


        //create new
        $order_item = trim($request->order_item);
        $customer_id = trim($request->customer_id);
        $order_id = trim($request->order_id);
        $type = trim($request->order_type);

        //
        $user = new \App\Http\Controllers\account\index;
        $user = $user->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);


        //ceking eksist orders 
        //ceking user id and cusomer id
        //if status order 1 and checkout 0
        $checko = tblOrders::where([
            'type'              =>  $type,
            'user_id'           =>  $user['id'],
            'customer_id'       =>  $customer_id,
            'payment'           =>  0,
            'status'            =>  1
        ])
        ->first();

        if( $type != '1' )
        {
            //cek
            $cekcomp = tblUserCompanies::where([
                'id'        =>  $user['config']['company_id']
            ])->first();

            $expire = $cekcomp->expire_payment;
        }
        else
        {
            $expire = 0;
        }

        $date = date('Y-m-d H:i:s', time());
        $expire = $expire === 0 ? '' : date('Y-m-d H:i:s', strtotime( date('Y-m-d H:i:s', time()) . '+'.$expire.' day'));

        if( $checko == null )
        {
            //new orders
            $datanew = [
                'type'              =>  $type,
                'customer_id'       =>  $customer_id,
                'user_id'           =>  $user['id'],
                'company_id'        =>  $user['config']['company_id'],
                'company_type'      =>  $user['config']['company_type'],
                'order_uniqnum'     =>  $user['config']['order_uniqnum'],
                'order_item'        =>  $order_item,
                'expire_payment_date'            =>  $expire
            ];

            $insneworder = new \App\Http\Controllers\models\orders;
            $insneworder = $insneworder->main($datanew);

            $order_id = $insneworder;
        }
        else
        {
            // update item orders
            $dataupdate = [
                'order_id'          =>  $checko->id,
                'order_type'        =>  $checko->type,
                'order_item'        =>  $order_item,
                'company_type'      =>  $user['config']['company_type'],
            ];

            $uporderitem = new \App\Http\Controllers\models\orders;
            $uporderitem = $uporderitem->updateoi($dataupdate);

            $order_id = $checko->id;
        }


        //get cart
        $getcart = new \App\Http\Controllers\orders\widget;
        $getcart = $getcart->cart(['id'=>$order_id]);

        $data = [
            'message'       =>  '',
            'response'      =>  [
                'order_id'          =>  $order_id,
                'cart'              =>  $getcart
            ]
        ];


        return response()->json($data, 200);

    }

    //VIEW ORDER
    public function view($request)
    {
        $Config = new Config;
        $order_id = $request['order_id'];

        $getod = tblOrders::from('orders as o')
        ->select(
            'o.id','o.type', 'o.token', 'o.uniq', 'o.invoice', 'o.field', 'o.notes', 'o.payment', 'o.paid', 'o.created_at', 'o.expire_payment_date',
            DB::raw('SUM(oi.weight_total) as weight'),
            DB::raw('SUM(oi.price_total) as total'),
            DB::raw('SUM(oi.price_total_reseller) as total_reseller'),
            // 
            DB::raw('IFNULL(os.code, 0) as shiping_code'),
            DB::raw('IFNULL(os.print_status, 0) as print_status'),
            // 
            DB::raw('IFNULL(os.courier_id, 0) as courier_id'),
            //
            DB::raw('IFNULL(ocl.image, "") as courier_image'),
            DB::raw('IFNULL(ocl.weight_up, 0) as weight_up'),
            DB::raw('IFNULL(aso.id, 0) as origin_id'),
            DB::raw('IFNULL(aso.name, "") as origin_name'),
            'aops.name as origin_p_name','aocs.name as origin_c_name', 'aocs.type as origin_c_type',
            //payments
            'oc.payment_id',
            'amp.type as payment_type', 'amp.name as payment_name', 'amp.account_name as payment_account', 'amp.account_norek as payment_norek',
            'abl.label as bank_name', 'abl.image as bank_image',
            //promo
            'o.promo_id',
            DB::raw('IFNULL(op.field, "") as promo_field'),
            DB::raw('IFNULL(op.total, 0) as promo_total'),
            DB::raw('IFNULL(op.status, 0) as promo_status'),
            DB::raw('IFNULL(aps.name, "") as promo_name'),
            DB::raw('IFNULL(aps.description, "") as promo_detail')
        )
        //order checkouts
        ->leftJoin('order_checkouts as oc', function($join)
        {
            $join->on('oc.order_id', '=', 'o.id');
        })
        //payments
        ->leftJoin('app_metode_payments as amp', function($join)
        {
            $join->on('amp.id', '=', 'oc.payment_id');
        })
        ->leftJoin('app_bank_lists as abl', function($join)
        {
            $join->on('abl.id', '=', 'amp.bank_id');
        })
        // shiping
        ->leftJoin('order_shipings as os', function($join)
        {
            $join->on('os.order_id', '=', 'o.id');
        })
        ->leftJoin('app_courier_lists as ocl', function($join)
        {
            $join->on('ocl.id', '=', 'os.courier_id');
        })
        ->leftJoin('order_items as oi', function($join)
        {
            $join->on('oi.order_id', '=', 'o.id')
            ->where(['oi.status'=>1]);
        })
        //origin
        ->leftJoin('app_shiping_origins as aso', function($join)
        {
            $join->on('aso.id', '=', 'os.origin_id');
        })
        ->leftJoin('app_origin_provinsis as aops', function($join)
        {
            $join->on('aops.id', '=', 'aso.provinsi');
        })
        ->leftJoin('app_origin_cities as aocs', function($join)
        {
            $join->on('aocs.id', '=', 'aso.city')
            ->where(['aocs.status'=>1]);
        })
        ->leftJoin('order_promos as op', function($join)
        {
            $join->on('op.id', '=', 'o.promo_id');
        })
        ->leftJoin('app_promos as aps', function($join)
        {
            $join->on('aps.id', '=', 'op.promo_id');
        })
        ->where([
            'o.id'            =>  $order_id
        ])
        ->first();

        //item product
        $getitem = tblOrderItems::from('order_items as oi')
        ->select(
            'oi.id', 'oi.weight', 'oi.weight_total', 'oi.quantity', 'oi.price',  'oi.price_total', 'oi.price_reseller', 'oi.price_total_reseller',
            'p.name as product_name', 'pi.id as product_image'
        )
        ->leftJoin('products as p', function($join)
        {
            $join->on('p.id', '=', 'oi.product_id');
        })
        ->leftJoin('product_images as pi', function($join)
        {
            $join->on('pi.product_id', '=', 'p.id')
            ->where(['pi.status'=>1]);
        })
        ->where([
            'oi.order_id'   =>  $order_id,
            'oi.status'        =>  1
        ])->get();

        foreach($getitem as $row)
        {
            $listitem[] = [
                'id'            =>  $row->id,
                'qty'           =>  $row->quantity,
                'price'         =>  $row->price,
                'total'         =>  $row->price_total,
                'price_reseller'    =>  $row->price_reseller,
                'total_reseller'    =>  $row->price_total_reseller,
                'weight'        =>  $row->weight_total,
                'name'          =>  $row->product_name,
                'image'         =>   $Config->apps()['storage']['URL'] .'/images/' . ($row->product_image === null ? 'none/no-product-image.png' : $row->product_image . '.jpg' ),
                'price_show'    =>  $getod->type === 1 || $getod->type === 2 ? 'p' : 'r'
            ];
        }

        // field
        $field = json_decode($getod->field, true);

        // bank list
        $banklist = new \App\Http\Controllers\orders\payment;
        $banklist = $banklist->viewbanklist();

        //for courier
        if( $field['shiping']['courier_name'] == 'SAP')
        {

            if( $field['destination']['array'] != '')
            {
                $arrdesticode = explode(',', $field['destination']['array']);

                //get code district on SAP
                $getdesticode = DB::table('app_origin_saps')
                ->where([
                    'origin_kecamatan'      =>  $arrdesticode[2]
                ])->first();
                
                //
                $getwherehouse = DB::table('app_shiping_origins as aso')        
                ->where([
                    'aso.id'        =>  $getod->origin_id
                ])
                ->first();


                $getOrigin = DB::table('app_origin_saps')
                ->where([
                    'origin_kecamatan'      =>  $getwherehouse->kecamatan
                ])->first();

                $origin_branch = $getOrigin->branch_code;
                $desti_branch = $getdesticode->branch_code;
                $branch_code = $getOrigin->branch_code .' - ' . $getdesticode->branch_code;

            }
            else
            {
                $origin_branch = '';
                $desti_branch = '';
                $branch_code = '';
            }

            $courier = [
                'origin_branch'       =>  $origin_branch,
                'desti_branch'        =>  $desti_branch,
                'branch_code'       =>  $branch_code
            ];
        }
        else
        {
            $courier = [
                'origin_branch'       =>  '',
                'desti_branch'        =>  '',
                'branch_code'         =>    ''
            ];
        }

        //
        $update = [
            'courier'       =>  $courier
        ];

        $promo = [
            'id'                =>  $getod->promo_id,
            'total'             =>  $getod->promo_total,
            'name'              =>  $getod->promo_name,
            'detail'            =>  $getod->promo_detail,
            'status'            =>  ($getod->promo_id === 0 ? 0 : ($getod->promo_id !== 0 && $getod->promo_status === 0 ? 0 : 1))
        ];


        //data
        $data = [
            'order'         =>  [
                'id'                =>  $getod->id,
                'type'              =>  $getod->type,
                'token'             =>  $getod->token,
                'uniq'              =>  $getod->uniq,
                'invoice'           =>  $getod->invoice,
                'total'             =>  $getod->total,
                'total_reseller'    =>  $getod->total_reseller,
                'weight'            =>  $getod->weight,
                'origin_id'         =>  $getod->origin_id,
                'notes'             =>  $getod->notes,
                'paid'              =>  $getod->paid,
                'payment'           =>  $getod->payment,
                'date'              =>  date('d/m/Y H.i', strtotime($getod->created_at)),
                'created_at'        =>  $getod->created_at,
                'url_invoice'       =>  $Config->apps()['URL']['STORE'] . '/invoice/v1?token=' . $getod->token,
                'url_cwa'            =>  $Config->apps()['URL']['SLINK'] . '/clickwa/orders/invoice?token=' . $getod->token,
                'exp'               =>  $getod->expire_payment_date
            ],
            'customer'     =>  $field['customers'],
            'payment'       =>  [
                'id'                =>  $getod->payment_id,
                'type'              =>  $getod->payment_type,
                'name'              =>  $getod->payment_type === 0 ? 0 : ( $getod->payment_type === 1 ? 'Transfer Bank ' . $getod->bank_name : $getod->payment_name),
                'label'             =>  $getod->payment_type === 0 ? '' : ( $getod->payment_type === 1 ? ($getod->payment_account . ' - ' . $getod->payment_norek ) : '' ),
                'image'             =>  $getod->payment_type === 0 ? '' : ( $getod->payment_type === 1 ? ($Config->apps()['storage']['URL'] . '/images/bank/' . $getod->bank_image) : $Config->apps()['storage']['URL'] . '/images/bank/cod.png'),
            ],
            'destination'   =>  [
                'id'                =>  $field['destination']['id'], // $getod->destination_id,
                'keep'              =>  $field['destination']['keep'], //$getod->destination_keep,
                'name'              =>  $field['destination']['name'], //$getod->destination_name,
                'label'             =>  $field['destination']['label'], //$getod->destination_label,
                'phone'             =>  $field['destination']['phone'], //$getod->destination_phone,
                'address'           =>  $field['destination']['address'], //$getod->destination_address,
                'kodepos'           =>  $field['destination']['kodepos'], //$getod->destination_kodepos,
                'array'             =>  $field['destination']['array'], //$getod->provinsi_id . ',' . $getod->city_id . ',' . $getod->kecamatan_id,
                'provinsi'          =>  $field['destination']['provinsi'], //$getod->provinsi_name,
                'city'              =>  $field['destination']['city'], //$getod->city_type . '. ' . ucwords(strtolower($getod->city_name)),
                'kecamatan'         =>  'Kec. ' . $field['destination']['kecamatan'], //ucwords(strtolower($getod->kecamatan_name))
            ],
            'shiping'       => [
                'type'              =>  $field['shiping']['type'] === '' ? 0 : $field['shiping']['type'], //$getod->shiping_type,
                'code'              =>  $field['shiping']['code'] === '' ? 0 : $field['shiping']['code'], //$getod->shiping_code,
                'courier_id'        =>  $getod->courier_id,
                'courier_price'     =>  $field['shiping']['courier_price'] === '' ? 0 : $field['shiping']['courier_price'], //$getod->courier_price,
                'courier_name'      =>  $field['shiping']['courier_name'] === '' ? 0 : $field['shiping']['courier_name'], //$getod->courier_name,
                'courier_service'   =>  $field['shiping']['courier_service'] === '' ? 0 : $field['shiping']['courier_service'], //$getod->courier_service,
                'courier_image'     =>  $Config->apps()['URL']['STORAGE'] . '/images/kurir/' . $getod->courier_image,
                'print_status'      =>  $getod->print_status,
                'cod'               =>  isset($field['shiping']['cod']) ? $field['shiping']['cod'] : 'no',
                'cod_cost'          =>  isset($field['payment']['biaya_cod']) ? $field['payment']['biaya_cod']:0,
                'weight_up'         =>  $getod->weight_up
            ],
            'origin'        =>  [
                'id'            =>  $getod->origin_id,
                'name'          =>  $getod->origin_name,    
                'label'         =>  $getod->origin_name === '' ? '' : ( $getod->origin_c_type . '. ' . $getod->origin_c_name . ' - ' . $getod->origin_p_name )
            ],
            'sales'         =>  $field['sales'],
            'bank'          =>  $banklist,
            'item'          =>  $listitem,
            'config'        =>  [
                'thisdate'      =>  date('Y-m-d', time())
            ],
            'promo'         =>  $promo,
            'update'        =>  $update,
            'price_show'    =>  $getod->type === 1 || $getod->type === 2 ? 'p' : 'r'
        ];

        return $data;
    }

    //
    public function getcart(Request $request)
    {
        $order_id = $request->orderid;

        $getcart = new \App\Http\Controllers\orders\widget;
        $getcart = $getcart->cart(['id'=>$order_id]);

        $data = [
            'message'       =>  '',
            'response'      =>  $getcart
        ];


        return response()->json($data, 200);
    }


    //update quantity
    public function updateqty(Request $request)
    {
        $order_id = $request->order_id;
        $item_id = $request->item_id;
        $quantity = $request->quantity;


        $cekorders = tblOrders::where([
            'id'        =>  $order_id
        ])->first();
        
        $getoi = tblOrderItems::where([
            'id'        =>  $item_id
        ])->first();

        //calc
        $upweight = ($getoi->weight * $quantity);
        $upprice = ($getoi->price * $quantity);
        $upprice_reseller = ($getoi->price_reseller * $quantity);

        $upoi = tblOrderItems::where([
            'id'            =>  $item_id
        ])
        ->update([
            'quantity'          =>  $quantity,
            'weight_total'      =>  $upweight,
            'price_total'       =>  $upprice,
            'price_total_reseller'  =>  $upprice_reseller
        ]);


        //update
        $updatefield = $this->updatefield([
            'order_id'      =>  $order_id,
            'price_total'   =>  $upprice,
            'price_total_reseller'      =>  $upprice_reseller
        ]);

        // $geto = tblOrders::where(['id'=>$order_id])
        // ->first();
        
        // $field = json_decode($geto->field, true);
        
        $data = [
            'message'       => '',
            'response'      =>  [
                'total'             =>  $updatefield['total'], //$field['payment']['total'],
                'total_reseller'    =>  $updatefield['reseller'], //$field['payment']['total_reseller'],
                'price_show'        =>  $cekorders->type === 1 || $cekorders->type === 2 ? 'p' : 'r'
            ]
        ];

        return response()->json($data,200);
    }

    //deleted items
    public function deleteitem(Request $request)
    {
        $order_id = $request->order_id;
        $item_id = $request->item_id;

        // check orders
        $cekorders = tblOrders::where([
            'id'        =>  $order_id
        ])->first();

        //cek if order upload checkout
        $ceko = tblOrders::where([
            'id'            =>  $order_id,
            'paid'          =>  1,
            'status'        =>  1
        ])->count();

        if( $ceko > 0)
        {
            $data = [
                'message'       =>  'Maaf kami tidak berhasil menghapus item orderan anda'
            ];
    
            return response()->json($data, 404);

        }


        // update order items
        $deloi = tblOrderItems::where([
            'id'            =>  $item_id,
            'status'        =>  1
        ])
        ->update(['status'=>0]);


        $cekoi = tblOrderItems::where([
            'order_id'      =>  $order_id,
            'status'        =>  1
        ])->count();

        if( $cekoi === 0) //delete orders
        {
            //update order status 1 to 0
            $delo = tblOrders::where([
                'id'            =>  $order_id,
                'status'        =>  1
            ])
            ->update(['status'  =>  0]);
                
        }
        else
        {
            

           
            //update:
            //tbl orders
            //tbl order checkout

            $updatefield = $this->updatefield([
                'order_id'      =>  $order_id
            ]);

            

        }


        // response
        $data = [
            'message'       =>  '',
            'total'         =>  $cekoi === 0 ? 0 : ($cekorders->type === 1 || $cekorders->type === 2 ? $updatefield['total'] : $updatefield['reseller'])
        ];

        return response()->json($data, 200);

    }


    //update field on orders
    public function updatefield($request)
    {

        $order_id = $request['order_id'];

        //get total
        $total = tblOrderItems::select(
            DB::raw('sum(weight) as weight'),
            DB::raw('sum(price_total) as total'),
            DB::raw('sum(price_total_reseller) as reseller')
        )
        ->where([
            'order_id'          =>  $order_id,
            'status'            =>  1
        ])->first();


        //
        $geto = tblOrders::from('orders as o')
        ->select(
            'o.id', 'o.type', 'o.uniq', 'o.token', 'o.invoice', 'o.user_id',
            'o.customer_id as customer_id',
            'c.name as customer_name',
            'c.gender as customer_gender',
            'c.phone as customer_phone',
            'c.email as customer_email',
            // shiping
            DB::raw('IFNULL(os.id, "") as shiping_id'),
            DB::raw('IFNULL(os.type, "") as shiping_type'),
            DB::raw('IFNULL(os.code, "") as shiping_code'),
            DB::raw('IFNULL(os.cod, 0) as shiping_cod'),
            DB::raw('IFNULL(os.courier_id, "") as courier_id'),
            DB::raw('IFNULL(os.courier_name, "") as courier_name'),
            DB::raw('IFNULL(os.noresi, "") as shiping_noresi'),
            DB::raw('IFNULL(os.courier_service, "") as courier_service'),
            DB::raw('IFNULL(os.courier_weight, "") as courier_weight'),
            DB::raw('IFNULL(os.courier_price, "") as courier_price'),
            DB::raw('IFNULL(os.origin_id, "") as origin_id'),
            //checkout
            DB::raw('IFNULL(oc.payment_id, 0) as payment_id'),
            DB::raw('IFNULL(oc.payment_type, "") as payment_type'),
            DB::raw('IFNULL(amp.name, "") as payment_label'),
            DB::raw('IFNULL(abl.name, "") as payment_name'),
            DB::raw('IFNULL(ca.id, "") as destination_id'),
            DB::raw('IFNULL(ca.provinsi, "") as destination_provinsi_id'),
            DB::raw('IFNULL(ca.city, "") as destination_city_id'),
            DB::raw('IFNULL(ca.kecamatan, "") as destination_kecamatan_id'),
            DB::raw('IFNULL(ca.name, "") as destination_name'),
            DB::raw('IFNULL(ca.label, "") as destination_label'),
            DB::raw('IFNULL(ca.phone, "") as destination_phone'),
            DB::raw('IFNULL(ca.kodepos, "") as destination_kodepos'),
            DB::raw('IFNULL(ca.address, "") as destination_address'),
            DB::raw('IFNULL(aok.name, "") as destination_kecamatan'),
            DB::raw('IFNULL(aoc.name, "") as destination_city'),
            DB::raw('IFNULL(aoc.type, "") as destination_city_type'),
            DB::raw('IFNULL(aop.name, "") as destination_provinsi'),
            // sales
            'u.name as sales_name', 'u.phone as sales_phone',
            'ucp.name as company', 'aoc2.name as company_city', 'aoc2.type as company_city_type', 'aop2.name as company_provinsi'
        )
        //customer
        ->leftJoin('customers as c', function($join)
        {
            $join->on('c.id', '=', 'o.customer_id');
        })
        // shiping
        ->leftJoin('order_shipings as os', function($join)
        {
            $join->on('os.order_id', '=', 'o.id')
            ->where(['os.status'=>1]);
        })
        //order checkouts
        ->leftJoin('order_checkouts as oc', function($join)
        {
            $join->on('oc.order_id', '=', 'o.id')
            ->where(['oc.status'=>1]);
        })
        ->leftJoin('app_metode_payments as amp', function($join)
        {
            $join->on('amp.id', '=', 'oc.payment_id');
        })
        ->leftJoin('app_bank_lists as abl', function($join)
        {
            $join->on('abl.id', '=', 'amp.bank_id');
        })
        //address
        ->leftJoin('customer_addresses as ca', function($join)
        {
            $join->on('ca.id', '=', 'os.destination_id');
        })
        ->leftJoin('app_origin_kecamatans as aok', function($join)
        {
            $join->on('aok.id', '=', 'ca.kecamatan');
        })
        ->leftJoin('app_origin_cities as aoc', function($join)
        {
            $join->on('aoc.id', '=', 'ca.city');
        })
        ->leftJoin('app_origin_provinsis as aop', function($join)
        {
            $join->on('aop.id', '=', 'aoc.provinsi_id');
        })
        //sales
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'o.user_id');
        })
        //sales
        ->leftJoin('user_companies as ucp', function($join)
        {
            $join->on('ucp.id', '=', 'o.company_id');
        })
        ->leftJoin('app_origin_cities as aoc2', function($join)
        {
            $join->on('aoc2.id', '=', 'ucp.city');
        })
        ->leftJoin('app_origin_provinsis as aop2', function($join)
        {
            $join->on('aop2.id', '=', 'ucp.provinsi');
        })
        ->where([
            'o.id'        =>  $order_id
        ])->first();


        //items
        $getitem = tblOrderItems::from('order_items as oi')
        ->select(
            'oi.quantity', 'p.name as name'
        )
        ->leftJoin('products as p', function($join)
        {
            $join->on('p.id', '=', 'oi.product_id');
        })
        ->where([
            'oi.order_id'          =>  $order_id,
            'oi.status'            =>  1
        ])->get();

        foreach($getitem as $row )
        {
            $productitem[] = '(' . $row->quantity .'x) ' . $row->name;
        }


        //if type order distributor
        if( $geto->type == '3' )
        {
        //     //
            $getcomp = tblUserCompanies::from('user_companies as uc')
            ->select(
                'uc.name', 'uc.contact',
                'asp.id', 'asp.kodepos', 'asp.label', 'asp.name as origin_name', 'asp.address', 'asp.phone', 'asp.kecamatan', 'asp.city', 'asp.provinsi',
                'aok.name as kecamatan_name',
                'aoc.name as city_name', 'aoc.type as city_type',
                'aop.name as provinsi_name',
                'ucp.name as sales_name', 'ucp.contact as sales_contact',
                'aocx.name as sales_city', 'aocx.type as sales_city_type',
                'aopx.name as sales_provinsi'
            )
            ->leftJoin('app_shiping_origins as asp', function($join)
            {
                $join->on('asp.company_id', '=', 'uc.id')
                ->where(['asp.status'=>1]);
            })
            ->leftJoin('app_origin_kecamatans as aok', function($join)
            {
                $join->on('aok.id', '=', 'asp.kecamatan');
            })
            ->leftJoin('app_origin_cities as aoc', function($join)
            {
                $join->on('aoc.id', '=', 'asp.city');
            })
            ->leftJoin('app_origin_provinsis as aop', function($join)
            {
                $join->on('aop.id', '=', 'asp.provinsi');
            })
            ->leftJoin('user_companies as ucp', function($join)
            {
                $join->on('ucp.id', '=', 'uc.produsen_id');
            })
            ->leftJoin('app_origin_cities as aocx', function($join)
            {
                $join->on('aocx.id', '=', 'ucp.city');
            })
            ->leftJoin('app_origin_provinsis as aopx', function($join)
            {
                $join->on('aopx.id', '=', 'ucp.provinsi');
            })
            ->where([
                'uc.id'            =>  $geto->customer_id
            ])->first();


            $customers = [
                'id'        =>  $geto->customer_id,
                'name'      =>  $getcomp->name,
                'gender'    =>  '',
                'phone'     =>  json_decode($getcomp->contact, true)['phone'],
                'email'     =>  json_decode($getcomp->contact, true)['email'],
            ];

            $destinations = [
                'id'            =>  $getcomp->id,
                'keep'          =>  0,
                'name'          =>  $getcomp->name,
                'label'         =>  $getcomp->label,
                'phone'         =>  $getcomp->phone,
                'array'         =>  $getcomp->provinsi .','.$getcomp->city.','.$getcomp->kecamatan,
                'address'       =>  $getcomp->address,
                'kodepos'       =>  $getcomp->kodepos,
                'kecamatan'     =>  ucwords(strtolower($getcomp->kecamatan_name)),
                'city'          =>  $getcomp->city_type . '. ' . ucwords(strtolower($getcomp->city_name)),
                'provinsi'      => $getcomp->provinsi_name
            ];

            $sales = [
                'name'          =>  $getcomp->sales_name,
                'phone'         =>  json_decode($getcomp->sales_contact, true)['phone'],
                'company'       =>  $getcomp->sales_name,
                'company_city'  =>  $getcomp->sales_city_type . '. ' . ucwords(strtolower($getcomp->sales_city)),
                'company_provinsi'  =>  $getcomp->sales_provinsi
            ];

        }
        else 
        {
            $customers = [
                'id'        =>  $geto->customer_id,
                'name'      =>  $geto->customer_name,
                'gender'    =>  $geto->customer_gender === 1 ? 'male' : 'female',
                'phone'     =>  $geto->customer_phone,
                'email'     =>  $geto->customer_email
            ];

            $destinations = [
                'id'            =>  $geto->destination_id,
                'keep'          =>  $geto->destination_keep,
                'name'          =>  $geto->destination_name,
                'label'         =>  $geto->destination_label,
                'phone'         =>  $geto->destination_phone,
                'array'         =>  $geto->destination_provinsi_id . ',' . $geto->destination_city_id . ',' .$geto->destination_kecamatan_id,
                'address'       =>  $geto->destination_address,
                'kodepos'       =>  $geto->destination_kodepos,
                'kecamatan'     =>  $geto->destination_kecamatan,
                'city'          =>  $geto->destination_city === '' ? '' : ( $geto->destination_city_type .'. ' . ucwords(strtolower($geto->destination_city))),
                'provinsi'      =>  $geto->destination_provinsi
            ];

            $sales = [
                'name'          =>  $geto->sales_name,
                'phone'         =>  $geto->sales_phone,
                'company'       =>  $geto->company,
                'company_city'  =>  $geto->company_city_type . '. ' . ucwords(strtolower($geto->company_city)),
                'company_provinsi'  =>  $geto->company_provinsi
            ];
        }


        //
        $productitem = implode(",", $productitem);

        if( $geto->payment_type <> "" || $geto->payment_id <> 0)
        {
            if( $geto->payment_type == "1")
            {
                $payment_metode = "Transfer Tunai";
                $payment_metode_type = "Trt";
                $payment_bank = 'Transfer Bank ' . $geto->payment_name;
                $biaya_cod = 0;
            }
            else if( $geto->payment_type == "2")
            {
                $payment_metode = "COD";
                $payment_metode_type = "COD";
                $payment_bank = $geto->payment_label;

                //count biaya COD
                $getInCourier = DB::table('app_courier_lists')
                ->where([
                    'id'            =>  $geto->courier_id
                ])->first();

                $biaya_cod = ( ($total->total + $geto->courier_price) * ($getInCourier->cod_cost_percent / 100));

            }
            else
            {
                $payment_metode = "";
                $payment_metode_type = "";
                $payment_bank = "";
                $biaya_cod = 0;
            }
        }
        else
        {
            $payment_metode = "";
            $payment_metode_type = "";
            $payment_bank = "";
            $biaya_cod = 0;
        }


        //biaya cod
        

        // data update
        $dataupdate = [
            'customers' =>  $customers,
            'payment'   =>  [
                'type'          =>  $geto->payment_type,
                'method'        =>  $payment_metode,
                'method_type'   =>  $payment_metode_type,
                'bank'          =>  $payment_bank,
                'total'             =>  $total->total,
                'total_reseller'    =>  $total->reseller,
                'biaya_cod'         =>  $biaya_cod
            ],
            'shiping'       =>  [
                'id'                =>  $geto->shiping_id,
                'type'              =>  $geto->shiping_type,
                'code'              =>  $geto->shiping_code,
                'noresi'            =>  $geto->shiping_noresi,
                'courier_name'      =>  $geto->courier_name,
                'courier_service'   =>  $geto->courier_service,
                'courier_price'     =>  $geto->courier_price,
                'weight'            =>  $geto->courier_weight,
                'origin_id'         =>  $geto->origin_id,
                'cod'               =>  $geto->shiping_cod === 0 ? "no" : "yes"
            ],
            'product'   =>  [
                'item'          =>  $productitem,
                'quantity'      =>  count($getitem)
            ],
            'destination'   =>  $destinations,
            'sales'         =>  $sales
        ];


        //search
        $search = $geto->invoice . ';' . $geto->customer_name . ';' . $geto->customer_phone . ( $geto->destination_address === '' ? '' : ';' .  $geto->destination_address );

        //update
        $updateo = tblOrders::where([
            'id'            =>  $order_id
        ])
        ->update([
            'search'    =>  $search,
            'field'     =>  json_encode($dataupdate)
        ]);

        $courier_price = $geto->courier_price === '' ? 0 : $geto->courier_price;

        $bayar = $biaya_cod === 0 ? (($total->total + $courier_price) - $geto->uniq) : ($total->total + $courier_price);
        $bayar_reseller = $biaya_cod === 0 ? (($total->reseller + $courier_price) - $geto->uniq) : ($total->reseller + $courier_price);

        //checkouts
        $upcheckouts = tblOrderCheckouts::where([
            'order_id'      =>  $order_id
        ])
        ->update([
            'total'             =>  $total->total,
            'total_reseller'    =>  $total->reseller,
            'bayar'             =>  $bayar,
            'bayar_reseller'    =>  $bayar_reseller
        ]);

        $data = [
            'total'     =>  $total->total,
            'reseller'  =>  $total->reseller
        ];

        return $data;
    }


    // SET DESTINATION
    public function setaddress(Request $request)
    {
        $Config = new Config;

        $order_id = $request->order_id;
        $address_id = $request->address_id;

        //update shiping
        $upshiping = tblOrderShipings::where([
            'order_id'          =>  $order_id,
            'status'            =>  1
        ])
        ->update([
            'courier_id'            =>  0,
            'courier_name'          =>  '',
            'courier_service'       =>  '',
            'courier_price'         =>  '',
            'destination_id'        =>  $address_id
        ]);


        //
        $updatefield = $this->updatefield([
            'order_id'      =>  $order_id
        ]);

        $data = [
            'message'       =>  ''
        ];

        return response()->json($data, 200);
    }

    // KEEP ADDRESS IN ORDERS
    public function keepaddress(Request $request)
    {
        $Config = new Config;

        $order_id = trim($request->order_id);
        $address_id = trim($request->address_id);
        $customer_id = trim($request->customer_id);


        //
        $chaddress = tblCustomerAddress::where([
            'customer_id'       =>  $customer_id,
            'status'            =>  1
        ])
        ->update(['keep'=>0]);

        $upaddress = tblCustomerAddress::where([
            'id'            =>  $address_id,
            'status'        =>  1
        ])
        ->update(['keep'=>1]);


        
        // CHANGE ORDER SHIPING
        // IF ORDER_ID NOT NULL
        


        $data = [
            'message'           =>  ''
        ];


        return response()->json($data, 200);



    }


    // CREATE CHECKOUT
    public function checkout($request)
    {
        $Config = new Config;

        //creaet checkout
        $order_id = $request['order_id'];
        
        //
        $geto = tblOrders::where([
            'id'            =>  $order_id
        ])->first();

        //
        $checkCo = tblOrderCheckouts::where([
            'order_id'        =>  $order_id,
            'status'          =>    1,
        ])->count();

        if( $checkCo == 0)
        {
            //getinfo orders
            $getod = tblOrders::from('orders as o')
            ->select(
                // 'os.id as origin_id',
                'o.uniq',
                DB::raw('IFNULL(ca.id, 0) as destination_id')
            )
            ->leftJoin('customer_addresses as ca', function($join)
            {
                $join->on('ca.customer_id', '=', 'o.customer_id')
                ->where(['ca.keep'  =>  1]);
            })
            ->where([
                'o.id'              =>  $order_id,
                'o.status'          =>  1
            ])
            ->first();

            //
            $datanewidco = [
                'value'     =>  tblOrderCheckouts::count(),
                'length'    =>  14
            ];

            $newidco = $Config->createnewidnew( $datanewidco );

            $getottal = tblOrderItems::select(
                DB::raw('sum(weight_total) as weight'),
                DB::raw('sum(price_total) as total'),
                DB::raw('sum(price_total_reseller) as reseller')
            )
            ->where([
                'order_id'          =>  $order_id,
                'status'            =>  1
            ])->first();

            $newco                  =   new tblOrderCheckouts;
            $newco->id              =   $newidco;
            $newco->order_id        =   $order_id;
            $newco->total           =   $getottal->total;
            $newco->total_reseller  =   $getottal->reseller;
            $newco->bayar           =   ($getottal->total - $getod->uniq);
            $newco->bayar_reseller  =   ($getottal->reseller - $getod->uniq);
            $newco->payment_type    =   0;
            $newco->payment_id      =   0;
            $newco->payment_date    =   '';
            $newco->bank_id         =   0;
            $newco->bank_user       =   '';
            $newco->bank_norek      =   '';
            $newco->bank_date       =   '';
            $newco->payment_total   =   0;
            $newco->paid_date       =   '';
            $newco->paid_user_id    =   0;
            $newco->status          =   1;
            $newco->save();



            //check shiping
            $chekshiping = tblOrderShipings::where([
                'order_id'          =>  $order_id,
                'status'            =>  1
            ])->count();


            //get origin

            if( $chekshiping == 0 )
            {
                $datanewidsp = [
                    'value'         =>  tblOrderShipings::count(),
                    'length'        =>  14
                ];

                $newidsp = $Config->createnewidnew( $datanewidco );
                // $codeShp = $invoice = date('ymd', time() ) . $Config->createuniq(['length'=>6,'value'=>$newidsp]) . 'SHP';

                // NEW CODE SHIPING
                $codeShp = 'HBP' . date('ymd', time() ) . $Config->createuniq(['length'=>6,'value'=>$newidsp]);

                //create shiping
                $newsp                  =   new tblOrderShipings;
                $newsp->id              =   $newidsp;
                $newsp->token           =   md5($newidsp);
                $newsp->type            =   0;
                $newsp->code            =   $codeShp;
                $newsp->order_id        =   $order_id;
                $newsp->courier_id      =   0;
                $newsp->courier_name    =   '';
                $newsp->courier_service =   '';
                $newsp->courier_weight  =   $getottal->weight;
                $newsp->courier_price   =   0;
                $newsp->cod             =   0;
                $newsp->origin_id       =   0;
                $newsp->origin_company_id   =   0;
                $newsp->destination_id      =   $geto->type === 3 ? $geto->customer_id : $getod->destination_id;
                $newsp->noresi            =   "";
                $newsp->print_status        =   0;
                $newsp->pickup_status       =   0;       
                $newsp->status              =   1;
                $newsp->save();
                
            }

            $data = [
                'checkout'  => $newco,
                'shiping'   => $newsp    
            ];
        }

        $upo = tblOrders::where([
            'id'        =>  $order_id
        ])
        ->update([
            'checkout'      =>  1
        ]);


    }


    // SET COST COURIER
    public function setcostcourier(Request $request)
    {
        $Config = new Config;


        $upshiping = tblOrderShipings::where([
            'order_id'          =>  $request->order_id,
            'status'            =>  1
        ])
        ->update([
            'courier_id'        =>  $request->courier_id,
            'courier_name'      =>  $request->courier_name,
            'courier_service'    =>  $request->courier_service,
            'courier_weight'    =>  $request->courier_weight,
            'courier_price'     =>  $request->courier_price,
            'cod'               =>  $request->cod_status === "yes" ? 1 : 0
        ]);


        //update table order checkout
        $upocheckout = tblOrderCheckouts::where([
            'order_id'          =>  $request->order_id
        ])
        ->update([
            'payment_type'      =>  0,
            'payment_id'        =>  0
        ]);


        $this->updatefield(['order_id'=>$request->order_id]);

        $data = [
            'message'       =>  '',
            'response'      =>  $request->order_id
        ];

    
        return response()->json($data, 200);
    }


    // SET METODE PAYMENT
    public function metodepayment(Request $request)
    {
        //
        $Config = new Config;

        //
        $order_id = $request->order_id;
        $payment_id = $request->payment_id;
        $payment_type = $request->payment_type;

        
        //change order checkout
        $upoc = tblOrderCheckouts::where([
            'order_id'          =>  $order_id,
            'status'            =>  1
        ])
        ->update([
            'payment_type'      =>  $payment_type,
            'payment_id'        =>  $payment_id
        ]);


        $this->updatefield(['order_id'=>$order_id]);

        $response = $this->view(['order_id'=>$order_id]);

        $data = [
            'message'   =>  '',
            'response'  =>  $response
        ];


        return response()->json($data, 200);
    }
    

    public function viewdetail(Request $request)
    {
        $Config = new Config;

        //
        if( $request->id == null )
        {
            return response()->json([
                'message'       =>  'Harap tentukan order ID'
            ], 401);
        }

        //check
        $checko = tblOrders::where([
            'id'            =>  $request->id,
            'status'        =>  1
        ])->count();


        if( $checko < 1 )
        {
            return response()->json([
                'message'       =>  'Order tidak ditemukan'
            ], 404);
        }


        $response = $this->view(['order_id'=>$request->id]);

        return $response;
    }


    public function viewcheck(Request $request)
    {
        $order_id = $request->id;

        $checko = tblOrders::where([
            'id'        =>  $order_id
        ])->first();


        if( $checko == null || $checko->status == 0 )
        {
            return response()->json([
                'message'       =>  'Maaf, Informasi order yang Anda minta tidak ditemukan'
            ], 404);
        }


        if( $checko->paid == 1 || $checko->payment == 1)
        {
            $data = [
                'status'        =>  2, //menunggu verifikasi pembayaran atau success
                'response'      =>  $this->view(['order_id'=>$order_id])
            ];

            return response()->json($data, 200);
        }


        $cart = new \App\Http\Controllers\orders\widget;
        $data = [
            'status'        =>  1, //keranjang
            'response'      =>  [
                'cart'          =>  $cart->cart(['id'=>$order_id]),
                'order_id'      =>  $order_id
            ]
        ];

        return response()->json($data, 200);


    }

    public function vieworder($request)
    {
        $order_id = $request['order_id'];

        //view orderan
        $geto = tblOrders::from('orders as o')
        ->select(
            'o.id', 'o.type',
            'oc.total as total', 'oc.total_reseller',
            'os.courier_id', 'os.courier_price', 'os.courier_name', 'os.courier_service',
            'amp.name as payment_type',
            'abl.name as payment',
            'ca.address as address', 'ca.name as address_name', 'ca.label as address_label', 'ca.kodepos', 'ca.phone as address_phone',
            'aop.name as provinsi', 'aoc.type as city_type', 'aoc.name as city',
            'aok.name as kecamatan'
        )
        ->leftJoin('order_checkouts as oc', function($join)
        {
            $join->on('oc.order_id', '=', 'o.id');
        })
        ->leftJoin('app_metode_payments as amp', function($join)
        {
            $join->on('amp.id', '=', 'oc.payment_id');
        })
        ->leftJoin('app_bank_lists as abl', function($join)
        {
            $join->on('abl.id', '=', 'amp.bank_id');
        })
        ->leftJoin('order_shipings as os', function($join)
        {
            $join->on('os.order_id', '=', 'o.id');
        })
        ->leftJoin('customer_addresses as ca', function($join)
        {
            $join->on('ca.id', '=', 'os.destination_id');
        })
        ->leftJoin('app_origin_provinsis as aop', function($join)
        {
            $join->on('aop.id', '=', 'ca.provinsi');
        })
        ->leftJoin('app_origin_cities as aoc', function($join)
        {
            $join->on('aoc.id', '=', 'ca.city');
        })
        ->leftJoin('app_origin_kecamatans as aok', function($join)
        {
            $join->on('aok.id', '=', 'ca.kecamatan');
        })
        ->where([
            'o.id'            =>  $order_id
        ])
        ->first();


        $getitem = tblOrderItems::from('order_items as oi')
        ->select(
            'oi.id',
            'oi.price', 'oi.price_reseller',
            'oi.price_total as total', 'oi.price_total_reseller as total_reseller', 'oi.weight', 'oi.weight_total', 'oi.quantity',
            'p.name as product_name'
        )
        ->leftJoin('products as p', function($join)
        {
            $join->on('p.id', '=', 'oi.product_id');
        })
        ->where([
            'oi.order_id'       =>  $order_id,
            'oi.status'         =>  1
        ])->get();


        foreach($getitem as $row)
        {
            $item[] = [
                'id'                    =>  $row->id,
                'qty'                   =>  $row->quantity,
                'price'                 =>  $geto->type === 1 ? $row->price : $row->price_reseller,
                'total'                 =>  $geto->type === 1 ? $row->total : $row->total_reseller,
                'weight'                =>  $row->weight,
                'weight_total'          =>  $row->weight_total,
                'product_name'          =>  $row->product_name
            ];
        }


        $response = [
            'id'            =>  $geto->id,
            'metode'        =>  $geto->payment === null ? $geto->payment_type : $geto->payment_type . ' Bank ' . $geto->payment,
            'total'         =>  $geto->type === 1 ? $geto->total : $geto->total_reseller,
            // 'checkout_status'   =>  
            // 'courier_status'    =>  $geto->courier_id === null ? 0 : ($geto->courier_id === 0 ? 0 : 1),
            'courier'       =>  [
                'id'        =>  $geto->courier_id === null ? 0 : ($geto->courier_id === 0 ? 0 : $geto->courier_id),
                'price'     =>  $geto->courier_price,
                'name'      =>  $geto->courier_name,
                'service'   =>  $geto->courier_service,
            ],
            'address_status'    =>  $geto->address === null ? 0 : 1,
            'address'       =>  [
                'address'       =>  $geto->address,
                'kodepos'       =>  $geto->kodepos,
                'address_label' =>  $geto->address_label,
                'address_name'  =>  $geto->address_name,
                'address_phone' =>  $geto->address_phone,
                'provinsi'      =>  $geto->provinsi,
                'city'          =>  $geto->city_type . '. ' . ucwords(strtolower($geto->city)),
                'kecamatan'     =>  'Kec. ' . ucwords(strtolower($geto->kecamatan)),
            ],
            'item'          =>  $item
        ];

        return $response;

    }



    //upload 
    public function upload(Request $request)
    {
        //
        $Config = new Config;

        //
        $CekAccount = new Account;
        $account = $CekAccount->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);
        
        //
        $order_id = $request->order_id;

        //cek data order
        $cekod = tblOrders::where([
            'id'            =>  $order_id
        ])->first();

        $type = $request->type;
        $bank = $request->bank;
        $name = $request->name;
        $date = $request->date;
        $total = $request->total;
        $file = $request->file('file');

        //
        $upoc = tblOrderCheckouts::where([
            'order_id'      =>  $order_id
        ])
        ->update([
            'payment_date'  =>  date('Y-m-d H:i:s', time()),
            'bank_id'       =>  $bank,
            'bank_user'     =>  $name,
            'bank_norek'    =>  '',
            'payment_total' =>  $total,
            'bank_date'     =>  $date,
        ]);

        
        // update order
        $upo = tblOrders::where([
            'id'            =>  $order_id
        ])
        ->update([
            'payment'       =>  1,
            'verify'        =>  $type === '2' ? 1 : 0
        ]);

        //
        $newid = $Config->createnewidnew([
            'value'     =>  tblOrderImages::count(),
            'length'    =>  15
        ]);
        $token = md5($newid);

        //
        $newimg = new tblOrderImages;
        $newimg->id             =   $newid;
        $newimg->token          =   $token;
        $newimg->url            =   $Config->apps()['storage']['URL'] . '/images/transfer/' . $token . '.jpg';
        $newimg->order_id       =   $order_id;
        $newimg->user_id        =   $account['id'];
        $newimg->status         =   1;
        $newimg->save();
        
        
        //upload transfer
        if( $type == '1')
        {
            $dataupload = [
                'name'          =>  $token,
                'file'          =>  $file,
                'path'          =>  'images/transfer/',
                "URL"           =>  $Config->apps()["URL"]["STORAGE"] . "/s3/upload/transfer"
            ];
    
            $upload = new \App\Http\Controllers\tdparty\s3\herbindo;
            $upload = $upload->transfer($dataupload);
        }

        //add notification
        $datanotif = [
            "order_id"          =>  $order_id,
            "account"           =>  $account,
        ];

        //creaet notif
        $addnotif = new \App\Http\Controllers\notification\index;
        $addnotif = $addnotif->verifPayment($datanotif);


        //log customers jika type order 1, 2 dan 4
        if( $cekod->type == '1' || $cekod->type == '2' || $cekod->type == '4')
        {
            $datalog = [
                'customer_id'       =>  $cekod->customer_id,
                'user_id'           =>  $cekod->user_id,
                'order_id'          =>  $order_id,
                'invoice'           =>  $cekod->invoice
            ];

            $addLogs = new \App\Http\Controllers\log\customers\manage;
            $addLogs = $addLogs->CBT($datalog);

        }

        //view orders
        $vieworder = $this->view(['order_id'=>$order_id]);

        //
        $data = [
            'message'       =>  '',
            'response'      =>  $vieworder,
            "type"          =>  $type,
            "file"          =>  $file
        ];


        return response()->json($data, 200);
    }


    // delete order
    public function delete(Request $request)
    {
        $Config = new Config;

        //get account
        // $account = $Refresh['refresh']['account'];
        $CekAccount = new Account;
        $account = $CekAccount->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);

        $sublevel = $account['sub_level'];

        $cek = tblOrders::where([
            'id'        =>  $request->order_id,
            'status'    =>  1
        ])->first();


        //null
        if( $cek == null )
        {

            $data = [
                'message'           =>  'Maaf, Order tidak ditemukan',
                // 'refresh'           =>  $Refresh
            ];
    
    
            return response()->json($data, 404);
        }

        // blocked
        if( $cek->paid == 1 || $cek->payment == 1)
        {

            if( $sublevel != '99' && $sublevel != '1' && $sublevel != '4')
            {

                $data = [
                    'message'           =>  'Maaf, Pembatalan order gagal di proses',
                    // 'refresh'           =>  $Refresh,
                    'sublevel'          =>  $sublevel
                ];
        
        
                return response()->json($data, 401);
            }
            
        }
        

        $delete = tblOrders::where([
            'id'            =>  $request->order_id,
            'status'        =>  1
        ])
        ->update([
            'status'        =>  0
        ]);

        //
        $data = [
            'message'           =>  '',
            // 'refresh'           =>  $Refresh
        ];


        return response()->json($data, 200);
    }



    // VERIFIKASI ORDER
    public function checkveriforders(Request $request)
    {
        $Config = new Config;

        //
        $order_id = $request->id;

        //
        $cek = tblOrders::where([
            'id'            =>  $order_id,
            'status'        =>  1
        ])->first();

        if( $cek == null )
        {
            
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }


        $upload = tblOrders::from('orders as o')
        ->select(
            'oc.bank_user', 'oc.payment_total',
            'oc.payment_total',
            'abl.label as bank_name',
            'oc.payment_date as date',
            'oi.url as images',
            'u.name as user_name'
        )
        ->leftJoin('order_checkouts as oc', function($join)
        {
            $join->on('oc.order_id', '=', 'o.id');
        })
        ->leftJoin('app_bank_lists as abl', function($join)
        {
            $join->on('abl.id', '=', 'oc.bank_id');
        })
        ->leftJoin('order_images as oi', function($join)
        {
            $join->on('oi.order_id', '=', 'o.id');
        })
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'oi.user_id');
        })
        ->where([
            'o.id'          =>  $order_id
        ])
        ->first();

        $data = [
            'message'       =>  '',
            'response'      =>  [
                'orders'        =>  $this->view(['order_id'=>$order_id]),
                'upload'        =>  [
                    'bank'              =>  $upload->bank_name,
                    'images'            =>  $upload->images,
                    'date'              =>  date('d/m/Y H.s', strtotime($upload->date)),
                    'user'              =>  $upload->user_name,
                    'total'     =>  $upload->payment_total
                ]
            ]
        ];


        return response()->json($data, 200);

    }


    //verification
    public function verification(Request $request)
    {

        $CekAccount = new Account;
        $account = $CekAccount->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);

        $order_id = $request->order_id;
        
        //
        $cek = tblOrders::where([
            'id'            =>  $order_id,
            'paid'          =>  0
        ])
        ->first();


        if( $cek == null)
        {
            $data = [
                'message'           =>  'Opss... Data tidak ditemukan'
            ];
    
            return response()->json($data, 404);
        }


        //update
        $upo = tblOrders::where([
            'id'            =>  $order_id,
            'paid'          =>  0
        ])
        ->update([
            'paid'      =>  1,
        ]);

        if( $upo )
        {
            $upoc = tblOrderCheckouts::where([
                'order_id'      =>  $order_id,
                'status'        =>  1
            ])
            ->update([
                'paid_date'     =>  date('Y-m-d H:i:s', time()),
                'paid_user_id'  =>  $account['id']
            ]);

            
            //data notification
            $datanotif = [
                "account"           =>  $account,
                "order_id"          =>  $order_id
            ];

            $addnotif = new \App\Http\Controllers\notification\index;
            $addnotif = $addnotif->verifSuccess($datanotif);

            //log customers
            if( $cek->type == '1' || $cek->type == '2' || $cek->type == '4')
            {
                $datalog = [
                    'customer_id'       =>  $cek->customer_id,
                    'user_id'           =>  $cek->user_id,
                    'order_id'          =>  $order_id,
                    'invoice'           =>  $cek->invoice
                ];

                $addLogs = new \App\Http\Controllers\log\customers\manage;
                $addLogs = $addLogs->CST($datalog);

            }

        }


        

        $data = [
            "message"           =>  "",
            "notif"             =>  $addnotif
        ];

        return response()->json($data, 200);
    }


}