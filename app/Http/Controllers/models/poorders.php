<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\po_orders as tblPoOrders;
use App\po_order_employes as tblPoOrderEmployes;
use App\po_order_tools as tblPoOrderTools;
use App\order_sdms as tblOrderSdms;
use App\order_tools as tblOrderTools;
use App\Http\Controllers\config\index as Config;

class poorders extends Controller
{
    //
    public function main($request)
    {
        $Config = new Config;

        //
        $newid = $Config->createnewidnew([
            'value'         =>  tblPoOrders::count(),
            'length'        =>  14
        ]);

        $countmonth = tblPoOrders::where([
            ['created_at', 'like', '%' . date('Y-m', time()) . '%']
        ])->count();

        $token = md5($newid);

        //
        $addnew                     =   new tblPoOrders;
        $addnew->id                 =   $newid;
        $addnew->token              =   $token;
        $addnew->code               =   'PO/' . date("d/m/Y", time()) . '/' . $Config->numberFZero([$countmonth,3]);
        $addnew->name               =   trim($request->name);
        $addnew->customer_id        =   trim($request->customer_id);
        $addnew->price              =   str_replace(".", "", trim($request->price));
        $addnew->address            =   trim($request->address);
        $addnew->startdate          =   $Config->changeFormatDate(trim($request->startdate));
        $addnew->enddate            =   $Config->changeFormatDate(trim($request->enddate));
        $addnew->sdm                =   trim($request->sdm);
        $addnew->sdm_status         =   0;
        $addnew->tools              =   trim($request->tools);
        $addnew->tools_status       =   0;
        $addnew->progress           =   1;
        $addnew->user_id            =   trim($request->user_id);
        $addnew->status             =   1;
        $addnew->save();

        $data = [
            'token'     =>  $token,
            'id'        =>  $newid
        ];
        return $data;

    }


    public function update($request)
    {
        $update = tblPoOrders::where([
            'id'            =>  trim($request->project_id)
        ])
        ->update([
            'name'              =>  trim($request->name),
            'customer_id'       =>  trim($request->customer_id),
            'price'             =>  str_replace(".", "", trim($request->price)),
            'address'           =>  trim($request->address),
            'startdate'         =>  trim($request->startdate),
            'enddate'           =>  trim($request->enddate),
            'sdm'               =>  trim($request->sdm),
            'tools'             =>  trim($request->tools)
        ]);
    }

    //PO ORDER SDM
    public function poordersdm($request)
    {
        $Config = new Config;

        //
        $newid = $Config->createnewidnew([
            'value'         =>  tblPoOrderEmployes::count(),
            'length'        =>  14
        ]);

        $addnew                 =   new tblPoOrderEmployes;
        $addnew->id             =   $newid;
        $addnew->po_id          =   $request['poid'];
        $addnew->employe_id     =   $request['employe_id'];
        $addnew->user_id        =   $request['user_id'];
        $addnew->status         =   1;
        $addnew->save();
    }


    //ORDER SDM
    public function ordersdm($request)
    {
        $Config = new Config;

        //
        $newid = $Config->createnewidnew([
            'value'         =>  tblOrderSdms::count(),
            'length'        =>  14
        ]);

        $addnew                 =   new tblOrderSdms;
        $addnew->id             =   $newid;
        $addnew->po_id          =   $request->project_id;
        $addnew->user_id        =   $request->user_id;
        $addnew->status         =   1;
        $addnew->save();
    }


    //PO ORDER TOOLS
    public function poordertools($request)
    {
        $Config = new Config;

        //
        $newid = $Config->createnewidnew([
            'value'         =>  tblPoOrderTools::count(),
            'length'        =>  14
        ]);

        $addnew                 =   new tblPoOrderTools;
        $addnew->id             =   $newid;
        $addnew->po_id          =   $request['poid'];
        $addnew->tools_id       =   $request['tools_id'];
        $addnew->user_id        =   $request['user_id'];
        $addnew->status         =   1;
        $addnew->save();
    }

    // ORDER TOOLS
    public function ordertools($request)
    {
        $Config = new Config;

        //
        $newid = $Config->createnewidnew([
            'value'         =>  tblOrderTools::count(),
            'length'        =>  14
        ]);

        $addnew                 =   new tblOrderTools;
        $addnew->id             =   $newid;
        $addnew->po_id          =   $request->project_id;
        $addnew->user_id        =   $request->user_id;
        $addnew->status         =   1;
        $addnew->save();
    }
}