<?php
namespace App\Http\Controllers\home\absen;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\Http\Controllers\account\index as Account;
use App\employe_attendances as tblEmployeAttendances;
use App\attendance_locations as tblAttendanceLocations;
use App\user_employes as tblUserEmployes;
use App\attendance_status as tblAttendanceStatus;
use DB;

class index extends Controller
{
    //
    public function getAbsen(Request $request)
    {
        $Config = new Config;
        $Account = new Account;

        $Account = $Account->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);


        //
        $thisday = date('Y-m-d', time());

        // CEK ATENDENCE
        $cek = tblEmployeAttendances::where([
            ['employe_id', '=', $Account['employe']['id']],
            ['date', 'like', '%' . $thisday . '%'],
            ['status', '=', 1]
        ])->first();

        $response = [
            'DayName'       =>  strtoupper($Config->namahari(date('Y-m-d H:i:s', time()))) .', '. date('d/m/Y', time()),
            'Time'          =>  date('H:i:s', time()),
            'TimeStr'       =>  strtotime(date('h:i:s', time())) . '000',
            'Date'          =>  time(),
            'xDate'         =>  date('d-m-Y', time()),
            'timeIn'        =>  $cek === null ? '' : date('H:i:s', strtotime($cek->checkin)),
            'timeOut'       =>  $cek === null ? '' : ($cek->checkout === '' ? '' : date('H:i:s', strtotime($cek->checkout))),
            'cekin'         =>  $cek === null ? 'on' : 'off',
            'cekout'        =>  $cek === null ? 'off' : ($cek->checkout !== '' ? 'off' : 'on')
        ];

        $data = [
            'message'       =>  '',
            'response'      =>  $response,
            // 'account'       =>  $Account['employe']['id']
        ];

