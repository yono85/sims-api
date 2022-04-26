<?php
namespace App\Http\Controllers\veriforder;
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
            ['o.search', 'like', $search],
            ['o.status', '=', 1],
            ['o.payment', '=', 1],
            // ['o.bulking', '=', 0]
        ];

        $cektable = tblData::from('orders as o')
        ->where($where);
        if( $request->status != '-1')
        {
            if ( $request->status == '1')
            {
                $cektable = $cektable->where([
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
        if( $account['config']['company_type'] == '1' )
        {
            $cektable = $cektable->whereIn('type', ['1','3']);
        }
        if( $account['config']['company_type'] == '2' )
        {
            $cektable = $cektable->whereIn('type', ['2','4']);
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
                'o.id', 'o.type', 'o.token', 'o.uniq', 'o.invoice', 'o.created_at', 'o.checkout', 'o.payment', 'o.paid', 'o.notes', 'o.field',
                'u.name as admin_name', 'o.expire_payment_date as exp', 'o.paid',
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
            if( $request->status != '-1')
            {
                if ( $request->status == '1')
                {
                    $gettable = $gettable->where([
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
            if( $account['config']['company_type'] == '1' )
            {
                $gettable = $gettable->whereIn('o.type', ['1','3']);
            }
            if( $account['config']['company_type'] == '2' )
            {
                $gettable = $gettable->whereIn('o.type', ['2','4']);
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
                    'paid'          =>  $row->paid,
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
                    'exp'           =>  $row->exp === '' ? date('d/m/Y', strtotime($row->created_at)) : date('d/m/Y H.i', strtotime($row->exp)),
                    'invoice_url'   => $Config->apps()['URL']['STORE'] . '/invoice/v1?token=' . $row->token,
                    'price_show'    =>  $row->type === '1' ? 'p' : ( $account['config']['company_type'] === '1' ? 'p' : 'r' ),
                    'promo'         =>  $promo
                ];
            }

            $response = [
                'paging'        =>  $paging,
                'total'         =>  $cektable,
                'countpage'     =>  ceil($cektable / $Config->table(['paging'=>$paging])['paging_item'] ),
                'list'          =>  $list,
                'company_type'  =>  $account['config']['company_type']
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
            'response'          =>  $status === 200 ? $response : ''
        ];

        return response()->json($data, $status);
    }



}
