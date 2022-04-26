<?php
namespace App\Http\Controllers\orders;
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
        $companyid = trim($request->companyid);
        $csid = trim($request->csid);

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
            ['o.status', '=', 1],
            ['o.company_id', '=', $companyid],
            // ['o.user_id', '=', $account['id']],
            ['o.type', '!=', 3]
        ];

        $getdata = tblData::from('orders as o')
        ->select(
            'o.id', 'o.type', 'o.token',  'o.uniq', 'o.invoice', 'o.created_at', 'o.checkout', 'o.payment', 'o.paid', 'o.notes', 'o.field',
            'u.name as admin_name',
            // promo
            'o.promo_id',
            DB::raw('IFNULL(op.field, "") as promo_field'),
            DB::raw('IFNULL(op.total, 0) as promo_total'),
            DB::raw('IFNULL(op.status, 0) as promo_status'),
            DB::raw('IFNULL(aps.name, "") as promo_name'),
            DB::raw('IFNULL(aps.description, "") as promo_detail')
        )
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'o.user_id');
        })
        ->leftJoin('order_promos as op', function($join)
        {
            $join->on('op.id', '=', 'o.promo_id');
        })
        ->leftJoin('app_promos as aps', function($join)
        {
            $join->on('aps.id', '=', 'op.promo_id');
        })
        ->where($where);
        if( $csid != '-1')
        {
            $getdata = $getdata->where([
                'o.user_id'     =>  $csid
            ]);
        }
        if( $request->status != '-1')
        {
            if( $request->status == '1')
            {

                $getdata = $getdata->where([
                    'o.payment' =>  0,
                    'o.paid'    =>  0
                ]);
            }
            elseif ( $request->status == '2')
            {
                $getdata = $getdata->where([
                    'o.payment' =>  1,
                    'o.paid'    =>  0
                ]);
            }
            else
            {
                $getdata = $getdata->where([
                    'o.paid'    =>  1
                ]);
            }
            
        }
        if( $date != '')
        {
            $getdata = $getdata->whereBetween('o.created_at', [$sdate, $edate] );
        }
        $cektable = $getdata->count();

        if( $cektable > 0 )
        {


            $gettable = $getdata->orderBy('o.created_at', $sort_date)
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

                //item
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

                // promo
                $promo = [
                    'id'                =>  $row->promo_id,
                    'total'             =>  $row->promo_total,
                    'name'              =>  $row->promo_name,
                    'detail'            =>  $row->promo_detail,
                    'status'            =>  ($row->promo_id === 0 ? 0 : ($row->promo_id !== 0 && $row->promo_status === 0 ? 0 : 1))
                ];
                
                //
                $list[] = [
                    'id'            =>  $row->id,
                    'type'          =>  $row->type,
                    'uniq'          =>  $row->uniq,
                    'invoice'       =>  $row->invoice,
                    'item'          =>  $item,
                    'status'        =>  $row->paid === 1 ? 'sc' :  ( $row->payment === 1 ? 'mv' : 'mp') ,
                    'customers'     =>  $field['customers'],
                    'shiping'       =>  $field['shiping'],
                    'payment'       =>  $field['payment'],
                    'destination'   =>  $field['destination'],
                    'date'          =>  $Config->timeago($row->created_at),
                    'notes'         =>  $row->notes,
                    'admin_name'    =>  $admin[0],
                    'invoice_url'   => $Config->apps()['URL']['STORE'] . '/invoice/v1?token=' . $row->token,
                    'price_show'    =>  $row->type === 1 || $row->type === 2 ? 'p' : 'r',
                    'url_cwa_shiping'   =>  $Config->apps()['URL']['SLINK'] . '/clickwa/orders/shiping?token=' . $row->token,
                    'promo'         =>  $promo
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
            'response'          =>  $status === 200 ? $response : ''
        ];

        return response()->json($data, $status);
    }


    //
    public function customers($request)
    {
        //
        $Config = new Config;


        $account = new \App\Http\Controllers\account\index;
        $account = $account->viewtype([
            'type'          =>  'key',
            'token'         =>  $request->header('key')
        ]);
        //
        // $Refresh = new Refresh;
        // $Refresh = $Refresh->refresh();

        //
        // $account = $Refresh['refresh']['account'];

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
            ['o.status', '=', 1],
            ['o.user_id', '=', $account['id']],
            ['o.type', '!=', 3]
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
                'u.name as admin_name'
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
                    'invoice'       =>  $row->invoice,
                    'item'          =>  $item,
                    'status'        =>  $row->paid === 1 ? 'sc' :  ( $row->payment === 1 ? 'mv' : 'mp') ,
                    'customers'     =>  $field['customers'],
                    'shiping'       =>  $field['shiping'],
                    'payment'       =>  $field['payment'],
                    'destination'   =>  $field['destination'],
                    'date'          =>  $Config->timeago($row->created_at),
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
            'response'          =>  $status === 200 ? $response : ''
        ];

        return response()->json($data, $status);
    }



}
