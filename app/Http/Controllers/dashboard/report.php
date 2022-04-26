<?php
namespace App\Http\Controllers\dashboard;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use DB;


class report extends Controller
{
    //
    public function main(Request $request)
    {

        if( $request->type == 'seling')
        {
            // $data = $this->PenjualanTotal($request);
            return $this->SelingTotal($request);
        }

        if( $request->type == 'customers')
        {
            return $this->CustomerTotal($request);
        }

        if( $request->type == 'users')
        {
            return $this->UsersTotal($request);
        }

        if( $request->type == 'orders')
        {
            return $this->orders($request);
        }

        if( $request->type == 'charts')
        {
            if( $request->date == '')
            {
                return $this->ChartDays($request);
            }
            else
            {
                $vdate = explode("_", $request->date);
                if( $vdate[0] == $vdate[1])
                {
                    return $this->ChartDays($request);
                }
                else
                {
                    return $this->cekDate($request);
                }
            }
        }
        
        if( $request->type == 'rankseling')
        {
            return $this->rankSeling($request);
        }

        if( $request->type == 'rankcustomers')
        {
            return $this->rankCustomers($request);
        }

        if( $request->type == 'rankekspedisi')
        {
            return $this->rankEkspedisi($request);
        }

        if( $request->type == 'rankdestination')
        {
            return $this->rankDestination($request);
        }

        // not type
        return response()->json([
            'message'   =>  'Error type'
        ], 500);
    }


    public function SelingTotal($request)
    {
        $date = $this->changeDate($request->date);

        $getdata = DB::table('vw_po_orders as vpo')
        ->where([
            'vpo.status' => 1
        ])
        ->whereBetween('vpo.date', [$date['startDate'], $date['endDate']]);

        $total = $getdata->count();
        
        $gettable = $getdata->get();
        
        $progress = 0; $pending = 0; $success = 0;
        foreach($gettable as $row)
        {
            if( $row->progress == '2')
            {
                $progress++;
            }
            if( $row->progress == '1')
            {
                $pending++;
            }

            if( $row->progress == '3')
            {
                $success++;
            }
        }

        $data = [
            'total'        =>  $total,
            'pending'      =>  $pending,
            'progress'     =>  $progress,
            'success'       =>  $success
        ];

        return response()->json($data, 200);
    }

    public function CustomerTotal($request)
    {

        $date = $this->changeDate($request->date);

        $getdata = DB::table('vw_customers as vc')
        ->where([
            'vc.status'    =>  1
        ])
        ->whereBetween('vc.date', [$date['startDate'], $date['endDate']]);
        

        $total = $getdata->count();
        
        $gettable = $getdata->get();
        
        $thisday = date('Y-m-d', time());
        $new = 0;
        foreach($gettable as $row)
        {
            if( $row->date == $thisday )
            {
                $new++;
            }
        }

        $data = [
            'total'         =>  $total,
            'new'           =>  $new
        ];

        return response()->json($data, 200);
    }

    public function UsersTotal($request)
    {

        $date = $this->changeDate($request->date);

        $getdata = DB::table('vw_users as vu')
        ->where([
            'vu.status'    =>  1
        ])
        ->whereBetween('vu.date', [$date['startDate'], $date['endDate']]);
        

        $total = $getdata->count();
        
        $getverify = $getdata->where([
            'vu.registers'      =>  0
        ])
        ->count();


        $data = [
            'total'        =>  $total,
            'verify'          =>  $getverify
        ];

        return response()->json($data, 200);
    }

    public function orders($request)
    {

        $date = $this->changeDate($request->date);

        $getdata = DB::table('vw_ordernews as vo')
        ->where([
            'vo.status'    =>  1
        ])
        ->whereBetween('vo.date', [$date['startDate'], $date['endDate']]);
        

        $total = $getdata->count();
        
        $gettable = $getdata->get();
        
        $progress = 0; $success = 0;
        foreach($gettable as $row)
        {
            if( $row->progress == '0')
            {
                $progress++;
            }

            if( $row->progress == '1')
            {
                $success++;
            }
        }


        $data = [
            'total'        =>  $total,
            'progress'     =>  $progress,
            'success'       =>  $success
        ];


        return response()->json($data, 200);
    }

