<?php
namespace App\Http\Controllers\news\pengumuman;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\pengumumans as tblPengumumans;

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
        $getdata = tblPengumumans::from('pengumumans as p')
        ->select(
            'p.id', 'p.token', 'p.name', 'p.date as datex', 'p.created_at as date', 'p.publish', 'p.text', 'p.text_code',
            'u.name as admin'
        )
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'p.user_id');
        })
        ->where([
            ['p.name', 'like', $search]
        ]);
        if( $status != '-1')
        {
            $getdata = $getdata->where([
                'p.publish'     =>  $status
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

        $gettable = $getdata->orderBy('p.id', $sort)
        ->take($Config->table(['paging'=>$paging])['paging_item'])
        ->skip($Config->table(['paging'=>$paging])['paging_limit'])
        ->get();
        
        foreach($gettable as $row)
        {
            $list[] = [
                'id'        =>  $row->id,
                'token'     =>  $row->token,
                'name'      =>  $row->name,
                'text'      =>  $row->text,
                'admin'     =>  $Config->nickName($row->admin),
                'date'      =>  $Config->timeago($row->date),
                'datex'     =>  $Config->roleFormatDate($row->datex),
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