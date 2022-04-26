<?php
namespace App\Http\Controllers\courier;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\app_courier_lists as tblCourierLists;
use DB;

class index extends Controller
{
    //
    public function list(Request $request)
    {

        $getlist = tblCourierLists::where([
            'status'        =>  1
        ])
        ->orderBy('id', 'asc')
        ->get();


        if( count($getlist) > 0 )
        {

            foreach($getlist as $row)
            {
                $list[] = [
                    'id'            =>  $row->id,
                    'name'          =>  $row->name,
                    'weight_up'     =>  $row->weight_up,
                    'weight_type'   =>  $row->weight_type
                ];
            }

            $status = 200;
        }
        else
        {
            $status = 404;
        }


        $data = [
            'message'           =>  $status === 200 ? '' : 'Data tidak ditemukan',
            'response'          =>  $status === 200 ? $list : ''
        ];

        return response()->json($data, $status);
    }


    public function checking(Request $request)
    {

        //
        $origin_id = trim($request->origin_id);
        $destination = trim($request->destination);
        $courier = trim($request->courier);
        $product_id = trim($request->product_id);
        $weight = trim($request->weight);
        $destination = trim($request->destination);
        
        //get config courier
        $getcc = tblCourierLists::from('app_courier_lists as cl')
        ->select(
            'cl.name', 'cc.id', 'cc.database_origin', 'cc.dir', 'cc.host', 'cc.sub_host', 'cc.key', 'cc.user', 'cc.password'
        )
        ->leftJoin('app_courier_configs as cc', function($join)
        {
            $join->on('cc.id', '=', 'cl.config_id');
        })
        ->where([
            'cl.id'        =>  $courier,
            'cc.status'     =>  1
        ])->first();

        if( $getcc->database_origin === '' || $getcc->database_origin === null )
        {

            $data = [
                'message'       =>  'Maaf, layanan untuk courier yang Anda minta belum tersedia'
            ];
            
            return response()->json($data, 404);
        }
        else
        {

            //
            $dataloc =[
                'origin_id'         =>  $origin_id,
                'destination'       =>  $destination,
                'database_origin'     =>  $getcc->database_origin
            ];


            $decodeOrigin = $this->decodeOrigin($dataloc);
            
            
            //
            $dataroot = [
                'courier_id'    =>  $courier,
                'courier'       =>  $decodeOrigin,
                'weight'        =>  $weight,
                'config'        =>  $getcc,
                'host'          =>  $getcc->host . json_decode($getcc->sub_host)->cost
            ];

            //
            $root = '\App\Http\Controllers\tdparty\courier\/' . $getcc->dir . '\index';
            $root = str_replace('/', '', $root);
            $root = new $root;
            $root = $root->single($dataroot);
    
            return $root;

        }

        // return response()->json([
        //     'message'       =>  $getcc->host
        // ], 200);
        
    }


    //function convert code orgin and destinatin
    public function decodeOrigin($request)
    {
        //
        $getorigin = DB::table('app_shiping_origins')
        ->where([
            'id'            =>  $request['origin_id']
        ])
        ->first();


        $desti = explode(",", $request['destination']);

        //origin
        $origin = $getorigin->kecamatan === 0 ? $getorigin->city : $getorigin->kecamatan;
        $origin_type = $getorigin->kecamatan === 0 ? 'origin_city' : 'origin_kecamatan';

        $dtorigin = DB::table($request['database_origin'])
        ->where([
            $origin_type    =>  $origin,
            'status'        =>  1
        ])->first();


        //destination
        $destination      = $desti[2] === '0' ? $desti[1] : $desti[2];
        $destination_type  = $desti[2] === '0' ? 'origin_city' : 'origin_kecamatan';

        $dtdestination = DB::table($request['database_origin'])
        ->where([
            $destination_type    =>  $destination,
            'status'        =>  1
        ])->first();


        //
        $data = [
            'origin'         =>  $origin_type === 'origin_city' ? $dtorigin->city_code : $dtorigin->district_code,
            'origin_type'    =>  $origin_type,
            'destination'      =>  $destination_type === 'origin_city' ? $dtdestination->city_code : $dtdestination->district_code,
            'destination_type'  =>  $destination_type
        ];

        return $data;

    }


    //cost
    public function cost(Request $request)
    {

        $origin = trim($request->origin);
        $destination = trim($request->destination);
        $courier = trim($request->courier);
        $weight = trim($request->weight);
        $order_type = trim($request->otype);


        $getcc = tblCourierLists::from('app_courier_lists as cl')
        ->select(
            'cl.name', 'cc.id', 'cc.database_origin', 'cc.dir', 'cc.host', 'cc.sub_host', 'cc.key', 'cc.user', 'cc.password'
        )
        ->leftJoin('app_courier_configs as cc', function($join)
        {
            $join->on('cc.id', '=', 'cl.config_id');
        })
        ->where([
            'cl.id'         =>  $courier,
            'cc.status'     =>  1
        ])->first();

        if( $getcc === null || $getcc->database_origin === '' || $getcc->database_origin === null )
        {

            $data = [
                'message'       =>  'Maaf, layanan untuk courier yang Anda minta belum tersedia'
            ];
            
            return response()->json($data, 404);
        }
 

        
        if( $order_type == '3' )
        {
            $getdestination = DB::table('app_shiping_origins')
            ->where([
                'id'            =>  $destination
            ])->first();
        }
        else
        {

            $getdestination = DB::table('customer_addresses')
            ->where([
                'id'            =>  $destination
            ])->first();
        }



        $dataloc =[
            'origin_id'         =>  $origin,
            'destination'       =>  $getdestination->provinsi . ',' . $getdestination->city . ',' . $getdestination->kecamatan,
            'database_origin'     =>  $getcc->database_origin,
            // 'prov'              =>  $getdestination
        ];


        $decodeOrigin = $this->decodeOrigin($dataloc);
        

        
        //
        $dataroot = [
            'courier_id'    =>  $courier,
            'courier'       =>  $decodeOrigin,
            'weight'        =>  $weight,
            'config'        =>  $getcc,
            'host'          =>  $getcc->host . json_decode($getcc->sub_host)->cost
        ];


        $root = '\App\Http\Controllers\tdparty\courier\/' . $getcc->dir . '\index';
        $root = str_replace('/', '', $root);
        $root = new $root;
        $root = $root->cost($dataroot);

        return $root;
        // return response()->json($dataroot, 200);

    }





}