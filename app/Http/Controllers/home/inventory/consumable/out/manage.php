<?php
namespace App\Http\Controllers\home\inventory\consumable\out;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\consumables as tblConsumables;
use DB;

class manage extends Controller
{
    //
    public function checkpo(Request $request)
    {
        $getdata = DB::table('vw_po_orders')
        ->where([
            'code'      =>  trim($request->q)
        ]);

        $count = $getdata->count();

        if( $count == 0)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }

        $gettable = $getdata->first();

        $data = [
            'message'       =>  '',
            'response'      =>  $gettable
        ];

        return response()->json($data,200);
    }


    //ADD
    public function add(Request $request)
    {
        //
        $check = DB::table('vw_consum_outs')
        ->where([
            'poid'          =>  trim($request->poid),
            'consumable_id' =>  trim($request->consumable_id),
            'status'        =>  1
        ]);

        $count = $check->count();
        

        if( $count > 0 )
        {
            $vdata = $check->first();


            $data = [
                'id'        =>  trim($request->consumable_id),
                'quantity'  =>  trim($request->quantity),
                'idout'     =>  $vdata->id,
                'user_id'   =>  trim($request->user_id),
                'type'      =>  'update'
            ];

            //update quantity
            $update = new \App\Http\Controllers\models\consumable;
            $update = $update->checkstock($data);

            return $update;
        }


        $data = [
            'id'        =>  trim($request->consumable_id),
            'quantity'  =>  trim($request->quantity),
            'poid'      =>  trim($request->poid),
            'user_id'   =>  trim($request->user_id),
            'type'      =>  'add'
        ];

        //add new
        $addnew = new \App\Http\Controllers\models\consumable;
        $addnew = $addnew->checkstock($data);

        return $addnew;
    }


    //DELETE
    public function delete(Request $request)
    {
        $id = trim($request->id);

        //
        $getdata = DB::table("vw_consum_outs")
        ->where([
            'id'        =>  $id
        ])->first();

        //
        $consum = tblConsumables::where([
            'id'        =>  $getdata->consumable_id
        ])->first();
        
        $countqty = ($getdata->quantity + $consum->quantity);

        //update
        $updateouts = DB::table("vw_consum_outs")
        ->where([
            'id'        =>  $id
        ])
        ->update([
            'status'        =>  0
        ]);

        //
        $updateconsum = tblConsumables::where([
            'id'            =>  $getdata->consumable_id
        ])
        ->update([
            'quantity'      =>  $countqty
        ]);



        //
        $data = [
            'message'       =>  'Data berhasil dihapus',
            'response'      =>  [
                'id'            =>  $id
            ],
            'data'          =>  $getdata
        ];

        return response()->json($data,200);
    }
}