    public function ChartDays($request)
    {

        $date = $this->changeDate($request->date);
        $cek = DB::table('vw_rp_ocharts');
        if( $request->compid <> '-1')
        {
            $cek = $cek->where([
                'company_id'        =>  trim($request->compid)
            ]);
        }
        $cek = $cek->whereBetween('date', [$date['startDate'], $date['endDate']])
        ->count();


        $qr = DB::table('vw_rp_ocharts')
        ->select(DB::raw('HOUR(date) as HOUR'), DB::raw('sum(bayar) as DAY') );
        if( $request->compid <> '-1')
        {
            $qr = $qr->where([
                'company_id'        =>  trim($request->compid)
            ]);
        }
        $qr = $qr->whereBetween('date', [$date['startDate'], $date['endDate']])
        ->groupBy('HOUR')
        ->get();

        $sumQr = 0;
        foreach($qr as $row)
        {
            $listQr[] = [
                "HOUR" => $row->HOUR,
                "TOTAL" => $row->DAY
            ];
        
            $sumQr+= $row->DAY;
        }

        for($i=1;$i<25;$i++)
        {


            //array hour
            if( strlen($i) < 2)
            {
                $ix = "".sprintf("%01s", $i) . "";
            }
            else
            {
                $ix = $i . "";
            }


            //
            if( $cek > 0 )
            {
                foreach($listQr as $arr)
                {

                    $h_arr = $arr["HOUR"];
                    if(sprintf('%02d', $h_arr) == sprintf('%02d', $i))
                    {
                        $tlQr = $arr["TOTAL"]; break;
                    //  $rw = $rw[0]; break;
                    }
                    else
                    {
                        $tlQr = 0;
                    }

                }

                if( $listQr == null)
                {
                    // $total[] = 0;
                    $totalQr = 0;
                }
                else
                {
                    // $total[] = $rw;
                    $totalQr = $tlQr;
                }
            }
            else
            {
                $totalQr = 0;
            }


            $datex[] = 'j'.$ix;
            $totalx[] = $totalQr;
            //data
            $list[] = [
                'Date'  =>  $ix,
                'Total' =>  $totalQr
            ];
            
            

        }

        $data = [
            // 'list'      =>  $list,
            'Date'      =>  $datex,
            'Total'     =>  $totalx
        ];
        return response()->json($data, 200);
    }

    public function cekDate($request)
    {
        $date = explode("_", $request->date);
        $start = date_create( $date[0] );
        $end = date_create(date('Y-m-d', strtotime($date[1] . '+1 day') ) );

        $diff = date_diff($start, $end);

        if( $diff->days > 31)
        {
            // return response()->json($diff->format("%m"), 200);
            return $this->ChartMonth($request);
        }
        else
        {
            return $this->ChartDate($request);
        }

    }

