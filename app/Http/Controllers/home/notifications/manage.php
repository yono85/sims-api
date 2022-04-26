<?php
namespace App\Http\Controllers\home\notifications;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class manage extends Controller
{
    //
    public function read(Request $request)
    {
        $readnotif = DB::table("vw_home_notif")
        ->where([
            "id"        =>  trim($request->id),
            "read_status"   =>  0
        ])
        ->update([
            "read_status"       =>  1,
            "read_date"         =>  date("Y-m-d H:i:s", time())
        ]);

        $data = [
            "message"       =>  ""
        ];

        return response()->json($data,200);
    }
}