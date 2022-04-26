<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\assets as tblAssets;
use App\asset_types as tblAssetTypes;
use App\Http\Controllers\config\index as Config;

class assets extends Controller
{
    //
    public function main($request)
    {
        $Config = new Config;

        //
        $newid = $Config->createnewidnew([
            'value'         =>  tblAssets::count(),
            'length'        =>  9
        ]);

        $token = md5($newid);

        $addnew                     =   new tblAssets;
        $addnew->id                 =   $newid;
        $addnew->token              =   $token;
        $addnew->code               =   trim($request->code);
        $addnew->name               =   trim($request->name);
        $addnew->type               =   trim($request->type_tools);
        $addnew->assesoris          =   trim($request->assesoris) === '' ? '' : trim($request->assesoris);
        $addnew->quantity           =   trim($request->quantity);
        $addnew->kalibrasi_status   =   trim($request->kalibrasi_status);
        $addnew->kalibrasi_date     =   trim($request->kalibrasi_status) === '0' ? '' : $Config->changeFormatDate(trim($request->kalibrasi_date));
        $addnew->description        =   trim($request->description);
        $addnew->reminder_duration  =   trim($request->durasi_reminder);
        $addnew->project_id         =   0;
        $addnew->user_id            =   trim($request->user_id);
        $addnew->status             =   1;
        $addnew->save();

        $data = [
            'id'        =>  $newid,
            'token'     =>  $token
        ];

        return $data;
    }


    public function update($request)
    {
        $Config = new Config;

        $update = tblAssets::where([
            'id'            =>  trim($request->asset_id)
        ])
        ->update([
            'code'                  =>  trim($request->code),
            'name'                  =>  trim($request->name),
            'type'                  =>  trim($request->type_tools),
            'assesoris'             =>  trim($request->assesoris) === '' ? '' : trim($request->assesoris),
            'quantity'              =>  trim($request->quantity),
            'kalibrasi_status'      =>  trim($request->kalibrasi_status),
            'kalibrasi_date'        =>  trim($request->kalibrasi_status) === '0' ? '' : $Config->changeFormatDate(trim($request->kalibrasi_date)),
            'description'           =>  trim($request->description),
            'reminder_duration'     =>  trim($request->durasi_reminder)
        ]);
    }

    //
    public function types($request)
    {
        $Config = new Config;

        $newid = tblAssetTypes::count();
        $newid = ($newid + 1);

        //
        $addnew                 =   new tblAssetTypes;
        $addnew->id             =   $newid;
        $addnew->name           =   trim($request->label);
        $addnew->user_id        =   trim($request->user_id);
        $addnew->status         =   1;
        $addnew->save();
    }
}