<?php
namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\Http\Controllers\access\manage as Refresh;
use App\users as tblUsers;



class table extends Controller
{
    //
    public function main(Request $request)
    {
        //default config
        $Config = new Config;

        //request
        $paging = trim($request->paging);
        $level = trim($request->level);
        $search = '%' . str_replace(";", "", trim($request->search)) . '%';
        $sort = trim($request->sort);
        $status = trim($request->status);


        //ceking refresh
        // $Refresh = newco Refresh;
        // $Refresh = $Refresh->refresh();

        $getaccount = new \App\Http\Controllers\account\index;
        $getaccount = $getaccount->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);
    

        $where = [
            ['u.search', 'like', '%' . $search . '%'],
            ['uc.company_id', '=', $getaccount['config']['company_id']]
        ];

        //cekin table
        $cektable = tblUsers::from('users as u')
        ->leftJoin('user_configs as uc', function($join)
        {
            $join->on('uc.user_id', '=', 'u.id');
        })
        ->where($where);
        if( $getaccount['sublevel'] != '1')
        {
            $cektable = $cektable->where([
                ['u.sublevel', '>', '1']
            ]);
        }
        if( $sublevel != '-1')
        {
            $cektable = $cektable->where([
                'u.sub_level'           =>  $sublevel
            ]);
        }
        if( $status != '-1')
        {

            if( $status == '2')
            {
                $cektable = $cektable->where([
                    'u.registers'           =>  0
                ]);
            }
            else
            {

                $cektable = $cektable->where([
                    'u.status'           =>  $status
                ]);
            }
        }
        $cektable = $cektable->count();
    

        if( $cektable > 0)
        {

            $getdata = tblUsers::from('users as u')
            ->select(
                'u.id', 'u.name', 'u.email', 'u.phone', 'u.status', 'u.username', 'u.level', 'u.sub_level', 'u.gender', 'u.created_at', 'u.registers',
                'us.name as sublevel_name',
                'adm.name as admin_name'
            )
            ->leftJoin('user_configs as uc', function($join)
            {
                $join->on('uc.user_id', '=', 'u.id');
            })
            ->leftJoin('users as adm', function($join)
            {
                $join->on('adm.id', '=', 'uc.admin_id');
            })
            ->leftJoin('user_sublevels as us', function($join)
            {
                $join->on([
                    'us.sub_level'=>'u.sub_level'
                ]);
            })
            ->where($where);
            if( $getaccount['sublevel'] != '1')
            {
                $getdata = $getdata->where([
                    ['u.sub_level', '>', '1']
                ]);
            }
            if( $sublevel != '-1')
            {
                $getdata = $getdata->where([
                    'u.sub_level'           =>  $sublevel
                ]);
            }
            if( $status != '-1')
            {

                if( $status == '2')
                {
                    $getdata = $getdata->where([
                        'u.registers'           =>  0
                    ]);
                }
                else
                {

                    $getdata = $getdata->where([
                        'u.status'           =>  $status
                    ]);
                }
            }
            $getdata = $getdata->orderBy('u.id', $sort)
            ->take($Config->table(['paging'=>$paging])['paging_item'])
            ->skip($Config->table(['paging'=>$paging])['paging_limit'])
            ->get();

            foreach($getdata as $row)
            {
                
                $admin = explode(' ', $row->admin_name);

                $list[] = [
                    'id'            =>  $row->id,
                    'name'          =>  $row->name,
                    'email'         =>  $row->email,
                    'phone'         =>  $row->phone,
                    'username'      =>  $row->username,
                    'gender'        =>  $row->gender === 1 ? 'male' : 'female',
                    'date'          =>  $Config->timeago($row->created_at),
                    'level'         =>  $row->level,
                    'sub_level'     =>  $row->sublevel,
                    'sublevel_name' =>  $row->sublevel_name,
                    'registers'     =>  $row->registers,
                    'status'        =>  $row->status,
                    'admin'    =>  $admin[0]
                ];
            }


            $status = 200;
            $response = [
                'list'          =>  $list,
                'paging'        =>  $paging,
                'total'         =>  $cektable,
                'countpage'     =>  ceil($cektable / $Config->table(['paging'=>$paging])['paging_item'] )
            ];
        }
        else
        {
            $status = 404;

        }

        $data = [
            // 'refresh'           =>  $Refresh,
            'message'           =>  $status === 404 ? 'Data tidak ditemukan' : '',
            'response'          =>  $status === 404 ? '' : $response
        ];

        return response()->json($data, $status );
    }

}