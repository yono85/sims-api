<?php
namespace App\Http\Controllers\export\home;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use DB;

class task extends Controller
{
    //
    public function excel(Request $request)
    {
        //
        $Config = new Config;

        //
        $employe = trim($request->employe);
        $status = trim($request->status);
        $date = trim($request->date);

        $getdata = DB::table('vw_tasks as t')
        ->select(
            't.id', 't.label', 't.text', 't.start_date', 't.end_date', 't.date', 't.progress', 't.progress_done', 't.verify_date',
            'ue.name as user_name',
            'uex.name as verif_name',
            'eg.name as group_name',
            'es.alias as jabatan'
        )
        ->leftJoin('user_employes as ue', function($join)
        {
            $join->on('ue.user_id', '=', 't.user_id');
        })
        ->leftJoin('user_employes as uex', function($join)
        {
            $join->on('uex.user_id', '=', 't.verify_id');
        })
        ->leftJoin('employe_sublevels as es', function($join)
        {
            $join->on('es.id', '=', 'ue.sublevel');
        })
        ->leftJoin('employe_groups as eg', function($join)
        {
            $join->on('eg.id', '=', 'ue.groups');
        })
        ->where([
            ['t.date', 'like', '%' . $date . '%'],
            ['t.status', '=', 1]
        ]);
        if( $employe != "-1" )
        {
            $getdata = $getdata->where([
                't.user_id'     =>  $employe
            ]);
        }
        if($status != "-1")
        {
            $getdata = $getdata->where([
                't.progress'        =>  $status
            ]);
        }


        $count = $getdata->count();

        //
        if( $count == 0 )
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }

        //
        $vdata = $getdata->get();
        foreach($vdata as $row)
        {
            $list[] = [
                'id'        =>  $row->id,
                'label'     =>  $row->label,
                'text'      =>  $row->text,
                'start'     =>  $row->start_date,
                'end'       =>  $row->end_date,
                'name'      =>  $row->user_name,
                'level'     =>  $row->jabatan === null ? "" : $row->jabatan,
                'group'     =>  $row->group_name,
                'verif_name'    =>  $row->verif_name,
                'verif_date'    =>  $row->verify_date === "" ? "" : date('d/m/Y', strtotime($row->verify_date)),
                'done_date'    =>  $row->progress_done === "" ? "" : date('d/m/Y', strtotime($row->progress_done))
            ];
        }

        $month = explode("-", $date);

        $dt = date('Y-m-d', strtotime(date('Y-m-d H:i', time()) . '1 year'));

        $data = [
            'message'       =>  '',
            'exp'           =>  $dt,
            'response'      =>  [
                'list'          =>  $list,
                'title'         =>  'Export_task_' . $Config->bulanFull($month[1]) . '-' . $month[0]. '_'. time() .'.xlsx'
            ]
        ];

        return response()->json($data,200);


    }
}