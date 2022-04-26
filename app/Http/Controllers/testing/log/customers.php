<?php
namespace App\Http\Controllers\testing\log;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class customers extends Controller
{
    //
    public function add(Request $request)
    {
        $datalog = [
            'customer_id'      =>  $request->customer,
            'user_id'           =>  $request->user
        ];

        $addLogs = new \App\Http\Controllers\log\customers\manage;
        $addLogs = $addLogs->Add($datalog);

        return response()->json($addLogs,200);
    }
}