<?php
namespace App\Http\Controllers\home\absen;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\user_employes as tblUserEmployes;
use App\Http\Controllers\config\index as Config;
use DB;


class report extends Controller
{
    //
    public function main(Request $request)
    {
        $Config = new Config;

        //request
        $src = '%' . trim($request->search) . '%';
        $date = trim($request->date);
        $paging = trim($request->paging);
        $sortname = trim($request->sort_name);
        
        //
        $getdata = DB::table('vw_user_employes as ue')
        ->select(
            'ue.id','ue.name','ue.gender','ue.status',
            DB::raw('IFNULL(eg.name, "null") as group_name')
        )
        ->leftJoin('employe_groups as eg', function($join)
        {
            $join->on('eg.id', '=', 'ue.groups');
        })
        ->where([
            ['ue.name','like', $src],
            ['ue.status', '=', 1]
        ]);

        //
        $count = $getdata->count();
        if( $count == 0)
        {
            $data = [
                'message'       =>  'Data Tidak ditemukan',
                'response'      =>  ''
            ];

            return response()->json($data, 404);
        }

        //
        $gettable = $getdata;

        $gettable = $gettable->orderBy('ue.name', $sortname)
        ->take($Config->table(['paging'=>$paging])['paging_item'])
        ->skip($Config->table(['paging'=>$paging])['paging_limit'])
        ->get();

        foreach($gettable as $row)
        {
            
            $where = [
                ['employe_id', '=', $row->id],
                ['date', 'like', '%' . $date . '%']
            ];

            //total time
            $totaltime = DB::table('count_time_attendances')
            ->where($where)->sum('total');

            //sick
            $totalsick = DB::table('count_sick_attendances')
            ->where($where)->count('*');

            //ijin
            $totalijin = DB::table('count_ijin_attendances')
            ->where($where)->count('*');

            //cuti
            $totalcuti = DB::table('count_cuti_attendances')
            ->where($where)->count('*');

            //lad
            $totallad = DB::table('count_lad_attendances')
            ->where($where)->count('*');

            //lap
            $totallap = DB::table('count_lap_attendances')
            ->where($where)->count('*');

            //la
            $totalla = DB::table('count_la_attendances')
            ->where($where)->count('*');

            //total time convert
            $d = $this->toDateInterval($totaltime)->format('%a');
            $h = gmdate('H', $totaltime);
            $h = (int)$h === 0 ? 0 : $h;
            $m = gmdate('i', $totaltime);
            $d = $d === 0 ? 0 : (($d * 8) * 3);

            // $tx = $this->toDateInterval($row->total_time)->format('%ah %hj %im');
            $list[] = [
                'id'                =>  $row->id,
                'name'              =>  $row->name,
                'gender'            =>  $row->gender,
                'group'             =>  $row->group_name,
                'total_time'        =>  $totaltime,
                'total_timeJ'        =>  ($d + $h),//gmdate('H', $row->total_time),
                'total_timeM'        =>  (int)$m,//gmdate('i', $row->total_time),
                'status_list'       =>  [
                    'sakit'         =>  $totalsick,
                    'ijin'          =>  $totalijin,
                    'cuti'          =>  $totalcuti,
                    'lad'           =>  $totallad,
                    'lap'           =>  $totallap,
                    'la'            =>  $totalla
                ]
            ];
        }

        //
        $data = [
            'message'       =>  '',
            'response'      =>  [
                'list'          =>  $list,
                'paging'        =>  $paging,
                'total'         =>  $count,
                'countpage'     =>  ceil($count / $Config->table(['paging'=>$paging])['paging_item'] )
            ]
        ];

        return response()->json($data,200);
    }

    public function toDateInterval($seconds) {
        return date_create('@' . (($now = time()) + $seconds))->diff(date_create('@' . $now));
    }

}