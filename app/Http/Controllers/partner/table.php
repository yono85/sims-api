<?php
namespace App\Http\Controllers\partner;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\Http\Controllers\access\manage as Refresh;
use App\user_companies as tblUserCompanies;

class table extends Controller
{
    //
    public function main(Request $request)
    {
        //default config
        $Config = new Config;

        //request
        $paging = trim($request->pg);
        $search = '%' . trim($request->src) . '%';
        $sort = trim($request->sort);
        $type = trim($request->type);
        $status = trim($request->status);
        $register = trim($request->register);



        // account with key
        $getaccount = new \App\Http\Controllers\account\index;
        $getaccount = $getaccount->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);
    

        if( $register != '')
        {
            $register = explode(";", $register);
            $start_date = $register[0];
            $end_date = $register[1];

            $end_date = date('Y-m-d', strtotime($end_date . '+1 day') );
        }
        // else
        // {
        //     $register = '';
        //     $start_date = '';
        //     $end_date = '';
        // }


        $where = [
            ['uc.name', 'like', '%' . $search . '%'],
            ['uc.status', '=', 1]
        ];

        //cekin table
        $cektable = tblUserCompanies::from('user_companies as uc')
        ->where($where);
        if( $type != '-1')
        {
            $cektable = $cektable->where([
                'uc.type'       =>  $type
            ]);
        }
        if( $status != '-1')
        {
            $cektable = $cektable->where([
                'uc.verify'       =>  $status
            ]);
        }
        if( $register != '')
        {
            $cektable->whereBetween('uc.created_at', [$start_date, $end_date]);
        }
        $cektable = $cektable->whereNotIn('uc.id', [$getaccount['config']['company_id']])
        ->count();
    

        if( $cektable > 0)
        {

            $getdata = tblUserCompanies::from('user_companies as uc')
            ->select(
                'uc.id', 'uc.token', 'uc.name', 'uc.owner', 'uc.owner_contact', 'uc.expire_payment', 'uc.verify',
                'uc.address', 'uc.contact', 'uc.owner', 'uc.owner_contact', 'uc.kodepos', 'uc.created_at as date',
                'ct.name as type_name',
                'aop.name as provinsi_name',
                'aoc.name as city_name', 'aoc.type as city_type',
                'aok.name as kecamatan',
                'u.name as admin_name'
            )
            ->leftJoin('company_types as ct', function($join)
            {
                $join->on('ct.id', '=', 'uc.type');
            })
            ->leftJoin('app_origin_provinsis as aop', function($join)
            {
                $join->on('aop.id', '=', 'uc.provinsi');
            })
            ->leftJoin('app_origin_cities as aoc', function($join)
            {
                $join->on('aoc.id', '=', 'uc.city');
            })
            ->leftJoin('app_origin_kecamatans as aok', function($join)
            {
                $join->on('aok.id', '=', 'uc.kecamatan');
            })
            ->leftJoin('users as u', function($join)
            {
                $join->on('u.id', '=', 'uc.user_id');
            })
            ->where($where);
            if( $type != '-1')
            {
                $getdata = $getdata->where([
                    'uc.type'       =>  $type
                ]);
            }
            if( $status != '-1')
            {
                $getdata = $getdata->where([
                    'uc.verify'       =>  $status
                ]);
            }
            if( $register != '')
            {
                $getdata->whereBetween('uc.created_at', [$start_date, $end_date]);
            }
            $getdata = $getdata->whereNotIn('uc.id', [$getaccount['config']['company_id']])
            ->orderBy('uc.name', $sort)
            ->take($Config->table(['paging'=>$paging])['paging_item'])
            ->skip($Config->table(['paging'=>$paging])['paging_limit'])
            ->get();

            foreach($getdata as $row)
            {

                $admin = explode(' ', $row->admin_name);

                $list[] = [
                    'id'            =>  $row->id,
                    'name'          =>  $row->name,
                    'type_name'     =>  $row->type_name,
                    'address'       =>  $row->address,
                    'address2'      =>  ucwords(strtolower($row->kecamatan)). ' - ' . $row->city_type . ' ' . ucwords(strtolower($row->city_name)),
                    'address3'      =>  $row->provinsi_name . ' - ' . $row->kodepos,
                    'contact'       =>  json_decode($row->contact),
                    'owner'         =>  $row->owner,
                    'owner_contact' =>  json_decode($row->owner_contact),
                    'expire_payment'    =>  $row->expire_payment,
                    'verify'            =>  $row->verify,
                    'date'              =>  $Config->timeago($row->date),
                    'admin_name'        =>  $admin[0],
                    'url'               =>  '/dashboard/partner/profile?token=' . $row->token
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
            'response'          =>  $status === 404 ? '' : $response,
            // 'acccount'          =>  $getaccount['config']['company_id']
        ];

        return response()->json($data, $status );
    }
}