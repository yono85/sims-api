<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\pengumumans as tblPengumumans;

class pengumuman extends Controller
{
    //
    public function new(Request $request)
    {
        $Config = new Config;

        $newid = $Config->createnewidnew([
            'value'         =>  tblPengumumans::count(),
            'length'        =>  9
        ]);

        $token = md5($newid);

        $addnew             =   new tblPengumumans;
        $addnew->id         =   $newid;
        $addnew->token      =   $token;
        $addnew->name       =   trim($request->name);
        $addnew->text       =   strip_tags(trim($request->editor));
        $addnew->text_code  =   trim($request->editor);
        $addnew->publish    =   trim($request->publish);
        $addnew->user_id    =   trim($request->user_id);
        $addnew->date       =   $Config->changeFormatDate(trim($request->pengumuman_date));
        $addnew->status     =   1;
        $addnew->save();
    }

    //UPDATE
    public function update($request)
    {
        
    }
}