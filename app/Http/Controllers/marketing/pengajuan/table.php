<?php
namespace App\Http\Controllers\marketing\pengajuan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\po_orders as tblPoOrders;
use App\Http\Controllers\config\index as Config;
use DB;

class table extends Controller
{
    //
    public function main(Request $request)
    {
        $Config = new Config;

        $src = '%' . str_replace(';', '', trim($request->search) ) . '%';
        $paging = trim($request->paging);
        $sort = trim($request->sort_name);
        $progress = trim($request->selected_status);


        $getdata = tblPoOrders::from('po_orders as po')
        ->select(
            'po.id', 'po.name', 'po.code', 'po.token', 'po.name', 'po.price', 'po.startdate', 'po.enddate','po.created_at as date', 'po.customer_id', 'po.address', 'po.sdm', 'po.sdm_status', 'po.tools', 'po.tools_status', 'po.progress',
            'c.name as customer', 'ct.alias as customer_type',
            'u.name as admin',
            DB::raw('IFNULL(ud.url, "") as document')
        )
        ->leftJoin('customers as c', function($join)
        {
            $join->on('c.id', '=', 'po.customer_id');
        })
        ->leftJoin('customer_types as ct', function($join)
        {
            $join->on('ct.id', '=', 'c.type');
        })
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'po.user_id');
        })
        ->leftJoin('upload_documents as ud', function($join)
        {
            $join->on('ud.link_id', '=', 'po.id')
            ->where([
                'ud.type'       =>  1,
                'ud.subtype'    =>  0,
                'ud.status'     =>  1
            ]);
        })
        ->where([
            ['po.name','like', $src],
            ['po.status', '=', 1]
        ]);
        if( $progress != '-1')
        {
            $getdata = $getdata->where([
                'progress'      =>  $progress
            ]);
        }

        $count = $getdata->count();

        if( $count == 0)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }

        
        $gettable = $getdata->orderBy('po.id', $sort)
        ->take($Config->table(['paging'=>$paging])['paging_item'])
        ->skip($Config->table(['paging'=>$paging])['paging_limit'])
        ->get();

        foreach($gettable as $row)
        {
            $list[] = [
                'id'            =>  $row->id,
                'name'          =>  $row->name,
                'token'         =>  $row->token,
                'code'          =>  $row->code,
                'price'         =>  $row->price,
                'startdate'     =>  $row->startdate,
                'enddate'       =>  $row->enddate,
                'date'          =>  $Config->timeago($row->date),
                'customer_id'      =>  $row->customer_id,
                'customer'      =>  $row->customer_type . ' ' . $row->customer,
                'admin'         =>  $Config->nickName($row->admin),
                'address'       =>  $row->address,
                'progress'      =>  $row->progress,
                'document'      =>  $row->document,
                'sdm'           =>  [
                    'label'         =>  $row->sdm,
                    'status'        =>  $row->sdm_status
                ],
                'tools'         =>  [
                    'label'         =>  $row->tools,
                    'status'        =>  $row->tools_status
                ]
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