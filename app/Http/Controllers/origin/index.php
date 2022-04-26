<?php
namespace App\Http\Controllers\origin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\orders as tblOrders;
use App\app_shiping_origins as tblAppShipingOrigins;
use App\order_shipings as tblOrderShipings;


class index extends Controller
{
    //
    public function list(Request $request)
    {

        //
        $key = $request->header('key');
        $order_id = trim($request->id);


        //account
        $account = new \App\Http\Controllers\account\index;
        $account = $account->viewtype([
            'type'       =>  'key',
            'token'     =>  $key
        ]);


        $cekorders = tblOrders::where([
            'id'        =>  $order_id
        ])->first();

        if( $cekorders == null)
        {
            $data = [
                'message'       =>  'Data Tidak ditemukan',
            ];
    
            return response()->json($data, 404);
        }

        //get shiping origin
        $getdata = tblAppShipingOrigins::from('app_shiping_origins as aso')
        ->select(
            'aso.id', 'aso.name',
            'aop.name as provinsi_name',
            'aoc.name as city_name', 'aoc.type as city_type'
        )
        ->leftJoin('app_origin_provinsis as aop', function($join)
        {
            $join->on('aop.id', '=', 'aso.provinsi');
        })
        ->leftJoin('app_origin_cities as aoc', function($join)
        {
            $join->on('aoc.id', '=', 'aso.city');
        })
        ->where([
            // 'aso.company_id'        =>  $account['config']['company_id'],
            'aso.status'            =>  1
        ]);
        if( $cekorders->type == '1' || $cekorders->type == '2')
        {
            $getdata = $getdata->where([
                 'aso.company_id'        =>  $account['config']['company_id']
            ]);
        }
        if( $cekorders->type == '3' || $cekorders->type == '4' )
        {
            $getdata = $getdata->where([
                 'aso.company_id'        =>  $account['config']['produsen_id']
            ]);
        }
        $getdata = $getdata->get();


        if( count($getdata) < 1)
        {
            $data = [
                'message'       =>  'Data Tidak ditemukan',
            ];
    
            return response()->json($data, 404);
        }

        
        //
        foreach($getdata as $row)
        {
            $list[] = [
                'id'        =>  $row->id,
                'name'          =>  $row->name,
                'label'         =>  $row->city_type . '. ' . ucwords(strtolower($row->city_name)) . ' - ' . $row->provinsi_name,
            ];
        }


        $data = [
            'message'       =>  '',
            'list'          =>  $list
        ];


        return response()->json($data, 200);
    }


    //
    public function set(Request $request)
    {
        $order_id = $request->order_id;
        $origin_id = $request->origin_id;

        $getorigin = tblAppShipingOrigins::where([
            'id'        =>  $origin_id
        ])->first();

        //update tbl order shipings
        $upshiping = tblOrderShipings::where([
            'order_id'      =>  $order_id,
            'status'        =>  1
        ])
        ->update([
            'origin_id'             =>  $origin_id,
            'origin_company_id'     =>  $getorigin->company_id,
            'courier_id'            =>  0,
            'courier_name'          =>  '',
            'courier_service'       =>  '',
            'courier_weight'        =>  0,
            'courier_price'         =>  0
        ]);

        //update field order
        // $upfield = new \App\Http\Controllers\orders\manage;
        // $upfield = $upfield->updatefield([
        //     'order_id'      =>  $order_id
        // ]);

        $data = [
            'message'       =>  '',
            'origin_id'     =>  $origin_id
        ];

        return response()->json($data, 200);
    }
}