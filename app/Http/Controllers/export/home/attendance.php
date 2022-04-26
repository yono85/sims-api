<?php
namespace App\Http\Controllers\export\home;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use DB;

class attendance extends Controller
{
    //
    public function main(Request $request)
    {
        $Config = new Config;

        //request
        $date = trim($request->date);

        //
        $getdata = DB::table('vw_user_employes as ue')
        ->select(
            'ue.id','ue.name','ue.gender','ue.status',
            'wh.out',
            DB::raw('IFNULL(eg.name, "null") as group_name')
        )
        ->leftJoin('employe_groups as eg', function($join)
        {
            $join->on('eg.id', '=', 'ue.groups');
        })
        ->leftJoin('working_hours as wh', function($join)
        {
            $join->on('wh.id', '=', 'ue.working_hours');
        })
        ->where([
            ['ue.status','=', 1]
        ]);

        //
        $count = $getdata->orderBy('name','asc')->count();
        if( $count == 0)
        {
            $data = [
                'message'       =>  'Data Tidak ditemukan',
                'response'      =>  ''
            ];

            return response()->json($data, 404);
        }

        //
        
        //
        $gettable = $getdata->get();
        foreach($gettable as $row)
        {

            //
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


            //
            $attendance = DB::table('vw_employe_attendances as ea')
            ->select(
                'ea.id','ea.checkin','ea.checkout','ea.date','ea.time_count','ea.late', 'ea.info',
                DB::raw('IFNULL(as.name, "") as label_status')
            )
            ->leftJoin('attendance_statuses as as', function($join)
            {
                $join->on('as.id', '=', 'ea.info');
            })
            ->where([
                ['ea.employe_id', '=', $row->id],
                ['ea.date',  'like', '%' . $date . '%'],
                ['ea.status', '=', 1]
            ])
            ->orderBy('ea.date', 'asc')
            ->get();

            
            // if(count($attendance) == 0)
            // {
            //     $listAtt = '';
            // }
            // else
            // {
                $listAtt = [];
                foreach($attendance  as $rowx)
                {

                    if( $rowx->info == 0 )
                    {
                        $hd = 1; //absen
                    }
                    else
                    {
                        if( $rowx->info == 4 || $rowx->info == 5 )
                        {
                            $hd = 1;
                        }
                        else
                        {
                            $hd = 0;
                        }
                    }


                    //late
                    // $late 
                    $txx = strtotime($row->out);
                    $txt = $rowx->checkout === "" ? "" : strtotime(date("H:i:s", strtotime($rowx->checkout)));
                    $cl = $txt === "" ? 0 : ($txt - $txx);
                    $late = ($rowx->late - $cl);

                    // list
                    $listAtt[] = [
                        'id'        =>  $rowx->id,
                        'date'      =>  date('d/m/Y', strtotime($rowx->date)),
                        'in'        =>  $hd === 0 ? "" : date('H:i:s', strtotime($rowx->checkin)),
                        'out'       =>  $hd === 0 ? "" : ( $rowx->checkout === "" ? "" : date('H:i:s', strtotime($rowx->checkout))),
                        'total'     =>  $hd === 0 ? "" : ( $rowx->checkout === "" ? "" : gmdate('H:i', $rowx->time_count)),
                        'late'      =>  $hd === 0 ? "" : ( $rowx->late < 60 ? "" : ($late < 1 ? "" : gmdate("H:i", $late))),
                        'label'     =>  $rowx->label_status,
                        // 'jam kerja' =>  $txx,
                        // 'chckout'   =>  $txt,
                        // 'calc'      =>  ($rowx->late - $cl)
                    ];

                }
            // }


            //
            $month = explode("-", $date);

            $d = $this->toDateInterval($totaltime)->format('%a');
            $h = gmdate('H', $totaltime);
            $h = (int)$h === 0 ? 0 : $h;
            $m = gmdate('i', $totaltime);

            $d = $d === 0 ? 0 : (($d * 8) * 3);

            $list[] = [
                'id'                =>  $row->id,
                'name'              =>  $row->name,
                'gender'            =>  $row->gender,
                'groups'             =>  $row->group_name,
                'month'             =>  $Config->bulanFull($month[1]) . ' ' . $month[0],
                'status'       =>  [
                    'sakit'         =>  $totalsick,
                    'ijin'          =>  $totalijin,
                    'cuti'          =>  $totalcuti,
                    'lad'           =>  $totallad,
                    'lap'           =>  $totallap,
                    'la'            =>  $totalla,
                ],
                'total_hour'        =>  ($d + $h),
                'total_minute'      =>  (int)$m,
                'list'              =>  count($attendance) === 0 ? '' : $listAtt
            ];
        }

        //
        $data = [
            'message'       =>  '',
            'response'      =>  [
                'list'          =>  $list,
                'total'         =>  $count,
                'title'             =>  'report_absen_' .$Config->bulanFull($month[1]) . '-' . $month[0]. '_'. time() .'.xlsx'
            ]
        ];

        return response()->json($data,200);
    }

    public function toDateInterval($seconds) {
        return date_create('@' . (($now = time()) + $seconds))->diff(date_create('@' . $now));
    }
    
}