    public function ChartDate($request)
    {

        $ndate = $this->changeDate($request->date);

        $date = explode("_", $request->date);
        $start = date_create( $date[0] );
        $end = date_create(date('Y-m-d', strtotime($date[1] . '+1 day') ) );

        $diff = date_diff($start, $end);
        $diff = ( $diff->format("%a"));
   
        //cek
        $cek = DB::table('vw_rp_ocharts');
        // ->where([
        //     'company_id'        =>  trim($request->compid)
        // ]);
        if( trim($request->compid) != '-1')
        {
            $cek = $cek->where([
                'company_id'       =>  trim($request->compid)
            ]);
        }
        $cek = $cek->whereBetween('date', [$start, $end])
        ->count();

        //query omzet
        $qr = DB::table('vw_rp_ocharts')
        ->select(DB::raw('DAY(date) as DAY'), DB::raw('sum(bayar) as TOTAL') );
        // ->where([
        //     'company_id'        =>  trim($request->compid)
        // ]);
        if( trim($request->compid) != '-1')
        {
            $qr = $qr->where([
                'company_id'       =>  trim($request->compid)
            ]);
        }
        $qr = $qr->whereBetween('date', [$start, $end])
        ->groupBy('DAY')
        ->get();

        $sumQr = 0;
        foreach($qr as $row)
        {
            $listQr[] = [
                "DAY" => $row->DAY,
                "TOTAL" => $row->TOTAL
            ];
        
            $sumQr+= $row->TOTAL;
        }


        for($i=0;$i<$diff;$i++)
        {

            //array days
            if( strlen($i) < 2)
            {
                $ix = "".sprintf("%01s", $i) . "";
            }
            else
            {
                $ix = $i . "";
            }

            $tanggal = date('d', strtotime($date[0] . '+'.$i.' day' ) );
            $tgl2 = date('d/m', strtotime($date[0] . '+'.$i.' day' ) );
            //
            if( $cek > 0 )
            {
                foreach($listQr as $arr)
                {

                    $h_arr = $arr["DAY"];
                    if(sprintf('%02d', $h_arr) == $tanggal )
                    {
                        $tlQr = $arr["TOTAL"]; break;
                    }
                    else
                    {
                        $tlQr = 0;
                    }

                }

                if( $listQr == null)
                {
                    $totalQr = 0;
                }
                else
                {
                    $totalQr = $tlQr;
                }
            }
            else
            {
                $totalQr = 0;
            }

            $datex[] = $tgl2;
            $totalx[] = $totalQr;

            //data
            // $list[] = [
            //     'Date'  =>  $tgl2,
            //     'Total' =>  $totalQr
            // ];
        }

        $data = [
            // 'list'  =>  $list,
            'Date'  =>  $datex,
            'Total' =>  $totalx
        ];

        return response()->json($data, 200);
    }

    public function ChartMonth($request)
    {

        $Config = new Config;
        $ndate = $this->changeDate($request->date);

        $date = explode("_", $request->date);
        $start = date_create( $date[0] );
        $end = date_create(date('Y-m-d', strtotime($date[1] . '+1 month') ) );

        $diff = date_diff($start, $end);
        $diff = ( $diff->format("%m"));
   
        //cek
        $cek = DB::table('vw_rp_ocharts');
        // ->where([
        //     'company_id'        =>  trim($request->compid)
        // ]);
        if( trim($request->compid) != '-1')
        {
            $cek = $cek->where([
                'company_id'       =>  trim($request->compid)
            ]);
        }
        $cek = $cek->whereBetween('date', [$start, $end])
        ->count();

        //query omzet
        $qr = DB::table('vw_rp_ocharts')
        ->select(DB::raw('MONTH(date) as MONTH'), DB::raw('sum(bayar) as TOTAL') );
        // ->where([
        //     'company_id'        =>  trim($request->compid)
        // ]);
        if( trim($request->compid) != '-1')
        {
            $qr = $qr->where([
                'company_id'       =>  trim($request->compid)
            ]);
        }
        $qr = $qr->whereBetween('date', [$start, $end])
        ->groupBy('MONTH')
        ->get();

        $sumQr = 0;
        foreach($qr as $row)
        {
            $listQr[] = [
                "MONTH" => $row->MONTH,
                "TOTAL" => $row->TOTAL
            ];
        
            $sumQr+= $row->TOTAL;
        }


        for($i=0;$i<$diff;$i++)
        {

            //array days
            if( strlen($i) < 2)
            {
                $ix = "".sprintf("%01s", $i) . "";
            }
            else
            {
                $ix = $i . "";
            }

            $tanggal = date('m', strtotime($date[0] . '+'.$i.' month' ) );
            $year = date('y', strtotime($date[0] . '+'.$i.' month' ) );
            $bln = $Config->bulanArr($tanggal) . '/' . $year;
            //
            if( $cek > 0 )
            {
                foreach($listQr as $arr)
                {

                    $h_arr = $arr["MONTH"];
                    if(sprintf('%02d', $h_arr) == $tanggal )
                    {
                        $tlQr = $arr["TOTAL"]; break;
                    }
                    else
                    {
                        $tlQr = 0;
                    }

                }

                if( $listQr == null)
                {
                    $totalQr = 0;
                }
                else
                {
                    $totalQr = $tlQr;
                }
            }
            else
            {
                $totalQr = 0;
            }

            $datex[] = $bln;
            $totalx[] = $totalQr;
            
        }

        $data = [
            'Date'  =>  $datex,
            'Total' =>  $totalx
        ];

        return response()->json($data, 200);
    }

