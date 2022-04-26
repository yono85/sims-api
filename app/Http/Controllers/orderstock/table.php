<?php
namespace App\Http\Controllers\orderstock;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\orders as tblData;
use App\order_items as tblOrderItems;
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
            ['o.type', '=', '3'],
            ['o.search', 'like', $search],
            ['o.status', '=', 1],
            ['o.user_id', '=', $account['id']]
        ];

        $cektable = tblData::from('orders as o')
        ->where($where);
        if( $request->status != '-1')
        {
            if( $request->status == '1')
            {

                $cektable = $cektable->where([
                    'o.payment' =>  0,
                    'o.paid'    =>  0
                ]);
            }
            elseif ( $request->status == '2')
            {
                $cektable = $cektable->where([
                    'o.payment' =>  1,
                    'o.paid'    =>  0
                ]);
            }
            else
            {
                $cektable = $cektable->where([
                    'o.paid'    =>  1
                ]);
            }
            
        }
        if( $date != '')
        {
            $cektable = $cektable->whereBetween('o.created_at', [$sdate, $edate] );
        }
        $cektable = $cektable->count();

        if( $cektable > 0 )
        {

            $gettable = tblData::from('orders as o')
            ->select(
                'o.id', 'o.type', 'o.token',  'o.uniq', 'o.invoice', 'o.created_at', 'o.checkout', 'o.payment', 'o.paid', 'o.notes', 'o.field',
                'u.name as admin_name', 'o.expire_payment_date as exp'
            )
            ->leftJoin('users as u', function($join)
            {
                $join->on('u.id', '=', 'o.user_id');
            })
            ->where($where);
            if( $request->status != '-1')
            {
                if( $request->status == '1')
                {

                    $gettable = $gettable->where([
                        'o.payment' =>  0,
                        'o.paid'    =>  0
                    ]);
                }
                elseif ( $request->status == '2')
                {
                    $gettable = $gettable->where([
                        'o.payment' =>  1,
                        'o.paid'    =>  0
                    ]);
                }
                else
                {
                    $gettable = $gettable->where([
                        'o.paid'    =>  1
                    ]);
                }
                
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

                $item = tblOrderItems::select(
                    'order_items.quantity',
                    'p.name as product_name'
                )
                ->leftJoin('products as p', function($join)
                {
                    $join->on('p.id', '=', 'order_items.product_id');
                })
                ->where([
                    'order_items.order_id'          =>  $row->id,
                    'order_items.status'            =>  1
                ])->get();

                //
                $list[] = [
                    'id'            =>  $row->id,
                    'type'          =>  $row->type,
                    'uniq'          =>  $row->uniq,
                    'paid'          =>  $row->paid,
                    'invoice'       =>  $row->invoice,
                    'item'          =>  $item,
                    'status'        =>  $row->paid === 1 ? 'sc' :  ( $row->payment === 1 ? 'mv' : 'mp') ,
                    'customers'     =>  $field['customers'],
                    'shiping'       =>  $field['shiping'],
                    'payment'       =>  $field['payment'],
                    'destination'   =>  $field['destination'],
                    'date'          =>  $Config->timeago($row->created_at),
                    'exp'           =>  $row->exp === '' ? date('d/m/Y', strtotime($row->created_at)) : date('d/m/Y H.i', strtotime($row->exp)),
                    'notes'         =>  $row->notes,
                    'admin_name'    =>  $admin[0],
                    'invoice_url'   => $Config->apps()['URL']['STORE'] . '/invoice/v1?token=' . $row->token,
                    'price_show'    =>  $row->type === 1 || $row->type === 2 ? 'p' : 'r'
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
