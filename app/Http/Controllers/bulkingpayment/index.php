<?php
namespace App\Http\Controllers\bulkingpayment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\order_bulkings as tblOrderBulkings;

class index extends Controller
{
    //
    public function check(Request $request)
    {
        $bulking_id = trim($request->id);


        //checking
        $cek = tblOrderBulkings::where([
            'id'            =>  $bulking_id
        ])
        ->first();

        if( $cek->status == 0 )
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }
        

        $view = new \App\Http\Controllers\bulkingpayment\manage;
        $view = $view->view(['bulking_id'=>$bulking_id]);

        if( $cek->paid == 0 )
        {
            
            $data = [
                'status'        =>  0,
                'response'      =>  [
                    'id'            =>  $cek->id,
                    'total'         =>  $cek->total_paid,
                    'qty'           =>  $cek->quantity,
                    'data'          =>  $view,
                    'list'          =>  $view['detail']
                ]
            ];

            return response()->json($data, 200);
        }


        $data = [
            'response'      =>  [
                'status'        =>  1,
                'response'      =>  $view
            ]
        ];


        return response()->json($data, 200);

    }
}