    public function rankSeling($request)
    {

        $Config = new Config;
        //
        $date = $this->changeDate($request->date);
        $start = $date['startDate'];
        $end = $date['endDate'];
        $paging = trim($request->pg);

        //
        $getdata = DB::table('vw_users as vu')
        ->select(
            'vu.id',
            'vu.name',
            DB::raw('IFNULL( SUM(vrs.total), 0) as total')
        )
        ->leftJoin('vw_rank_seling as vrs', function($join) use ($start, $end)
        {
            $join->on('vrs.user_id', '=', 'vu.id')
            ->whereBetween('vrs.date', [$start,$end]);
        })
        ->where([
            'vu.company_id'     =>  trim($request->compid),
            'vu.registers'      =>  1
        ])
        ->groupBy('vu.id')
        ->orderBy('total', 'desc');

        $count = count($getdata->get());
        if( $count > 0 )
        {
            $total = 0;
            foreach($getdata->get() as $row)
            {
                $total += $row->total;
            }

            $getdata = $getdata->take($Config->table(['paging'=>$paging])['paging_item'])
            ->skip($Config->table(['paging'=>$paging])['paging_limit'])
            ->get();

            foreach($getdata as $row)
            {
                $list[] = [
                    'id'        =>  $row->id,
                    'name'      =>  $row->name,
                    'total'     =>  $row->total
                ];

            }


            $data = [
                'list'      =>  $list,
                'total'     =>  $total,
                'paging'    =>  $paging,
                'countpage' =>  ceil($count / $Config->table(['paging'=>$paging])['paging_item'] )
            ];
            return response()->json($data, 200);
        }
        else
        {
            $data = [
                'message'      =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }
    }

    public function rankCustomers($request)
    {

        $Config = new Config;

        $date = $this->changeDate($request->date);
        $start = $date['startDate'];
        $end = $date['endDate'];
        $paging = trim($request->pg);

        //
        $getdata = DB::table('vw_customers as vc')
        ->select(
            'vc.id',
            'vc.name',
            DB::raw('IFNULL( SUM(vrs.total), 0) as total')
        )
        ->leftJoin('vw_rank_seling as vrs', function($join) use ($start, $end)
        {
            $join->on('vrs.customer_id', '=', 'vc.id')
            ->whereBetween('vrs.date', [$start,$end]);
        });
        if( $request->id <> "-1")
        {
            $getdata = $getdata->where([
                'vc.user_id'    =>  trim($request->id)
            ]);
        }
        $getdata = $getdata->where([
            'vc.company_id'     =>  trim($request->compid)
        ])
        ->groupBy('vc.id')
        ->orderBy('total', 'desc');

        $count = count($getdata->get());

        if( $count > 0 )
        {

            $total = 0;
            foreach($getdata->get() as $row)
            {
                $total += $row->total;
            }

            $getdata = $getdata->take($Config->table(['paging'=>$paging])['paging_item'])
            ->skip($Config->table(['paging'=>$paging])['paging_limit'])
            ->get();

            foreach($getdata as $row)
            {
                $list[] = [
                    'id'        =>  $row->id,
                    'name'      =>  $row->name,
                    'total'     =>  $row->total
                ];
            }

            $data = [
                'list'      =>  $list,
                'total'     =>  $total,
                'paging'    =>  $paging,
                'countpage' =>  ceil($count / $Config->table(['paging'=>$paging])['paging_item'] )
            ];
            return response()->json($data, 200);
        }
        else
        {
            $data = [
                'message'      =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }
    }

    public function rankEkspedisi($request)
    {

        $Config = new Config;

        $date = $this->changeDate($request->date);
        $start = $date['startDate'];
        $end = $date['endDate'];
        $paging = trim($request->pg);
        $companyid = trim($request->compid);
        $uid = trim($request->id);

        //
        $getdata = DB::table('vw_courier as vc')
        ->select(
            'vc.id',
            'vc.name',
            // DB::raw('IFNULL( SUM(vre.total), 0) as total'),
            DB::raw('IFNULL( COUNT(vre.courier_id), 0) as total')
        )
        ->leftJoin('vw_rank_ekspedisi as vre', function($join) use ($start, $end, $companyid,$uid)
        {
            $join->on('vre.courier_id', '=', 'vc.id');
            if( $uid <> "-1")
            {
                $join = $join->where([
                    'vre.user_id'    =>  $uid
                ]);
            }
            $join = $join->where(['vre.company_id'=>$companyid])
            ->whereBetween('vre.date', [$start,$end]);
        })
        ->groupBy('vc.id')
        ->orderBy('total', 'desc');
        // ->get();

        $count = count($getdata->get());

        if( $count > 0 )
        {

            $total = 0;
            foreach($getdata->get() as $row)
            {
                $total += $row->total;
            }

            $getdata = $getdata->take($Config->table(['paging'=>$paging])['paging_item'])
            ->skip($Config->table(['paging'=>$paging])['paging_limit'])
            ->get();

            foreach($getdata as $row)
            {
                $list[] = [
                    'id'        =>  $row->id,
                    'name'      =>  $row->name,
                    'total'     =>  $row->total
                ];
            }

            $data = [
                'list'      =>  $list,
                'total'     =>  $total,
                'paging'    =>  $paging,
                'countpage' =>  ceil($count / $Config->table(['paging'=>$paging])['paging_item'] )
            ];
            return response()->json($data, 200);
        }
        else
        {
            $data = [
                'message'      =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }

        // return response()->json(['count'=>$count],200);
    }

    public function rankDestination($request)
    {

        $Config = new Config;

        $date = $this->changeDate($request->date);
        $start = $date['startDate'];
        $end = $date['endDate'];
        $paging = trim($request->pg);
        $companyid = trim($request->compid);

        //
        $getdata = DB::table('vw_rank_destination as vrd')
        ->select(
            'vrd.id',
            DB::raw('COUNT(vrd.city) as total'),
            'vrd.city_name as name',
            'vrd.city_type_label as label'
        );
        if( trim($request->id) <> "-1")
        {
            $getdata = $getdata->where([
                'vrd.user_id'    =>  trim($request->id)
            ]);
        }
        $getdata  =  $getdata->where(['vrd.company_id'=>$companyid])
        ->whereBetween('vrd.date', [$start,$end])
        ->groupBy('vrd.city')
        ->orderBy('total', 'desc')
        ->orderBy('name', 'asc');

        $count = count($getdata->get());

        if( $count > 0 )
        {

            $total = 0;
            foreach($getdata->get() as $row)
            {
                $total += $row->total;
            }

            $getdata = $getdata->take($Config->table(['paging'=>$paging])['paging_item'])
            ->skip($Config->table(['paging'=>$paging])['paging_limit'])
            ->get();

            foreach($getdata as $row)
            {
                $list[] = [
                    'id'        =>  $row->id,
                    'name'      =>  $row->label . '. ' . ucwords(strtolower($row->name)),
                    'total'     =>  $row->total
                ];
            }

            $data = [
                'list'      =>  $list,
                'total'     =>  $total,
                'paging'    =>  $paging,
                'countpage' =>  ceil($count / $Config->table(['paging'=>$paging])['paging_item'] )
            ];
            return response()->json($data, 200);
        }
        else
        {
            $data = [
                'message'      =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }

 
        // return response()->json($data,200);
    }

    //
    public function changeDate($request)
    {

        if( trim($request) != null )
        {
            $date = explode("_", $request);
            $startDate = $date[0];
            $endDate = $date[1];
            $endDate = date('Y-m-d', strtotime($endDate . '+1 day') );
        }
        else
        {
            $startDate = date('Y-m-d', time());
            $endDate = date('Y-m-d', strtotime($startDate . '+1 day') );
        }

        return ['startDate'=>$startDate,'endDate'=>$endDate];
    }

}