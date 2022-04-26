<?php
namespace App\Http\Controllers\verifbulking;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\order_bulkings as tblOrderBulkings;
use App\orders as tblOrders;
use App\Http\Controllers\access\manage as Refresh;

class manage extends Controller
{
    //
    public function check(Request $request)
    {
        $bulking_id = trim($request->id);

        $cek = tblOrderBulkings::where([
            'id'        =>  $bulking_id
        ])->first();


        if( $cek->status == 0 )
        {

            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }

        
        $view = $this->view(['bulking_id'=>$bulking_id]);

        return response()->json([
            'message'       =>  '',
            'response'      =>  $view
        ], 200);
        
    }


    public function view($request)
    {
        $bulking_id = $request['bulking_id'];

        $getdata = tblOrderBulkings::from('order_bulkings as ob')
        ->select(
            'ob.id', 'ob.invoice', 'ob.company_user', 'ob.paid', 'ob.created_at',
            'ob.field', 'ob.total_paid as total', 'ob.quantity'
        )
        ->where([
            'ob.id'            =>  $bulking_id
        ])->first();



        $field = json_decode($getdata->field);

        $data = [
            'id'            =>  $getdata->id,
            'invoice'       =>  $getdata->invoice,
            'total'         =>  $getdata->total,
            'paid'          =>  $getdata->paid,
            'quantity'      =>  $getdata->quantity,
            'field'         =>  $field,
            'detail'        =>  json_decode($field->list),
            'date'          =>  date('d/m/Y H:i', strtotime($getdata->created_at)),
            'orders'        =>  $field->orders,
            'customers'     =>  $field->customers,
            'upload'        =>  $field->upload
        ];

        return $data;
        
        
    }


    // VERIFICATION
    public function verification(Request $request)
    {

        //
        $Config = new Config;

        //
        // $Refresh = new Refresh;
        // $Refresh = $Refresh->refresh();


        // $account = $Refresh['refresh']['account'];

        $account = new \App\Http\Controllers\account\index;
        $account = $account->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);
        
        //
        $bulking_id = trim($request->bulking_id);


        $cek = tblOrderBulkings::where([
            'id'        =>  $bulking_id
        ])
        ->first();


        // null
        if( $cek->status == 0 || $cek->paid == 2)
        {

            $data = [
                'message'       =>  'Opss.. Data tidak ditemukan',
                // 'refresh'       =>  $Refresh
            ];


            return response()->json($data, 404);
        }



        //verification bulking paid
        $up = tblOrderBulkings::where([
            'id'        =>  $bulking_id
        ])
        ->update([
            'paid'      =>  2,
            'paid_date'     =>  date('Y-m-d H:i:s', time()),
            'paid_user_id'  =>  $account['id']
        ]);

        //
        $uporder = tblOrders::whereIn('id', json_decode($cek->order_id))
        ->update([
            'bulking_paid'      =>  1
        ]);

        //response
        $data = [
            'message'       =>  '',
            // 'refresh'       =>  $Refresh
        ];


        return response()->json($data, 200);
    }

}