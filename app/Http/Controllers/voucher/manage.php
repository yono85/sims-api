<?php
namespace App\Http\Controllers\voucher;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\app_promos as tblAppPromos;
use App\order_promos as tblOrderPromos;
use App\orders as tblOrders;
use App\Http\Controllers\config\index as Config;
use DB;

class manage extends Controller
{
    //
    public function list(Request $request)
    {

        $now = strtotime(date('Y-m-d',time()));

        $getdata = tblAppPromos::from('app_promos as ap');
        if( trim($request->level) == '1' || trim($request->level) == '3')
        {
            $getdata = $getdata->where([
                'ap.company_id'     =>  trim($request->compid)
            ]);
        }
        if( trim($request->level) == '2' && trim($request->produsenid) != '0')
        {
            $getdata = $getdata->where([
                'ap.company_id'     =>  trim($request->produsenid)
            ]);
        }
        $getdata = $getdata->where([
            ['ap.start_date', '<=', $now],
            ['ap.expire_date', '>', $now],
            ['ap.status', '=', 1]
        ]);
        $count = $getdata->count();


        if( $count > 0 )
        {
            $getdata = $getdata->get();

            foreach($getdata as $row)
            {
                $list[] = [
                    'id'        =>  $row->id,
                    'code'      =>  $row->code,
                    'name'      =>  $row->name,
                    'detail'    =>  $row->description
                ];
            }

            $data = [
                'message'   =>  '',
                'list'      =>  $list
            ];

            return response()->json($data, 200);
        }
        else
        {
            $data = [
                'message'       =>  'Promo saat ini tidak tersedia',
                'produsen'      =>  trim($request->produsenid),
                'compid'        =>  trim($request->compid)
            ];
    
            return response()->json($data, 404);
        }
    }


    //
    public function set(Request $request)
    {
        //
        $order_id = trim($request->order_id);
        $voucher_id = trim($request->voucher_id);

        $now = strtotime(date('Y-m-d', time()));

        //cek voucher
        $cek = tblAppPromos::where([
            ['id', '=', $voucher_id],
            ['expire_date', '>=', $now],
            ['status','=',1]
        ])
        ->first();

        if( $cek == null)
        {
            $data = [
                'message'       =>  'Promo ini tidak ditemukan atau sudah berlaku'
            ];

            return response()->json($data,404);
        }

        //CEK TYPE
        if( $cek->type == 1)
        {

            $data = $this->product($request,$cek);
            return $data;
        }        
    }

    // PRODUCT
    private function product($request,$dat)
    {

        //get data accoun
        $account = new \App\Http\Controllers\account\index;
        $account = $account->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);

        //
        $order_id = trim($request->order_id);
        $voucher_id = trim($request->voucher_id);

