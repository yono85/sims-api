<?php
namespace App\Http\Controllers\tools\prints;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\orders as tblOrders;
use App\Http\Controllers\config\index as Config;

class shiping extends Controller
{
    //
    public function token(Request $request)
    {

        $token = $request->id;


        $cek = tblOrders::where([
            'token'     =>  $token
        ])
        ->first();

        if( $cek == null)
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