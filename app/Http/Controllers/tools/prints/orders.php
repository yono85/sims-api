<?php
namespace App\Http\Controllers\tools\prints;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\orders as tblOrders;
use App\order_shipings as tblOrderShipings;
use App\Http\Controllers\config\index as Config;

class orders extends Controller
{
    //
    public function shiping(Request $request)
    {

        $token = $request->id;


        $cek = tblOrders::where([
            'token'     =>  $token,
            'checkout'  =>  1,
            'status'    =>  1
            // 'payment'   =>  1
        ])
        ->first();

        $field = json_decode($cek->field, true);

        //
        if( $cek == null || $field['shiping']['courier_name'] == '')
        {
            return response()->json([
                'message'       =>  'Data tidak ditemukan'
            ], 404);
        }


        //update prints in orders shiping
        $upos = tblOrderShipings::where([
            'order_id'          =>  $cek->id,
            'status'            =>  1
        ])
        ->update([
            'print_status'          =>  1
        ]);

        $response = new \App\Http\Controllers\orders\manage;
        $response = $response->view(['order_id'=>$cek['id']]);

        
        //
        $data = [
            'message'       =>  '',
            'response'      =>  $response
        ];

        return response()->json($data, 200); 
    }



    // INVOICE
    public function invoice(Request $request)
    {

        $token = $request->id;


        $cek = tblOrders::where([
            'token'     =>  $token,
            'checkout'  =>  1,
            // 'payment'   =>  1,
            'status'    =>  1
        ])
        ->first();

        $field = json_decode($cek->field, true);

        //
        if( $cek == null || $field['shiping']['courier_name'] == '')
        {
            return response()->json([
                'message'       =>  'Data tidak ditemukan'
            ], 404);
        }

        $response = new \App\Http\Controllers\orders\manage;
        $response = $response->view(['order_id'=>$cek['id']]);

        //
        $data = [
            'message'       =>  '',
            'response'      =>  $response
        ];




        return response()->json($data, 200);
    }


}