        if( $dat->item_id == 0)
        {
            //all
            $cekoi = DB::table('order_items as oi')
            ->select(
                'oi.id', 'oi.quantity', 'oi.price',
                'p.id as product_id', 'p.name'
            )
            ->leftJoin('products as p', function($join)
            {
                $join->on('p.id', '=', 'oi.product_id');
            })
            ->where([
                ['oi.order_id', '=', $order_id],
                ['oi.quantity', '>=', $dat->min],
                ['oi.status', '=', 1]
            ])
            ->get();

            if( count($cekoi) > 0)
            {
                $total = 0;
                foreach($cekoi as $row)
                {
                    $calcqty = $this->loopCalc($row->quantity, $dat->min);
                    
                    $list[] = [
                        'id'        =>  $row->id,
                        'product_id'    =>  $row->product_id,
                        'name'      =>  $row->name,
                        'qty'       =>  $row->quantity,
                        'min'       =>  $calcqty,
                        'total'     =>  ($row->price * $calcqty)
                    ];

                    $total += ($row->price * $calcqty);
                }
                
                $data = [
                    'promo_id'  =>  $voucher_id,
                    'type'      =>  $dat->type,
                    'user_id'   =>  $account['id'],
                    'order_id'  =>  $order_id,
                    'list'      =>  $list,
                    'total'     =>  $total
                ];

                $create = $this->add($data);

                $data = [
                    'message'       =>  '',
                    'total'         =>  $total,
                    'status'        =>  1
                ];

                return response()->json($data,200);
            }
            else
            {
                //
                $this->remove($request);

                //
                $data = [
                    'message'   =>  'Promo tidak bisa digunakan pada orderan ini',
                    'total'     =>  0,
                    'status'    =>  0
                ];

                return response()->json($data, 404);
            }
        }
    }

    //
    private function add($request)
    {
        //
        $Config = new Config;

        //
        $cek = tblOrderPromos::where([
            'order_id'        =>  $request['order_id']
        ])
        ->first();

        //
        $list = json_encode($request['list']);
        $list = trim($list, '[]');

        //
        $field = [
            'list'      =>  $list
        ];
        $field = stripslashes(json_encode($field));

        if( $cek == null)
        {
            //create new
            $newid = $Config->createnewidnew([
                'value'=>tblOrderPromos::count(),
                'length'=>15
            ]);

            $addPromo = new tblOrderPromos;
            $addPromo->id               =  $newid;
            $addPromo->type             =   $request['type'];
            $addPromo->promo_id         =   $request['promo_id'];
            $addPromo->order_id         =   $request['order_id'];
            $addPromo->field            =   $field;
            $addPromo->total            =   $request['total'];
            $addPromo->user_id          =   $request['user_id'];
            $addPromo->status           =   1;
            $addPromo->save();

            //
            $promoid = $newid;
        }
        else
        {
            //
            $update = tblOrderPromos::where([
                'id'        =>  $cek->id
            ])
            ->update([
                'field'         =>  $field,
                'total'         =>  $request['total'],
                'status'        =>  1
            ]);
            $promoid = $cek->id;

        }

        //
        $updateod = DB::table('orders')
        ->where([
            'id'        =>  $request['order_id']
        ])
        ->update([
            'promo_id'      =>  $promoid
        ]);

    }

    //
    private function remove($request)
    {
        //
        $update = tblOrderPromos::where([
            'order_id'      =>  $request->order_id
        ])
        ->update([
            'field'         =>  '',
            'total'         =>  0,
            'status'        =>  0
        ]);
    }

    public function update($request)
    {
        $order_id = $request['order_id'];
        $promo_id = $request['promo_id'];

        //
        $now = strtotime(date('Y-m-d', time()));

        //
        $cekpromo = tblOrderPromos::where([
            'id'        =>  $promo_id
        ])->first();
        
        //cek voucher
        $cek = tblAppPromos::where([
            ['id', '=', $cekpromo->promo_id],
            ['expire_date', '>=', $now],
            ['status','=',1]
        ])
        ->first();

        // jika cek app promo tidak ditemukan atau expire maka hapus
        if( $cek == null)
        {
            $updateod = DB::table('orders')
            ->where([
                'id'            =>  $order_id
            ])
            ->update([
                'promo_id'      =>  0
            ]);

            $uppromo = tblOrderPromos::where([
                'id'        =>  $promo_id
            ])
            ->update([
                'status'            =>  0
            ]);
            return;
        }

        //CEK TYPE jika type 1 maka product
        if( $cek->type == 1)
        {

            //JIKA ITEM ID 0 maka semua product
            if($cek->item_id == 0)
            {
                //cek produk pada order items
                $cekoi = DB::table('order_items as oi')
                ->select(
                    'oi.id', 'oi.quantity', 'oi.price',
                    'p.id as product_id', 'p.name'
                )
                ->leftJoin('products as p', function($join)
                {
                    $join->on('p.id', '=', 'oi.product_id');
                })
                ->where([
                    ['oi.order_id', '=', $order_id],
                    ['oi.quantity', '>=', $cek->min],
                    ['oi.status', '=', 1]
                ])
                ->get();

                //JIKA DITEMUKAN DI ORDER ITEMS
                if( count($cekoi) > 0)
                {

                    $total = 0;
                    foreach($cekoi as $row)
                    {
                        $calcqty = $this->loopCalc($row->quantity, $cek->min);
                        
                        $list[] = [
                            'id'            =>  $row->id,
                            'product_id'    =>  $row->product_id,
                            'name'          =>  $row->name,
                            'qty'           =>  $row->quantity,
                            'min'           =>  $calcqty,
                            'total'         =>  ($row->price * $calcqty)
                        ];

                        $total += ($row->price * $calcqty);
                    }
                    
                    //EXTRACT LIST
                    $list = json_encode($list);
                    $list = trim($list, '[]');

                    //CREATE FIELD
                    $field = [
                        'list'      =>  $list
                    ];
                    $field = stripslashes(json_encode($field));

                    //update
                    $update = tblOrderPromos::where([
                        'id'        =>  $promo_id
                    ])
                    ->update([
                        'field'         =>  $field,
                        'total'         =>  $total,
                        'status'        =>  1
                    ]);

                }
                else
                {
                    //
                    $update = tblOrderPromos::where([
                        'order_id'      =>  $order_id
                    ])
                    ->update([
                        'field'         =>  '',
                        'total'         =>  0,
                        'status'        =>  0
                    ]);
                }
            }

        } 

    }


    //function
    private function loopCalc($n,$m)
    {
        $value = $n;
        $multiple = $m;

        $calc = floor($value / $multiple);

        return $calc;
    }


    public function delete(Request $request)
    {
        $promo_id = trim($request->promo_id);


        //CEK
        $cek = tblOrderPromos::where([
            'id'        =>  $promo_id
        ])->first();

        if( $cek == null )
        {
            $data = [
                'message'       =>  'Opss.. Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }


        //update
        $uppromo = tblOrderPromos::where([
            'id'        =>  $promo_id
        ])
        ->update([
            'field'     =>  '',
            'total'     =>  0,
            'status'    =>  0
        ]);

        $upod = tblOrders::where([
            'id'        =>  $cek->order_id
        ])
        ->update([
            'promo_id'      =>  0
        ]);

        $data = [
            'message'       =>  '',
            'response'      =>  [
                'total'     =>  0
            ]
        ];

        return response()->json($data,200);
    }


}