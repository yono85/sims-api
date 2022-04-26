<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\hibahs as tblHibahs;

class hibah extends Controller
{
    //
    public function new($request)
    {
        $Config = new Config;

        $newid = $Config->createnewidnew([
            'value'         =>  tblHibahs::count(),
            'length'        =>  9
        ]);

        $token = md5($newid);
        $start = $Config->changeFormatDate(trim($request->startdate));
        $end = $Config->changeFormatDate(trim($request->enddate));

        $addnew             =   new tblHibahs;
        $addnew->id         =   $newid;
        $addnew->token      =   $token;
        $addnew->type       =   trim($request->lembaga_type);
        $addnew->name       =   trim($request->name);
        $addnew->text       =   strip_tags(trim($request->editor));
        $addnew->text_code  =   trim($request->editor);
        $addnew->dekrit     =   '';
        $addnew->start_date =   $start;
        $addnew->start_time =   strtotime($start);
        $addnew->end_date   =   $end;
        $addnew->end_time   =   strtotime($end);
        $addnew->publish    =   trim($request->publish);
        $addnew->user_id    =   trim($request->user_id);
        $addnew->status     =   1;
        $addnew->save();
    }

    //UPDATE
    public function update($request)
    {

    }
}