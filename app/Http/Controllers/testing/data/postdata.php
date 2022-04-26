<?php
namespace App\Http\Controllers\testing\data;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;


class postdata extends Controller
{
    //
    public function main()
    {

        //update field cusomters
        $data = DB::table('customers')->get();
        foreach($data as $row)
        {
            DB::table('customers')
            ->where(['id'=>$row->id])
            ->update(['field'=>$row->name .',' . $row->phone ]);
        }

    }
}