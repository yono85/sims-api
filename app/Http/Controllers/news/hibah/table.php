<?php
namespace App\Http\Controllers\news\hibah;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\hibahs as tblHibahs;
use DB;

class table extends Controller
{
    //
    public function main(Request $request)
    {
        $Config = new Config;

        //
        $search = "%" . trim($request->search) . "%";
        $paging = trim($request->paging);
        $sort = trim($request->sort);
        $status = trim($request->status);

        //
        $getdata = tblHibahs::from('hibahs as h')
        ->select(
            'h.id', 'h.token', 'h.type', 'h.name', 'h.created_at as date', 'h.publish', 'h.text', 'h.text_code', 'h.start_date', 'h.start_time', 'h.end_date', 'h.end_time',
            'u.name as admin',
            DB::raw('IFNULL(lt.name, "") as lembaga')
        )
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'h.user_id');
        })
        ->leftJoin('lembaga_types as lt', function($join)
        {
            $join->on('lt.id', '=', 'h.type');
        })
        ->where([
            ['h.name', 'like', $search]
        ]);
        if( $status != '-1')
        {
            $getdata = $getdata->where([
                'h.publish'     =>  $status
            ]);
        }

        $count = $getdata->count();

        //NULL
        if( $count == 0)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }

        $gettable = $getdata->orderBy('h.id', $sort)
        ->take($Config->table(['paging'=>$paging])['paging_item'])
        ->skip($Config->table(['paging'=>$paging])['paging_limit'])
        ->get();
        
        foreach($gettable as $row)
        {
            $list[] = [
                'id'        =>  $row->id,
                'token'     =>  $row->token,
                'type'      =>  $row->type,
                'name'      =>  $row->name,
                'text'      =>  $row->text,
                'admin'     =>  $Config->nickName($row->admin),
                'date'      =>  $Config->timeago($row->date),
                'lembaga'   =>  $row->lembaga === '' ? 'Semua Lembaga' : $row->lembaga,
                'datex'     =>  [
                    'start'     =>  [
                        'date'      =>  $Config->roleFormatDate($row->start_date),
                        'time'      =>  $row->start_time
                    ],
                    'end'       =>  [
                        'date'      =>  $Config->roleFormatDate($row->end_date),
                        'time'      =>  $row->end_time
                    ]
                ],
                'publish'   =>  $row->publish
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