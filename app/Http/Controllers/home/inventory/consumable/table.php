<?php
namespace App\Http\Controllers\home\inventory\consumable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\consumables as tblConsumables;
use App\Http\Controllers\config\index as Config;
use DB;

class table extends Controller
{
    //
    public function main(Request $request)
    {
        $Config = new Config;

        $src = '%' . trim($request->search) . '%';
        $paging = trim($request->paging);
        $type = trim($request->selected_type);
        $sortname = trim($request->sort_name);

        //
        $getdata = tblConsumables::from('consumables as c')
        ->select(
            'c.id', 'c.name', 'c.type', 'c.code', 'c.quantity', 'c.quantity_limit', 'c.expired_date', 'c.expired_status', 'c.description', 'c.created_at as date',
            'ct.name as type_name',
            'u.name as admin'
        )
        ->leftJoin('consumable_types as ct', function($join)
        {
            $join->on('ct.id', '=', 'c.type');
        })
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'c.user_id');
        })
        ->where([
            ['c.name',    'like', $src],
            ['c.status',  '=', 1]
        ]);
        if( $type != '-1')
        {
            $getdata = $getdata->where([
                'c.type'        =>  $type
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
        $gettable = $getdata->orderBy('c.name', $sortname)
        ->take($Config->table(['paging'=>$paging])['paging_item'])
        ->skip($Config->table(['paging'=>$paging])['paging_limit'])
        ->get();

        foreach($gettable as $row)
        {
            $list[] = [
                'id'            =>  $row->id,
                'code'          =>  $row->code,
                'type'          =>  $row->type,
                'type_name'     =>  $row->type_name,
                'name'          =>  $row->name,
                'quantity'      =>  $row->quantity,
                'quantity_limit'    =>  $row->quantity_limit,
                'expired_date'    =>  date('d/m/Y', strtotime($row->expired_date)),
                'expired_status'  =>  $row->expired_status,
                'description'   =>  $row->description,
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