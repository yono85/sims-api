<?php
namespace App\Http\Controllers\products;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\products as tblProducts;
use App\Http\Controllers\config\index as Config;
use DB;

class table extends Controller
{
    //
    public function main(Request $request)
    {

        //default config
        $Config = new Config;

        //request 
        $src = trim($request->src);
        $type = trim($request->type);
        $compid = trim($request->compid);
        $sort_name = trim($request->sort);
        $paging = trim($request->pg);
        $status = trim($request->status);

        $where = [
            ['p.name', 'like', '%' . $src . '%'],
            ['p.status', '>', 0]
        ];

        //product
        $getdata = tblProducts::from('products as p')
        ->select(
            'p.id as product_id','p.name as product_name','p.price as product_price', 'p.price_reseller as product_price_reseller', 'p.weight as product_weight', 'p.weight_type as product_weight_type', 'p.description as product_description', 'p.token as product_token',
            DB::raw('IFNULL(pi.token, "") as product_images'),
            DB::raw('IFNULL(uc.name, "") as company_name'),
            'p.type as product_type',
            'p.status as product_status',
            'p.created_at as product_date',
            DB::raw('IFNULL(u.name, "") as admin')
        )
        ->leftJoin('product_images as pi', function($join)
        {
            $join->on('pi.product_id', '=', 'p.id')
            ->where(['pi.status'=>1]);
        })
        ->leftJoin('user_companies as uc', function($join)
        {
            $join->on('uc.id', '=', 'p.company_id')
            ->where(['uc.status'=>1]);
        })
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'p.user_id');
        });
        if( $type != '-1' )
        {
            if( $compid == '-1')
            {
                $getdata = $getdata->where([
                    'p.type'        =>  $type
                ]);
            }
            else
            {
                $getdata = $getdata->where([
                    'p.company_id'        =>  $compid
                ]);
            }
        }
        if( $status != '-1')
        {
            $getdata = $getdata->where([
                'p.status'      =>  $status
            ]);
        }
        $getdata = $getdata->where($where);

        // count
        $cdata = $getdata->count();

        if( $cdata > 0 )
        {
            $vdata = $getdata->orderBy('p.type', 'asc')
            ->orderBy('p.name', $sort_name)
            ->take($Config->table(['paging'=>$paging])['paging_item'])
            ->skip($Config->table(['paging'=>$paging])['paging_limit'])
            ->get();

            foreach($vdata as $row)
            {
                $admin = $row->admin === "" ? "" : explode(' ', $row->admin);
                $list[] = [
                    'id'            =>  $row->product_id,
                    'token'         =>  $row->product_token,
                    'name'          =>  $row->product_name,
                    'wight'         =>  $row->product_weight,
                    'weight_type'   =>  $row->product_weight_type,
                    'price'         =>  $row->product_price,
                    'price_reseller'    =>  $row->product_price_reseller,
                    'description'       =>  $row->product_description,
                    'images'            =>  $Config->apps()['storage']['URL'] . ($row->product_images === '' ? '/images/none/no-product-image.png' : '/images/product/' . $row->product_images),
                    'type'          =>  $row->product_type,
                    'status'        =>  $row->product_status,
                    'stock'         =>  0,
                    'admin'         =>  $row->admin === "" ? "NULL" : $admin[0],
                    'date'          =>  $row->date === null ? "NULL" : $Config->timeago($row->product_date),
                ];
            }

            $data = [
                'message'   =>  '',
                'response' =>  [
                    'list'      =>  $list,
                    'paging'        =>  $paging,
                    'total'         =>  $cdata,
                    'countpage'     =>  ceil($cdata / $Config->table(['paging'=>$paging])['paging_item'] )
                ]
            ];


            return response()->json($data, 200);
        }
        else
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }

    }



}