<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\ordernew as tblOrderNew;
use App\Http\Controllers\config\index as Config;

class ordernew extends Controller
{
    //
    public function main($request)
    {
        $Config = new Config;

        //
        $newid = $Config->createnewidnew([
            'value'         =>  tblOrderNew::count(),
            'length'        =>  14
        ]);

        $token = md5($newid);

        $countmonth = tblOrderNew::where([
            ['created_at', 'like', '%' . date('Y-m', time()) . '%']
        ])->count();

        // code
        $code = 'INV/' . date("d/m/Y", time()) . '/' . $Config->numberFZero([$countmonth,3]);
        $search = $code . ';' . $request['contentpo']['name'];

        //
        $addnew                 =   new tblOrderNew;
        $addnew->id             =   $newid;
        $addnew->token          =   $token;
        $addnew->code           =   $code;
        $addnew->poid           =   $request['poid'];
        $addnew->search         =   $search;
        $addnew->content_po     =   json_encode($request['contentpo']);
        $addnew->progress       =   0;
        $addnew->status         =   1;
        $addnew->save();

        return $addnew;
    }
}