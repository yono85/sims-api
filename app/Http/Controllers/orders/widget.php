<?php
namespace App\Http\Controllers\orders;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\orders as tblOrders;
use App\order_items as tblOrderItems;
use App\order_shipings as tblOrderShipings;
use App\Http\Controllers\config\index as Config;
use App\Http\Controllers\access\manage as Refresh;
use DB;

class widget extends Controller
{

    //
    public function cart($request)
    {
        $Config = new Config;

        //
        $id = $request['id'];

        $getdata = tblOrders::from('orders as o')
        ->select(
            'o.id', 'o.type', 'o.invoice', 'o.field', 'o.company_id',
            DB::raw('IFNULL(os.destination_id, 0) as destination')
        )
        ->leftjoin('order_shipings as os', function($join)
        {
            $join->on('os.order_id', '=', 'o.id');
        })
        ->where([
            'o.id'         =>  $id
        ])
        ->first();

        //field
        $field = json_decode($getdata->field, true);

        //listitem
        $getlistitem = tblOrderItems::from('order_items as oi')
        ->select(
            'oi.id',
            'oi.price',
            'oi.price_reseller',
            'oi.quantity',
            'p.name as product_name',
            'p.weight as product_weight',
            'p.weight_type as product_weight_type',
            'p.max as product_max',
            'pi.id as product_image'
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
            'oi.order_id'          =>  $getdata->id,
            'oi.status'            =>  1
        ])
        ->get();

        foreach($getlistitem as $row)
        {
            // $image = $Config->apps()['storage']['URL'] .'/images/product/' .$row->product_image. '.jpg';

            $images = $row->product_image === null ? 'none/no-product-image.png' : 'product/' . $row->product_image . '.jpg';

            $listitem[] = [
                'id'                =>  $row->id,
                'price'             =>  $row->price,
                'price_reseller'    =>  $row->price_reseller,
                'quantity'          =>  $row->quantity,
                'product_name'      =>  $row->product_name,
                'product_weight'    =>  $row->product_weight,
                'product_weight_type'   =>  $row->product_weight_type,
                'product_max'           =>  $row->product_max,
                'product_image'         =>  $Config->apps()['storage']['URL'] .'/images/' .$images,
            ];
        }

        $data = [
            'id'            =>  $getdata->id,
            'type'          =>  $getdata->type,
            'invoice'       =>  $getdata->invoice,
            'customer'      =>  $field['customers'],
            'payment'       =>  $field['payment'],
            'destination'   =>  $getdata->destination,
            'item'          =>  $listitem,
            'price_show'    =>  $getdata->type === 1 || $getdata->type === 2 ? 'p' : 'r'
        ];


        return $data;
    }

    // CHECKOUTS
    public function checkout(Request $request)
    {
        $Config = new Config;
        
        //
        $order_id = $request->order_id;
        $checkod = tblOrders::where([
            "id"        =>  $order_id,
            "checkout"  =>  0
        ])->count();

        //create checkout
        if( $checkod > 0 )
        {
            $datacreate = [
                "order_id"      =>  $order_id
            ];

            $create = new \App\Http\Controllers\orders\manage;
            $create = $create->checkout($datacreate);
        }
        else
        {
            //update shiping
            // get info orders
            $getoi = tblOrderItems::select(
                DB::raw("sum(weight_total) as weight"),
            )
            ->where([
                "order_id"          =>  $order_id,
                "status"            =>  1
            ])->first();

            // UPDATE SHIPING WEIGHT
            $upshiping = tblOrderShipings::where([
                "order_id"      =>  $order_id,
                "status"        =>  1
            ])
            ->update([
                "courier_weight"        =>  $getoi->weight,
                "destination_id"        =>  $request->destination_id
            ]);
        }

        //checking promo
        $getod = tblOrders::where([
            'id'        =>  $order_id
        ])->first();

        if($getod->promo_id != 0)
        {
            $datapromo = [
                'order_id'      =>  $order_id,
                'promo_id'      =>  $getod->promo_id
            ];

            $promo = new \App\Http\Controllers\voucher\manage;
            $promo = $promo->update($datapromo);
        }
        
        //UPDATE FIELD
        $upfield = new \App\Http\Controllers\orders\manage;
        $upfield = $upfield->updatefield(["order_id"=>$order_id]);

        // VIEW ORDERS
        $vieworder = new \App\Http\Controllers\orders\manage;
        $vieworder = $vieworder->view(["order_id"=>$order_id]);

        $data = [
            "message"       =>  "",
            "response"      =>  $vieworder
        ];

        return response()->json($data, 200);
    }


    public function setpayment(Request $request)
    {
        $Config = new Config;

        //
        $order_id = $request->order_id;
        $notes = trim($request->notes);

        //ceking refresh
        // $Refresh = new Refresh;
        // $Refresh = $Refresh->refresh();
        $cekorders = tblOrders::where([
            'id'            =>  $order_id
        ])->first();

        //
        $upnotes = tblOrders::where([
            'id'            =>  $order_id
        ])
        ->update([
            'notes'         =>  $notes,
            'verify'        =>  $cekorders->type === 3 ? 1 : 0
            // 'verify'        =>  $cekorders->type === 3 || $cekorders->type === 4 ? 1 : 0   
        ]);

        $view = new \App\Http\Controllers\orders\manage;
        $view = $view->view(['order_id'=>$order_id]);

        //data
        $data = [
            'message'           =>  '',
            // 'refresh'           =>  $Refresh,
            'response'          =>  $view
        ];

        return response()->json($data, 200);

    }

}