        return response()->json($data, 200);
    }


    public function getAttendanceLocation(Request $request)
    {

        $getdata = tblAttendanceLocations::from('attendance_locations as al')
        ->select(
            'al.id','al.name','al.token_static','al.kodepos','al.address',
            'uc.name as company_name',
            'aop.name as provinsi_name',
            'aoc.name as city_name',
            'aoc.type as city_type',
            'aok.name as kecamatan_name'
        )
        ->leftJoin('user_companies as uc', function($join)
        {
            $join->on('uc.id', '=', 'al.company_id');
        })
        ->leftJoin('app_origin_provinsis as aop', function($join)
        {
            $join->on('aop.id', '=', 'al.provinsi');
        })
        ->leftJoin('app_origin_cities as aoc', function($join)
        {
            $join->on('aoc.id', '=', 'al.city');
        })
        ->leftJoin('app_origin_kecamatans as aok', function($join)
        {
            $join->on('aok.id', '=', 'al.kecamatan');
        })
        ->where([
            'al.token'     =>  $request->q
        ])->first();

        $data = [
            'message'       =>  '',
            'response'      =>  $getdata
        ];

        return response()->json($data,200);
    }


    //SEND ATTAENDANCE
    public function sendAttendance(Request $request)
    {


        $cekLocation = $this->cekAttendanceLocation($request);

        if( $cekLocation['status'] != 200)
        {
            return response()->json([
                'message'       =>  $cekLocation['message']
            ],401);
        }
        

        return response()->json($cekLocation, 200);
    }

    public function cekAttendanceLocation($request)
    {
        //
        $token = explode("_", $request->location);
        $location = $token[0];
        $location_type = $token[1];
        $thisdays = date('Y-m-d', time());
        $employe_id = $request->employe_id;
        $type = trim($request->type);

        //CEK EMPLOYE TYPE ATTENDANCE
        // IF ATTENDANCE TYPE 0 NOT FOR STATIC
        $cekemploye = tblUserEmployes::from('user_employes as ue')
        ->select(
            'ue.attendance_type',
            'wh.in'
        )
        ->leftJoin('working_hours as wh', function($join)
        {
            $join->on('wh.id', '=', 'ue.working_hours')
            ->where([
                'wh.status' =>  1
            ]);
        })
        ->where([
            'ue.id'    =>  $employe_id
        ])->first();

        // REJECT ABSEN
        if( $location_type == '2' && $cekemploye->attendance_type == 0)
        {
            return $data = [
                'message'       =>  'Absen ditolak! Harap melakukan absen di layar Absensi',
                'status'        =>  401
            ];
        }


        //
        $getloc = tblAttendanceLocations::where([
            'status'        =>  1
        ]);
        if( $location_type == '2') //STATIC
        {
            
            $getloc = $getloc->where([
                'token_static'      =>  $location
            ]);
        }
        else
        {
            $getloc = $getloc->where([
                'token_dinamis'      =>  $location
            ]);
        }
        $getloc = $getloc->first();
        

        //REJECT ABSEN IF QRCode not valid
        if( $getloc == null)
        {
            return $data = [
                'message'       =>  'Token QRCode tidak valid',
                'status'        =>  401
            ];
        }


        //REJECT ABSEN IF NOTE SAME LOCATION IN AND OUT
        $rsp = $getloc;
        $thisTime = date('H:i:s', (int)$request->time);
        $time_diff = strtotime($thisTime . ($rsp->diff_minute .' minute'));
        $timeInOut = date('Y-m-d H:i:s', $time_diff);


        $ceckAtt = tblEmployeAttendances::where([
            ['employe_id', '=', $request->employe_id],
            ['date', 'like', '%' . $thisdays . '%'],
            ['status', '=', 1]
        ])->first();

        if( $ceckAtt == null && $type == '1' )
        {

            //clear late
            $xlate = (strtotime($timeInOut) - strtotime($cekemploye->in));
            
            //cekin or create
            $datacekin = [
                'employe_id'        =>  $employe_id,
                'time'              =>  $timeInOut,
                'type'              =>  1,
                'location_id'       =>  $rsp->id,
                'location_type'     =>  $location_type,
                'late'              =>  $xlate < 1 ? 0 : $xlate,
                'info'              =>  0,
                'note'              =>  '',
                'date'              =>  date('Y-m-d', time()),
                'updated'           =>  0
            ];

            $cekin = new \App\Http\Controllers\models\attendances;
            $cekin = $cekin->main($datacekin);
        }
        else
        {
            if( $type == '2')
            {
                
                if($rsp->id != $ceckAtt->location_checkin)
                {
                    return $data = [
                        'message'       =>  'Absen ditolak! Harap melakukan absen di lokasi yang sama dengan lokasi Absen Datang',
                        'status'        =>  401
                    ];
                }

                //
                $countAtt = strtotime($timeInOut) - strtotime($ceckAtt->checkin); 

                //checkout or update
                $checkout = tblEmployeAttendances::where([
                    ['employe_id', '=', $request->employe_id],
                    ['date', 'like', '%' . $thisdays . '%'],
                    ['status', '=', 1]
                ])
                ->update([
                    'checkout'              =>  $timeInOut,
                    'location_checkout'     =>  $rsp->id,
                    'time_count'            =>  $countAtt
                ]);
                
            }
        }

        //
        return $data = [
            'message'       =>  'Absen '. ($request->type === '1' ? 'Datang' : 'Pulang') .' Berhasil',
            'status'        =>  200,
            'response'      =>  [
                'name'             =>  strtoupper($rsp->name),
                'time'             =>  date('H:i:s', $time_diff)
            ]
        ];

    }

    public function getView(Request $request)
    {
        $Config = new Config;
        
        //
        $employe_id = trim($request->q);

        $getdata = tblEmployeAttendances::where([
            ['employe_id','=',$employe_id],
            ['status','=',1]
        ])
        ->whereBetween('date', [$request->start, $request->end]);

        if( $getdata->count() > 0 )
        {
            foreach($getdata->get() as $row)
            {
                if( $row->info != 0)
                {
                    $getinfo = tblAttendanceStatus::where([
                        'id'        =>  $row->info
                    ])->first();
                }

                if( $row->type == 0 )
                {
                    

                    $data[] = [
                        'title'     =>  $getinfo->name,
                        'date'      =>  date('Y-m-d', strtotime($row->date)),
                        'type'      =>  'rgb(247, 97, 97)'
                    ];
                }
                
                if( $row->type == 1)
                {
                    $data[] = [
                        'title'     =>  date('H:i:s', strtotime($row->checkin)),
                        'date'      =>  date('Y-m-d', strtotime($row->date)),
                        'type'      =>  'rgb(11, 170, 11)'
                    ];
    
                    if( $row->checkout != '' )
                    {

                        $data[] = [
                            'title'     =>  date('H:i:s', strtotime($row->checkout)),
                            'date'      =>  date('Y-m-d', strtotime($row->date)),
                            'type'      =>  'orange'
                        ];
                    }
                    else
                    {
                        if( $row->info != 0 )
                        {
                            $data[] = [
                                'title'     =>  $getinfo->name,
                                'date'      =>  date('Y-m-d', strtotime($row->date)),
                                'type'      =>  'rgb(247, 97, 97)'
                            ];
                        }
                    }
                }

            }

            return response()->json($data,200);
        }  
  
    }


    public function getScreen(Request $request)
    {
        $Config = new Config;

        $thisday = date('Y-m-d H:i:s', time());

        //
        $token = trim($request->token);

        $getdata = tblAttendanceLocations::from('attendance_locations as al')
        ->select(
            'al.id','al.name','al.token_dinamis','al.kodepos','al.address', 'al.reload_limit', 'al.reload_time', 'al.diff_minute',
            'uc.name as company_name',
            'aop.name as provinsi_name',
            'aoc.name as city_name',
            'aoc.type as city_type',
            'aok.name as kecamatan_name'
        )
        ->leftJoin('user_companies as uc', function($join)
        {
            $join->on('uc.id', '=', 'al.company_id');
        })
        ->leftJoin('app_origin_provinsis as aop', function($join)
        {
            $join->on('aop.id', '=', 'al.provinsi');
        })
        ->leftJoin('app_origin_cities as aoc', function($join)
        {
            $join->on('aoc.id', '=', 'al.city');
        })
        ->leftJoin('app_origin_kecamatans as aok', function($join)
        {
            $join->on('aok.id', '=', 'al.kecamatan');
        })
        ->where([
            'al.token'     =>  $token
        ])->first();

        $time = strtotime(date('H:i:s', time()));
        $timev = strtotime(date('h:i:s', time()));
        // strtotime($thisTime . ($rsp->diff_minute .' minute'));


        $thisTime = date('H:i:s', time());
        $time_diff = strtotime($thisTime . ($getdata->diff_minute .' minute'));
        $timeInOut = date('h:i:s A', $time_diff);

        $data = [
            'message'       =>  '',
            'response'      =>  [
                'name'          =>  strtoupper($getdata->name),
                'address'       =>  $getdata->address,
                'address2'      =>  'Kec. ' . $getdata->kecamatan_name . ' - ' . $getdata->city_type . ' ' . $getdata->city_name,
                'address3'      =>  $getdata->provinsi_name . ' - ' . $getdata->kodepos,
                'day'      =>  $Config->namahari(date('Y-m-d H:i:s', time())) .', ' .date('d/m/Y', time()),
                'timex'          =>  date('H:i:s', time()),
                'timev'         =>  strtotime($timeInOut) . '000', //strtotime(date('h:i:s A', time())) . '000',
                'time'          =>  strtotime($time . ($getdata->diff_minute . ' minute') ),
                'qrcode'        =>  $getdata->token_dinamis . '_1',
                'limit'         =>  $getdata->reload_limit,
                'count'         =>  ($getdata->reload_time * 60),
                'token'         =>  $token,
                'reloadpage'    =>  3
            ]
        ];

        return response()->json($data,200);
    }


    //INFO DATE
    public function infodate(Request $request)
    {
        $Config = new Config;

        //
        $id = trim($request->id);
        $date = trim($request->date);
        $thisday = strtotime(date('Y-m-d', time()));
        
        if( date('w', strtotime($date)) == '0' )
        {
            $data = [
                'message'       =>  'Tanggal yang Anda pilih adalah hari libur'
            ];
            return response()->json($data,401);
        }

        // if( strtotime($date) > $thisday)
        // {
        //     $data = [
        //         'message'       =>  'Tanggal yang Anda pilih belum masuk waktu kerja'
        //     ];
        //     return response()->json($data,401);
        // }

        //
        $cek = tblEmployeAttendances::from('employe_attendances as ea')
        ->select(
            'ea.id','ea.checkin','ea.checkout','ea.info','ea.note','ea.updated',
            DB::raw('IFNULL(af.name_file, "") as files')
        )
        ->leftJoin('attendance_files as af', function($join)
        {
            $join->on('af.attendance_id','=','ea.id')
            ->where([
                'af.status' =>  1
            ]);
        })
        ->where([
            ['ea.employe_id','=',$id],
            ['ea.date','like', '%' . $date .'%'],
            ['ea.status','=',1]
        ])->first();


        // if( $cek !== null)
        // {
        //     if($cek->checkin != '' && $cek->checkout != '' || $cek->updated != 0)
        //     {
        //         $data = [
        //             'message'       =>  'Tanggal absen sudah terupdate'
        //         ];
        //         return response()->json($data,401);
        //     }
        // }

        //
        $getStatus = tblAttendanceStatus::where([
            'status'        =>  1
        ]);
        if( strtotime($date) > $thisday)
        {
            $getStatus = $getStatus->where([
                ['id', '<', 4]
            ]);
        }
        $getStatus = $getStatus->get();

        //
        $data = [
            'message'       =>  '',
            'response'      =>  [
                'att_id'            =>  $cek === null ? 0 : $cek->id,
                'TimeIn'            =>  $cek === null ? 0 : ( $cek->checkin === '' ? 0 : date('H:i:s', strtotime($cek->checkin)) ),
                'TimeOut'           =>  $cek === null ? 0 : ( $cek->checkout === '' ? 0 : date('H:i:s', strtotime($cek->checkout))),
                'status'            =>  $cek === null ? 0 : $cek->info,
                'note'              =>  $cek === null ? '' : $cek->note,
                'days'              =>  $Config->namahari(date($date, time())) . ', ' . date('d/m/Y', strtotime($date)),
                'disabled'             =>  $cek === null ? 'false' : ( $cek->checkout !== '' ? 'true' : ($cek->updated === 0 ? 'false' : 'true')),
                'selectStatus'            =>  $getStatus,
                'file'              =>  $cek === null ? '' : $cek->files
            ]
        ];


        return response()->json($data,200);
    }


    //SET INFO
    public function setInfo(Request $request)
    {
        $Config = new Config;
 
        $id = trim($request->att_id);
        $status = trim($request->status);
        $file = $request->file('file');
        $note = trim($request->detail);
        $date = trim($request->date);
        $user_id = trim($request->user_id);
        $employe_id = trim($request->employe_id);


        if( $id === '0')
        {

            $datacekin = [
                'employe_id'        =>  $employe_id,
                'time'              =>  '',
                'type'              =>  0,
                'location_id'       =>  0,
                'location_type'     =>  0,
                'late'              =>  0,
                'info'              =>  $status,
                'note'              =>  $note,
                'date'              =>  $date,
                'updated'           =>  1
            ];

            $addAtt = new \App\Http\Controllers\models\attendances;
            $addAtt = $addAtt->main($datacekin);


        }
        else
        {
            $upAtt = tblEmployeAttendances::where([
                'id'        =>  $id
            ])
            ->update([
                'info'      =>  $status,
                'note'      =>  $note,
                'updated'   =>  1
            ]);
    
        }

        if( $file != '')
        {
            $dataupload = [
                'att_id'     =>  $id !== '0' ? $id : $addAtt,
                'employe_id' =>  $employe_id,
                'user_id'   =>  $user_id,
                'file'      =>  $file
            ];

            $upload = new \App\Http\Controllers\models\attendances;
            $upload = $upload->files($dataupload);
        }

        //update
        $data = [
            'message'       =>  'Absen berhasil di update'
        ];

        return response()->json($data,200);
    }


    //GET DINAMIC
    public function getdinamic(Request $request)
    {
        $Config = new Config;

        //
        $token = trim($request->token);

        $cek = tblAttendanceLocations::where([
            'token'     =>  $token
        ])->count();

        if( $cek == 0)
        {
            $data = [
                'message'       =>  'Terjadi kesalahan tidak diketahui, halaman akan di reload'
            ];

            return response()->json($data, 401);
        }

        //update
        $time = strtotime(date('Y-m-d H:i:s', time()));
        $dinamic = $Config->randString(50,$time);

        $update = tblAttendanceLocations::where([
            'token'     =>  $token
        ])
        ->update([
            'token_dinamis'     =>  $dinamic
        ]);

        $data = [
            'message'       =>  '',
            'qrcode'        =>  $dinamic . '_1'
        ];

        return response()->json($data,200);

    }


    //VIEW SINGLE
    public function viewSingle(Request $request)
    {

        //
        $Config = new Config;

        //
        $date = trim($request->date);
        $datex = explode("-", $date);
        $id = trim($request->id);

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
            'ue.id'     =>  $id
        ])
        ->first();


        $where = [
            ['employe_id', '=', $getdata->id],
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


        //record
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
            ['ea.employe_id', '=', $id],
            ['ea.date',  'like', '%' . $date . '%'],
            ['ea.status', '=', 1]
        ]);

        $countatt = $attendance->count();
        $dtattendance = $attendance->get();


        $d = cal_days_in_month(CAL_GREGORIAN, (int)$datex[1],$datex[0]);
        $t = 0;
        for($i=1;$i<($d+1);$i++)
        {
            $t = $date . '-' . sprintf('%02s', $i);
            $days = $Config->namahari($t);

            if( $countatt > 0 )
            {
                foreach($dtattendance as $row)
                {
                    if( $row->date == $t)
                    {
                        $in = $row->checkin;
                        $out = $row->checkout;
                        $total_time = $row->time_count;
                        $late = $row->late;
                        $status = $row->label_status;
                        break;
                    }
                    else
                    {
                        $in = '';
                        $out = '';
                        $total_time = '';
                        $late = 0;
                        $status = "";
                    }
                }
    
    
                $txx = strtotime($getdata->out); 
                $txt = $out === "" ? "" : strtotime(date("H:i:s", strtotime($out)));
                $cl = $txt === "" ? 0 : ($txt - $txx);
                $clate = ($late - $cl);
    
                $list[] = [
                    'day'       =>  $days,
                    'date'      =>  sprintf('%02s', $i) . '/' . $datex[1] . '/' . $datex[0],
                    'in'        =>  $in === "" ? "" : date("H:i:s", strtotime($in)),
                    'out'       =>  $out === "" ? "" : date("H:i:s", strtotime($out)),
                    'total'     =>  $out === "" ? "" : gmdate("H:i", $total_time),
                    'late'      =>  $out === "" ? "" : ( $late < 60 ? "" : ($clate < 1 ? "" : gmdate("H:i", $clate))),
                    'status'    =>  $status
                ];

            }
            else
            {
                $list[] = [
                    'day'       =>  $days,
                    'date'      =>  sprintf('%02s', $i) . '/' . $datex[1] . '/' . $datex[0],
                    'in'        =>  "",
                    'out'       =>  "",
                    'total'     =>  "",
                    'late'      =>  "",
                    'status'    =>  ""
                ];
            }

            //
        }

        $d = $this->toDateInterval($totaltime)->format('%a');
        $h = gmdate('H', $totaltime);
        $h = (int)$h === 0 ? 0 : $h;
        $m = gmdate('i', $totaltime);

        $d = $d === 0 ? 0 : (($d * 8) * 3);


        //
        $response = [
            'employe'       =>  [
                'id'            =>  $getdata->id,
                'name'          =>  $getdata->name,
                'group'         =>  $getdata->group_name,
                'gender'        =>  $getdata->gender
            ],
            'month'         =>  $Config->bulanFull($datex[1]) . ' ' . $datex[0],
            'attendance'        =>  [
                'result'            =>  [
                    'sakit'         =>  $totalsick,
                    'ijin'          =>  $totalijin,
                    'cuti'          =>  $totalcuti,
                    'lad'           =>  $totallad,
                    'lap'           =>  $totallap,
                    'la'            =>  $totalla,
                    'total_time'    =>  ($d + $h) .'j ' . (int)$m.'m'
                ],
                'list'     =>  $list
            ]
        ];

        $data = [
            'message'       =>  '',
            'response'      =>  $response
        ];

        return response()->json($data,200);
    }



    public function toDateInterval($seconds) {
        return date_create('@' . (($now = time()) + $seconds))->diff(date_create('@' . $now));
    }

}