<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\tasks as tblTasks;

class task extends Controller
{

    public function main($request)
    {
        $Config = new Config;
    
        //
        $newid = $Config->createnewidnew([
            'value'         =>  tblTasks::count(),
            'length'        =>  15
        ]);

        //
        $newadd =   new tblTasks;
        $newadd->id                 =   $newid;
        $newadd->token              =   md5($newid);
        $newadd->progress           =   trim($request->status_selected);
        $newadd->progress_done      =   "";
        $newadd->label              =   trim($request->label);
        $newadd->text               =   strip_tags(trim($request->editor));
        $newadd->text_code          =   trim($request->editor);
        $newadd->start_date         =   trim($request->dateStart);
        $newadd->end_date           =   trim($request->dateClose);
        $newadd->verify_id          =   trim($request->verify_selected);
        $newadd->verify_status      =   0;
        $newadd->verify_date        =   "";
        $newadd->user_id            =   trim($request->user_id);
        $newadd->date               =   date('Y-m-d H:i:s', time());
        $newadd->status             =   1;
        $newadd->save();

        return $data = [
            'id'        =>  $newid
        ];

    }
}