<?php
namespace App\Http\Controllers\pelayanan\lembaga;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\lembagas as tblLembagas;
use DB;

class table extends Controller
{
    //
    public function main(Request $request)
    {
        $Config = new Config;

        //
        $search = "%" . str_replace(";", "", trim($request->search)) . "%";
        $paging = trim($request->paging);
        $sort = trim($request->sort);
        $status = trim($request->status);

        //
        $getdata = tblLembagas::from('lembagas as l')
        ->select(
            'l.id', 'l.token', 'l.type', 'l.name', 'l.npwp', 'l.phone', 'l.email', 'l.verify', 'l.verify_user', 'l.verify_date', 'l.address', 'l.status', 'l.owner', 'l.created_at',
            'lt.name as type_name',
            'u.name as admin'
        )
        ->leftJoin('lembaga_types as lt', function($join)
        {
            $join->on('lt.id', '=', 'l.type');
        })
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'l.user_id');
        });
        if( $status != '-1')
        {
            $status = $status === '1' ? 1 : 0;
            $getdata = $getdata->where([
                'l.verify'      =>  $status
            ]);
        }
        $getdata = $getdata->where([
            ['l.search', 'like', $search],
            ['l.status', '=', 1]
        ]);

        $count = $getdata->count();
        
        if( $count == 0)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }

        $count = $getdata->count();

        //
        $gettable = $getdata->orderBy('l.id', $sort)
        ->take($Config->table(['paging'=>$paging])['paging_item'])
        ->skip($Config->table(['paging'=>$paging])['paging_limit'])
        ->get();

        foreach($gettable as $row)
        {
            $owner = json_decode($row->owner, true);
            $address = json_decode($row->address, true);

            $list[] = [
                'id'        =>  $row->id,
                'token'     =>  $row->token,
                'type'      =>  $row->type,
                'type_name'  =>  $row->type_name,
                'name'      =>  $row->name,
                'npwp'      =>  $row->npwp,
                'phone'     =>  $row->phone,
                'email'     =>  $row->email,
                'verify'    =>  [
                    'status'        =>  $row->verify,
                    'user'          =>  '',
                    'date'          =>  $row->verify === 0 ? '' : date('d/m/Y', strtotime($row->verify_date))
                ],
                'owner'     =>  [
                    'ketua'         =>  $owner['ketua'],
                    'sekertaris'    =>  $owner['sekertaris'],
                    'bendahara'     =>  $owner['bendahara']
                ],
                'address'   =>  [
                    'name'      =>  $address['name'],
                    'line1'     =>  $address['kecamatan'] . ' - ' . $address['city'],
                    'line2'     =>  $address['provinsi'] . ' - ' . $address['kodepos'],
                ],
                'admin'     =>  $Config->nickName($row->admin),
                'date'      =>  $Config->timeago($row->created_at),
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