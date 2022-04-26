<?php
namespace App\Http\Controllers\log\customers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\customer_logs as tblCustomerLogs;

class index extends Controller
{
    //
    public function main($request)
    {
        $Config = new Config;

        //
        $newid = $Config->createnewidnew([
            'value'     =>  tblCustomerLogs::count(),
            'length'    =>  15
        ]);

        $new                =   new tblCustomerLogs;
        $new->id            =   $newid;
        $new->type          =   $request['type'];
        $new->sub_type      =   $request['sub_type'];
        $new->text          =   json_encode($request['text']);
        $new->customer_id   =   $request['customers']['id'];
        $new->user_id       =   $request['users']['id'];
        $new->company_id    =   $request['users']['company_id'];
        $new->status        =   1;
        $new->save();

    }
}