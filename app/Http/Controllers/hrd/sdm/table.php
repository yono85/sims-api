<?php
namespace App\Http\Controllers\hrd\sdm;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\order_sdms as tblOrderSdms;
use App\Http\Controllers\config\index as Config;
use DB;

class table extends Controller
{
    //
    public function main(Request $request)
    {
        $Config = new Config;

        //
        $search = '%' . trim($request->search) . '%';
        $paging = trim($request->pg);
        $sort = trim($request->sort_name);
        $progress = trim($request->selected_status);

        //
        $getdata = DB::table('vw_order_sdms as vos')
        ->where([
            ['vos.name', 'like', $search]    
        ]);
        if($progress != '-1')
        {
            $getdata = $getdata->where([
                'vos.progress'      =>  $progress
            ]);
        }

        $count = $getdata->count();

        //IF NULL
        if($count == 0)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }

        // IF FOUND
        $gettable = $getdata->orderBy('id', $sort)
        ->take($Config->table(['paging'=>$paging])['paging_item'])
        ->skip($Config->table(['paging'=>$paging])['paging_limit'])
        ->get();

        foreach($gettable as $row)
        {
            
            $getlist = DB::table('vw_po_smd')
            ->where([
                'poid'  =>  $row->poid
            ])->get();

            $list[] = [
                'id'        =>  $row->id,
                'name'      =>  $row->name,
                'poid'      =>  $row->poid,
                'code'      =>  $row->code,
                'sdm'       =>  $row->sdm,
                'marketing' =>  $row->marketing,
                'progress'  =>  $row->progress,
                'startdate' =>  $row->startdate,
                'enddate'   =>  $row->enddate,
                'address'   =>  $row->address,
                'date'      =>  $Config->timeago($row->date),
                'item'      =>  $getlist,
                'customer'  =>  $row->customer_type . $row->customer
            ];
        }
    
        

        $data = [
            'message'       =>  '',
            'response'      =>  [
                'list'          =>  $list,
                'paging'        =>  $paging,
                'total'         =>  $count,
                'countpage'     =>  ceil($count / $Config->table(['paging'=>$paging])['paging_item'] )
            ]
        ];


        return response()->json($data, 200);
    }
}