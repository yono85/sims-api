<?php
namespace App\Http\Controllers\bulkingpayment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\orders as tblData;
use App\order_items as tblOrderItems;
use App\order_bulkings as tblOrderBulkings;
use App\Http\Controllers\access\manage as Refresh;
use DB;

class table extends Controller
{

    //
    public function main(Request $request)
    {
        //
        $Config = new Config;


        $account = new \App\Http\Controllers\account\index;
        $account = $account->viewtype([
            'type'          =>  'key',
            'token'         =>  $request->header('key')
        ]);
        
        //
        $paging = trim($request->pg);
        $sort_date = trim($request->sortdate);
        $search = '%' . trim($request->src) . '%';
        $date = trim($request->date);

        if( $date != '')
        {
            $date = explode('-', $date);
            $sdate = explode('/', $date[0]); $sdate = $sdate[2] . '-' . $sdate[1] . '-' . $sdate[0];
            $edate = explode('/', $date[1]); $edate = $edate[2] . '-' . $edate[1] . '-' . $edate[0];
            $edate = date('Y-m-d', strtotime($edate . '+1 day') ) ;

        }

        //
        $where = [
            ['o.search', 'like', $search],
            // ['o.bulking', '=', 1],
            // ['o.bulking_keep', '=', 1],
            ['o.status', '=', 1]
        ];

        $cektable = tblOrderBulkings::from('order_bulkings as o')
        ->where($where);
        if( $request->status != '-1')
        {

            $cektable = $cektable->where([
                'o.paid' =>  $request->status,
            ]);

            // if( $request->status == '1')
            // {

            //     $cektable = $cektable->where([
            //         'o.payment' =>  0,
            //         'o.paid'    =>  0
            //     ]);
            // }
            // elseif ( $request->status == '2')
            // {
            //     $cektable = $cektable->where([
            //         'o.payment' =>  1,
            //         'o.paid'    =>  0
            //     ]);
            // }
            // else
            // {
            //     $cektable = $cektable->where([
            //         'o.paid'    =>  1
            //     ]);
            // }
            
        }
        if( $date != '')
        {
            $cektable = $cektable->whereBetween('o.created_at', [$sdate, $edate] );
        }
        $cektable = $cektable->count();

        if( $cektable > 0 )
        {

            $gettable = tblOrderBulkings::from('order_bulkings as o')
            ->select(
                'o.id', 'o.token', 'o.invoice','o.field', 'o.total_paid', 'o.quantity', 'o.paid', 'o.created_at as date', 'o.expire_payment_date as exp',
                'u.name as admin_name'
            )
            ->leftJoin('users as u', function($join)
            {
                $join->on('u.id', '=', 'o.user_id');
            })
            ->leftJoin('user_companies as uc', function($join)
            {
                $join->on('uc.id', '=', 'o.company_user');
            })
            ->where($where);
            if( $request->status != '-1')
            {

                $gettable = $gettable->where([
                    'o.paid'    =>  $request->status
                ]);

            }
            if( $date != '')
            {
                $gettable = $gettable->whereBetween('o.created_at', [$sdate, $edate] );
            }


            $gettable = $gettable->orderBy('o.created_at', $sort_date)
            ->take($Config->table(['paging'=>$paging])['paging_item'])
            ->skip($Config->table(['paging'=>$paging])['paging_limit'])
            ->get();


            //
            foreach($gettable as $row)
            {

                $field = json_decode($row->field, true);
                // $customer = $field['customers'];
                //
                $admin = explode(' ', $row->admin_name);
                $bank = $field['orders']['payment'];

                //
                $list[] = [
                    'id'            =>  $row->id,
                    'token'         =>  $row->token,
                    'paid'          =>  $row->paid,
                    'invoice'       =>  $row->invoice,
                    'item'          =>  json_decode($field['list']),
                    'total'         =>  $row->total_paid,
                    'status'        =>  $row->paid === 2 ? 'sc' :  ( $row->paid === 1 ? 'mv' : 'mp'),
                    'date'          =>  $Config->timeago($row->date),
                    'exp'           =>  $row->exp === '' ? date('d/m/Y', strtotime($row->created_at)) : date('d/m/Y H.i', strtotime($row->exp)),
                    'admin_name'    =>  $admin[0],
                    'bank'                  =>  $bank
                ];
            }

            $response = [
                'paging'        =>  $paging,
                'total'         =>  $cektable,
                'countpage'     =>  ceil($cektable / $Config->table(['paging'=>$paging])['paging_item'] ),
                'list'          =>  $list
            ];

            $message = '';
            $status = 200;
        }
        else
        {
            $message = 'Data tidak ditemukan';
            $status = 404;
        }


        //
        $data = [
            'message'           =>  $message,
            // 'refresh'           =>  $Refresh,
            'response'          =>  $status === 200 ? $response : '',
            'search'            =>  $search,
            'date'              =>  $date === '' ? '' : $edate
        ];

        return response()->json($data, $status);
    }



}
