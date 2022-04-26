<?php
namespace App\Http\Controllers\pelayanan\pengguna;
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
        $sublevel = trim($request->sublevel);
        $search = "%" . str_replace(";", "", trim($request->search)) . "%";
        $paging = trim($request->paging);
        $sort = trim($request->sort);
        $status = trim($request->status);

        $getdata = DB::table('vw_users as u')
        ->select(
            'u.id', 'u.name as name', 'u.username', 'u.level_name', 'u.phone', 'u.email', 'u.date', 'u.registers', 'u.admin', 'u.status',
            'l.name as lembaga_name'
        )
        ->leftJoin('lembagas as l', function($join)
        {
            $join->on('l.id', '=', 'u.company_id');
        });
        if( $sublevel != '-1')
        {
            $getdata = $getdata->where([
                'u.sublevel'   =>  $sublevel
            ]);
        }
        if( $status != '-1')
        {
            if($status == '3')
            {
                $getdata = $getdata->where([
                    'u.status'      =>  0
                ]);
            }
            else
            {
                $getdata = $getdata->where([
                    'u.registers'       =>  ($status === '1' ? 1 : 0)
                ]);
            }
        }
        $getdata = $getdata->where([
            ['u.search', 'like', $search],
            ['u.level', '=', 2],
            ['u.status', '=', 1]
        ]);

        $count = $getdata->count();

        
        if( $count == 0)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }

        $gettable = $getdata->orderBy('u.id', $sort)
        ->take($Config->table(['paging'=>$paging])['paging_item'])
        ->skip($Config->table(['paging'=>$paging])['paging_limit'])
        ->get();
        
        foreach($gettable as $row)
        {
            $list[] = [
                'id'        =>  $row->id,
                'name'      =>  $row->name,
                'username'  =>  $row->username,
                'sublevel'  =>  str_replace('User ', '', $row->level_name),
                'lembaga'   =>  $row->lembaga_name,
                'phone'     =>  $row->phone,
                'email'     =>  $row->email,
                'date'      =>  $Config->timeago($row->date),
                'registers'  =>  $row->registers,
                'admin'     =>  $row->admin,
                'status'    =>  $row->status
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