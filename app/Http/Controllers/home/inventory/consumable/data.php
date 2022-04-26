<?php
namespace App\Http\Controllers\home\inventory\consumable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\consumable_types as tblConsumableTypes;

class data extends Controller
{
    //
    public function types(Request $request)
    {
        $getdata = tblConsumableTypes::where([
            'status'        =>  1
        ])
        ->get();

        $data = [
            'message'       =>  '',
            'response'      =>  $getdata
        ];

        return response()->json($data,200);
    }
}