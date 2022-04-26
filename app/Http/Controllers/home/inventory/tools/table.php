<?php
namespace App\Http\Controllers\home\inventory\tools;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\assets as tblAssets;
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
        $getdata = tblAssets::from('assets as a')
        ->select(
            'a.id', 'a.name', 'a.type', 'a.code', 'a.quantity', 'a.kalibrasi_status', 'a.kalibrasi_date', 'a.description', 'a.created_at as date', 'a.assesoris',
            'at.name as type_name',
            'u.name as admin'
        )
        ->leftJoin('asset_types as at', function($join)
        {
            $join->on('at.id', '=', 'a.type');
        })
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'a.user_id');
        })
        ->where([
            ['a.name',    'like', $src],
            ['a.status',  '=', 1]
        ]);
        if( $type != '-1')
        {
            $getdata = $getdata->where([
                'a.type'        =>  $type
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
        $gettable = $getdata->orderBy('a.name', $sortname)
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
                'kalibrasi_date'    =>  date('d/m/Y', strtotime($row->kalibrasi_date)),
                'kalibrasi_status'  =>  $row->kalibrasi_status,
                'assesoris'         =>  $row->assesoris === '' ? '' : json_decode($row->assesoris),
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