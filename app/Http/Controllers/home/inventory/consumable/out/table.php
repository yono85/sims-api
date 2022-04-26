<?php
namespace App\Http\Controllers\home\inventory\consumable\out;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use DB;

class table extends Controller
{
    //
    public function main(Request $request)
    {
        $Config = new Config;

        //
        $src = '%' . trim($request->search) . '%';
        $paging = trim($request->paging);
        $sortname = trim($request->sort_name);
        $type = trim($request->selected_type);

        //
        $getdata = DB::table('vw_consum_outs')
        ->select('*')
        ->where([
            ['po_code',    'like', $src],
            ['status',  '=', 1]
        ]);
        if($type != '-1')
        {
            $getdata = $getdata->where([
                'consum_type'       =>  $type
            ]);
        }

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
        $gettable = $getdata->orderBy('id', $sortname)
        ->take($Config->table(['paging'=>$paging])['paging_item'])
        ->skip($Config->table(['paging'=>$paging])['paging_limit'])
        ->get();

        foreach($gettable as $row)
        {
            $list[] = [
                'id'            =>  $row->id,
                'code'          =>  $row->code,
                'name'          =>  $row->name,
                'type'          =>  $row->consum_type_name,
                'consum_type'   =>  $row->consum_type,
                'po_code'       =>  $row->po_code,
                'project'       =>  $row->po_name,
                'quantity'      =>  $row->quantity,
                'customer'      =>  $row->customer_type . ' ' . $row->customer,
                'date'          =>  $Config->timeago($row->date),
                'admin'         =>  $Config->nickName($row->admin)
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

        return response()->json($data,200);

    }
}