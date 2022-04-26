<?php
namespace App\Http\Controllers\testing\home;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\employe_attendances as tblEmployeAttendances;
use DB;

class index extends Controller
{
    //
    public function AttCount()
    {
        $cek = tblEmployeAttendances::where([
            'date'      =>  '2021-11-04'
        ])->first();

        $count = (strtotime($cek->checkout) - strtotime($cek->checkin));
        $data = [
            'data1'      =>  $cek->checkin,
            'data2'     =>  $cek->checkout,
            'cekin'     =>  strtotime($cek->checkin),
            'cekout'     =>  strtotime($cek->checkout),
            'count'     =>  $count,
            'ch'        =>  gmdate('H:i:s', $cek->time_count)
        ];

        return response()->json($data,200);
    }


    public function viewTime()
    {
        $time = date('Y-m-d H:i:s', '1636303530');

        return response()->json([
            'test'      =>  $time
        ],200);
    }


    public function countLate()
    {
        $time = date('2021-11-11 12:26:18');
        $cek = date('08:30:00');

        $calc = strtotime($time) - strtotime($cek);

        $data = [
            'datang'    =>  $time,
            'cek'       =>  $cek,
            'calc'      =>  $calc,
            'ch'        =>  gmdate('H:i:s', $calc),
            'calcx'     =>  date('H:i:s', (strtotime($cek) + $calc))
        ];

        return response()->json($data, 200);
    }

    //CEK VIEW QUERY
    public function cekcount(Request $request)
    {
        $getdata = DB::table('count_time_attendances')
        ->where([
            ['employe_id', '=', $request->id],
            ['date', 'like', '%' . $request->date . '%']
        ])->sum('total');


        try {
            
            $data = [
                'message'       =>  '',
                'sum'         =>  $getdata
            ];
    
            return response()->json($data, 200);

          }
          
          //catch exception
          catch(Exception $e) {
            //   return 
            // echo 'Message: ' .$e->getMessage();

            return response()->json([
                'message'       =>  $e->getMessage()
            ], 200);
          }
        
    }
}