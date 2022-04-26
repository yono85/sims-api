<?php
namespace App\Http\Controllers\home\task;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\tasks as tblTasks;
use DB;

class table extends Controller
{
    //
    public function main(Request $request)
    {
        $Config = new Config;

        //
        $search = trim($request->search);
        $paging = trim($request->paging);
        $status = trim($request->status_selected);
        $employe = trim($request->employe_selected);
        $sort = trim($request->sort_name);
        $date = '%' . $request->date . '%';

        //
        $getdata = tblTasks::from('tasks as t')
        ->select(
            't.id', 't.label', 't.date', 't.text', 't.start_date', 't.end_date', 't.progress', 't.verify_status', 't.verify_date', 't.progress_done', 't.user_id','t.verify_id',
            'ue.name as user_name',
            'eg.name as group_name',
            'uex.name as verify_name'
        )
        ->leftJoin('user_employes as ue', function($join)
        {
            $join->on('ue.user_id', '=', 't.user_id');
        })
        ->leftJoin('employe_groups as eg', function($join)
        {
            $join->on('eg.id', '=', 'ue.groups');
        })
        ->leftJoin('user_employes as uex', function($join)
        {
            $join->on('uex.user_id', '=', 't.verify_id');
        })
        ->where([
            ['t.label', 'like', '%' . $search . '%'],
            ['t.date', 'like', $date],
            ['t.status', '=', 1]
        ]);
        if( $employe != "-1")
        {
            $getdata = $getdata->where([
                't.user_id'     =>  $employe
            ]);
        }
        if( $status != "-1")
        {
            $getdata = $getdata->where([
                't.progress'    =>  $status
            ]);
        }

        //count
        $count = $getdata->count();
        if( $count == 0 )
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }

        $caldata = $getdata->get();
        $sum_hold = [];
        $sum_progress = [];
        $sum_done = [];
        foreach($caldata as $row)
        {
            if($row->progress == 3)
            {
                $sum_done[] = $row->progress;
            }

            if($row->progress == 2)
            {
                $sum_hold[] = $row->progress;
            }

            if($row->progress == 1)
            {
                $sum_progress[] = $row->progress;
            }

        }

        //
        $gettable = $getdata->orderBy('t.id', $sort)
        ->take($Config->table(['paging'=>$paging])['paging_item'])
        ->skip($Config->table(['paging'=>$paging])['paging_limit'])
        ->get();

        //
        foreach($gettable as $row)
        {
            $list[] = [
                'id'        =>  $row->id,
                'label'     =>  $row->label,
                'name'      =>  $row->user_name,
                'group'     =>  $row->group_name,
                'start_date'    =>  date('d/m/Y', strtotime($row->start_date)),
                'end_date'    =>  date('d/m/Y', strtotime($row->end_date)),
                'done_date'     =>  $row->progress_done === "" ? "--" : $Config->timeago($row->progress_done),
                'date'      =>  $Config->timeago($row->date),
                'status'    =>  $row->progress,
                'detail'   =>  $row->text,
                'verif_date'    =>  $row->verify_date === "" ? "--" : $Config->timeago($row->verify_date),
                'verif_name'    =>  $row->verify_name === null ? "--" : $row->verify_name,
                'verif_status'  =>  (int)$row->verify_status,
                'verif_type'    =>  $row->user_id === $row->verify_id ? "false" : "true",
                'user_id'       =>  $row->user_id
            ];
        }

        $data = [
            'message'       =>  '',
            'response'      =>  [
                'list'          =>  $list,
                'result'        =>  [
                    'hold'          =>  count($sum_hold),
                    'progress'      =>  count($sum_progress),
                    'done'          =>  count($sum_done)
                ],
                'paging'        =>  $paging,
                'total'         =>  $count,
                'countpage'     =>  ceil($count / $Config->table(['paging'=>$paging])['paging_item'] )
            ]
        ];

        return response()->json($data, 200);
            
    }

    public function verif(Request $request)
    {
        $Config = new Config;

        $token = trim($request->token);

        //
        $getdata = tblTasks::from('tasks as t')
        ->select(
            't.id', 't.label', 't.date', 't.text', 't.start_date', 't.end_date', 't.progress', 't.verify_status', 't.verify_date', 't.progress_done', 't.user_id','t.verify_id',
            'ue.name as user_name',
            'eg.name as group_name',
            'uex.name as verify_name'
        )
        ->leftJoin('user_employes as ue', function($join)
        {
            $join->on('ue.user_id', '=', 't.user_id');
        })
        ->leftJoin('employe_groups as eg', function($join)
        {
            $join->on('eg.id', '=', 'ue.groups');
        })
        ->leftJoin('user_employes as uex', function($join)
        {
            $join->on('uex.user_id', '=', 't.verify_id');
        })
        ->where([
            ['t.token', '=', $token]
        ])->first();
        

        if( $getdata === null)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data,404);
        }


        //
        $list = [
            'id'        =>  $getdata->id,
            'label'     =>  $getdata->label,
            'name'      =>  $getdata->user_name,
            'group'     =>  $getdata->group_name,
            'start_date'    =>  date('d/m/Y', strtotime($getdata->start_date)),
            'end_date'    =>  date('d/m/Y', strtotime($getdata->end_date)),
            'done_date'     =>  $getdata->progress_done === "" ? "--" : $Config->timeago($getdata->progress_done),
            'date'      =>  $Config->timeago($getdata->date),
            'status'    =>  $getdata->progress,
            'detail'   =>  $getdata->text,
            'verif_date'    =>  $getdata->verify_date === "" ? "--" : $Config->timeago($getdata->verify_date),
            'verif_name'    =>  $getdata->verify_name === null ? "--" : $getdata->verify_name,
            'verif_status'  =>  (int)$getdata->verify_status,
            'verif_type'    =>  $getdata->user_id === $getdata->verify_id ? "false" : "true",
            'user_id'       =>  $getdata->user_id,
            'verif_id'         =>  $getdata->verify_id        
        ];
        
        $data = [
            'message'       =>  '',
            'response'      =>  [
                'list'          =>  $list,
                'paging'        =>  1,
                'total'         =>  1,
                'countpage'     =>  1
            ]
        ];

        return response()->json($data, 200);
    }
}