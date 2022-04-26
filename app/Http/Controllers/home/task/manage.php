<?php
namespace App\Http\Controllers\home\task;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\tasks as tblTasks;
use App\user_employes as tblUserEmployes;

class manage extends Controller
{
    //
    public function create(Request $request)
    {
        $Config = new Config;

        if($request->type == "new")
        {

            $addnew = new \App\Http\Controllers\models\task;
            $addnew = $addnew->main($request);
    
            //notify
            $datanotif = [
                'id'        =>  $addnew['id'],
                'to_id'     =>  $request->verify_selected,
                'from_id'   =>  $request->user_id,
                'selected'  =>  $request->status_selected
            ];

            $this->notifyTask($datanotif);

            $data = [
                'message'       =>  'Task baru berhasil dibuat'
            ];
    
            return response()->json($data, 200);
        }

        //
        // view
        $updata = tblTasks::where([
            "id"        =>  trim($request->task_id)
        ])
        ->update([
            "progress"      =>  trim($request->status_selected),
            "progress_done" =>  trim($request->status_selected) !== "3" ? "" : date("Y-m-d H:i:s", time()),
            "label"         =>  trim($request->label),
            "text"          =>  strip_tags(trim($request->editor)),
            "text_code"     =>  trim($request->editor),
            "start_date"    =>  trim($request->dateStart),
            "end_date"      =>  trim($request->dateClose),
            "verify_id"     =>  trim($request->verify_selected)
        ]);

        //notify
        $datanotif = [
            'id'        =>  $request->task_id,
            'to_id'     =>  $request->verify_selected,
            'from_id'   =>  $request->user_id,
            'selected'  =>  $request->status_selected
        ];
        $this->notifyTask($datanotif);

        $data = [
            "message"       =>  trim($request->status_selected) !== "3" ? "Task berhasil di perbaharui" : "Task anda sudah selesai"
        ];

        return response()->json($data, 200);
    }

    public function notifyTask($request)
    {
        if($request['selected'] == "3" && $request['to_id'] != $request['from_id'])
        {
            
            $gettask = tblTasks::from('tasks as t')
            ->select(
                't.token', 't.label',
                'ue.name'
            )
            ->leftJoin('user_employes as ue', function($join)
            {
                $join->on('ue.user_id', '=', 't.user_id');
            })
            ->where([
                't.id'        =>  $request['id']
            ])
            ->first();

            $datanotif = [
                "type"          =>  1,
                "to_id"         =>  trim($request['to_id']),
                "from_id"       =>  trim($request['from_id']),
                "groups"        =>  0,
                "label"         =>  "Task Done",
                "content"       =>  "<b>" . $gettask->name . "</b> <span> Menyelesaikan task:</span> " . $gettask->label,
                "link"          =>  "/home/task?token=" . $gettask->token
            ];

            $insertnotif = new \App\Http\Controllers\models\notification\home;
            $insertnotif = $insertnotif->main($datanotif);
        }
    }

    //
    public function view(Request $request)
    {
        $Config = new Config;

        //
        $id = trim($request->id);

        //
        $getdata = tblTasks::from("tasks as t")
        ->select(
            "t.id", "t.label", "t.start_date", "t.end_date", "t.verify_id", "t.verify_status", "t.progress", "t.text"
        )
        ->where([
            't.id'        =>  $id
        ])
        ->first();
        
        //
        $vdata = [
            "id"            =>  $getdata->id,
            "label"         =>  $getdata->label,
            "start_date"    =>  $getdata->start_date,
            "end_date"      =>  $getdata->end_date,
            "verify_id"     =>  $getdata->verify_id,
            "verify_status" =>  $getdata->verify_status,
            "progress"      =>  $getdata->progress,
            "detail"        =>  $getdata->text
        ];


        //
        $data = [
            "message"       =>  "",
            "response"      =>  $vdata
        ];

        return response()->json($data,200);
    }


    public function verify(Request $request)
    {
        $Config = new Config;

        //
        $uptask = tblTasks::where([
            'id'        =>  trim($request->task_id)
        ])
        ->update([
            'verify_status' =>  1,
            'verify_date'   =>  date('Y-m-d H:i:s', time())
        ]);


        //DATA TASKS
        $gettask = tblTasks::from("tasks as t")
        ->select(
            "t.id", "t.user_id", "t.label", "t.token",
            "ue.name"
        )
        ->leftJoin("user_employes as ue", function($join)
        {
            $join->on("ue.user_id", "=", "t.verify_id");
        })
        ->where([
            't.id'        =>  trim($request->task_id)
        ])
        ->first();

        //notification
        $datanotif = [
            "type"          =>  2,
            "to_id"         =>  trim($gettask->user_id),
            "from_id"       =>  trim($request->user_id),
            "groups"        =>  0,
            "label"         =>  "Verification Task",
            "content"       =>  "<b>" . $gettask->name . "</b> <span> Memverifikasi task:</span> " . $gettask->label,
            "link"          =>  "/home/task?token=" . $gettask->token
        ];

        $insertnotif = new \App\Http\Controllers\models\notification\home;
        $insertnotif = $insertnotif->main($datanotif);

        //
        $data = [
            "message"       =>  "Task berhasil diverifikasi"
        ];

        return response()->json($data, 200);
    }
}