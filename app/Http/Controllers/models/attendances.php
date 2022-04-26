<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\employe_attendances as tblEmployeAttendances;
use App\attendance_files as tblAttendanceFiles;

class attendances extends Controller
{
    //
    public function main($request)
    {
        $Config = new Config;

        $newid = $Config->createnewidnew([
            'value'     =>  tblEmployeAttendances::count(),
            'length'    =>  15
        ]);

        //
        $addnew                     =   new tblEmployeAttendances;
        $addnew->id                 =   $newid;
        $addnew->type               =   $request['type'];
        $addnew->employe_id         =   $request['employe_id'];
        $addnew->location_type      =   $request['location_type'];
        $addnew->checkin            =   $request['time'];
        $addnew->checkout           =   '';
        $addnew->location_checkin   =   $request['location_id'];
        $addnew->location_checkout  =   0;
        $addnew->time_count         =   0;
        $addnew->late               =   $request['late'];
        $addnew->field              =   '';
        $addnew->note               =   $request['note'];
        $addnew->info               =   $request['info'];
        $addnew->status             =   1;
        $addnew->updated            =   $request['updated'];
        $addnew->date               =   $request['date'];
        $addnew->save();

        return $newid;
    }

    public function files($request)
    {
        $Config = new Config;

        $newid = $Config->createnewidnew([
            'value'     =>  tblAttendanceFiles::count(),
            'length'    =>  15
        ]);

        $token = md5($newid);
        $name = $token . '.jpg';

        //
        $newadd             = new tblAttendanceFiles;
        $newadd->id         =   $newid;
        $newadd->token      =   $token;
        $newadd->url        =   $Config->apps()['URL']['STORAGE'] . '/images/attendance/' . $name;
        $newadd->name_file  =   $request['file']->getClientOriginalName();
        $newadd->attendance_id  =   $request['att_id'];
        $newadd->employe_id     =   $request['employe_id'];
        $newadd->user_id        =   $request['user_id'];
        $newadd->status         =   1;
        $newadd->save();

        $dataupload = [
            'name'          =>  $token,
            'file'          =>  $request['file'],
            'path'          =>  'images/attendance/',
            "URL"           =>  $Config->apps()["URL"]["STORAGE"] . "/s3/upload/transfer"
        ];

        $upload = new \App\Http\Controllers\tdparty\s3\herbindo;
        $upload = $upload->transfer($dataupload);

        return $newid;
    }
}