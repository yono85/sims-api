<?php
namespace App\Http\Controllers\products;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\orders as tblOrders;
use App\products as tblData;
use App\order_items as tblOrderItems;
use App\product_stocks as tblProductStocks;
use App\user_companies as tblUserCompanies;
use App\product_prices as tblProductPrices;
use App\Http\Controllers\config\index as Config;
use App\Http\Controllers\access\manage as Refresh;
use DB;

class lists extends Controller
{
    //
    public function widget(Request $request)
    {
        if( $request->level == '2')
        {
            $product = $this->productWidgetDistributor($request);
        }
        else
        {
            $product = $this->productWidgetProdusen($request);
        }

        return $product;
    }


    // product list widget produsen
    public function productWidgetProdusen($request)
    {
        //
        $Config = new Config;

        $companyid = $request->companyid;
        $type = $request->type;
        $src = $request->src === null ? '' : $request->src;

        $cekcomp = tblUserCompanies::where([
            'id'            =>  $companyid
        ])->first();

        //
        $getlist = tblData::from('products as p')
        ->select(
            'p.id', 'p.name', 'p.description', 'p.price', 'p.price_discount', 'p.price_reseller', 'p.weight', 'p.weight_type',
            'pi.token as product_images'
        )
        ->where([
            ['p.type', '=', '1'],
            ['p.name', 'like', '%' . $request->src . '%'],
            // ['p.company_id', '=', $companyid],
            ['p.status', '=', 1]
        ]);
        if( $cekcomp->type == '3' )
        {
            $getlist = $getlist->where([
                'p.company_id'  =>  $companyid
            ]);
        }
        $getlist = $getlist->leftJoin('product_images as pi', function($join)
        {
            $join->on('pi.product_id', '=', 'p.id')
            ->where(['pi.status'=>1]);
        })
        ->get();


        if( count($getlist) > 0 )
        {

            //
            
            foreach($getlist as $row)
            {
                // cek price
                //distributor
                
                if( $cekcomp->type == '2')
                {
                    $getcekprice = tblProductPrices::select(
                        'price'
                    )
                    ->where([
                        'product_id'        =>  $row->id,
                        'company_id'        =>  $companyid
                    ])->first();

                    $price_reseller = $getcekprice === null ? $row->price_reseller : $getcekprice->price;
                }
                else
                {

                    $price_reseller = $row->price_reseller;
                }


                $images = $row->product_images === null ? 'none/no-product-image.png' : 'product/' . $row->product_images . '.jpg';

                $list[] = [
                    'id'                =>  $row->id,
                    'name'              =>  $row->name,
                    'image'             =>  $Config->apps()['storage']['URL'] .'/images/' .$images,
                    'description'       =>  $row->description,
                    'price'             =>  $row->price,
                    'price_discount'    =>  $row->price_discount,
                    'price_reseller'    =>  $price_reseller, //$row->price_reseller,
                    'weight'            =>  $row->weight,
                    'weight_type'       =>  $row->weight_type,
                    'price_show'        =>  $type === '1' || $type === '2' ? 'p' : 'r'
                ];
            }

            $response = [
                'list'      =>  $list
            ];

            $status = 200;
        }
        else
        {
        
            $status = 404;
        }

        $data = [
            'message'       =>  $status !== 200 ? 'Data tidak ditemukan' : '',
            'response'      =>  $status === 200 ? $response : '',
            'companyid'     =>  $companyid
        ];


        return response()->json($data, $status);
    }

    // product list widget distributor
    public function productWidgetDistributor($request)
    {
        //
        $Config = new Config;

        $companyid = $request->companyid;
        $type = $request->type;
        $src = $request->src === null ? '' : $request->src;

        $cekcomp = tblUserCompanies::where([
            'id'            =>  $companyid
        ])->first();

        $getlist = tblProductStocks::from('product_stocks as ps')
        ->select(
            'p.id', 'p.name', 'p.description', 'p.price', 'p.price_discount', 'p.price_reseller', 'p.weight', 'p.weight_type',
            'pi.token as product_images'
        )
        ->leftJoin('products as p', function($join)
        {
            $join->on('p.id', '=', 'ps.product_id');
        })
        ->leftJoin('product_images as pi', function($join)
        {
            $join->on('pi.product_id', '=', 'p.id')
            ->where(['pi.status'=>1]);
        })
        ->where([
            ['p.name', 'like', '%' . $src . '%'],
            ['ps.company_id', '=', $request->companyid],
            ['ps.status', '=', 1]
        ])->get();
        
        if( count($getlist) > 0 )
        {

            //
            
            foreach($getlist as $row)
            {
                // cek price
                //distributor
                
                if( $cekcomp->type == '2')
                {
                    $getcekprice = tblProductPrices::select(
                        'price'
                    )
                    ->where([
                        'product_id'        =>  $row->id,
                        'company_id'        =>  $companyid
                    ])->first();

                    $price_reseller = $getcekprice === null ? $row->price_reseller : $getcekprice->price;
                }
                else
                {

                    $price_reseller = $row->price_reseller;
                }


                $images = $row->product_images === null ? 'none/no-product-image.png' : 'product/' . $row->product_images . '.jpg';

                $list[] = [
                    'id'                =>  $row->id,
                    'name'              =>  $row->name,
                    'image'             =>  $Config->apps()['storage']['URL'] .'/images/' .$images,
                    'description'       =>  $row->description,
                    'price'             =>  $row->price,
                    'price_discount'    =>  $row->price_discount,
                    'price_reseller'    =>  $price_reseller, //$row->price_reseller,
                    'weight'            =>  $row->weight,
                    'weight_type'       =>  $row->weight_type,
                    'price_show'        =>  $type === '1' || $type === '2' ? 'p' : 'r'
                ];
            }

            $response = [
                'list'      =>  $list
            ];

            $status = 200;
        }
        else
        {
        
            $status = 404;
        }

        $data = [
            'message'       =>  $status !== 200 ? 'Data tidak ditemukan' : '',
            'response'      =>  $status === 200 ? $response : '',
            'companyid'     =>  $companyid
        ];


        return response()->json($data, $status);
    }

    // widget inmodal ====>
    public function widgetmodal(Request $request)
    {
        if( $request->level == '2')
        {
            $product = $this->wigetModalDistributor($request);
        }
        else
        {
            $product = $this->wigetModalProdusen($request);
        }

        return $product;
    }


    // widget modal produsen
    public function wigetModalProdusen($request)
    {

        //
        $Config = new Config;

        //
        $order_id = $request->id;

        $cekorders = tblOrders::from('orders as o')
        ->select(
            'o.id', 'o.type', 'o.company_id', 'uc.type as company_type'
        )
        ->leftJoin('user_companies as uc', function($join)
        {
            $join->on('uc.id', '=', 'o.company_id');
        })
        ->where([
            'o.id'        =>  $order_id
        ])->first();

        //cekorderitem
        $getoi = tblOrderItems::where([
            'order_id'      =>  $order_id,
            'status'        =>  1
        ])->get();

        foreach($getoi as $row)
        {
            $product_id[] = $row->product_id;
        }


        $getlist = tblData::from('products as p')
        ->select(
            'p.id', 'p.name', 'p.description', 'p.price', 'p.price_discount', 'p.price_reseller', 'p.weight', 'p.weight_type',
            'pi.id as product_images'
        )
        ->where([
            ['p.type', '=', 1],
            ['p.status', '=', 1],
        ])
        ->whereNotIn('p.id', $product_id)
        ->leftJoin('product_images as pi', function($join)
        {
            $join->on('pi.product_id', '=', 'p.id')
            ->where(['pi.status'=>1]);
        })
        ->get();


        if( count($getlist) > 0 )
        {
            $cek = 'ada';

            foreach($getlist as $row)
            {

                if( $cekorders->company_type == '2')
                {
                    $getcekprice = tblProductPrices::select(
                        'price'
                    )
                    ->where([
                        'product_id'        =>  $row->id,
                        'company_id'        =>  $cekorders->company_id
                    ])->first();

                    $price_reseller = $getcekprice === null ? $row->price_reseller : $getcekprice->price;
                }
                else
                {

                    $price_reseller = $row->price_reseller;
                }

                $images = $row->product_images === null ? 'none/no-product-image.png' : 'product/' . $row->product_images . '.jpg';

                $list[] = [
                    'id'                =>  $row->id,
                    'name'              =>  $row->name,
                    'image'             =>  $Config->apps()['storage']['URL'] .'/images/' .$images,
                    'description'       =>  $row->description,
                    'price'             =>  $row->price,
                    'price_discount'    =>  $row->price_discount,
                    'price_reseller'    =>  $price_reseller,
                    'weight'            =>  $row->weight,
                    'weight_type'       =>  $row->weight_type,
                    'price_show'        =>  $cekorders->type === 1 || $cekorders->type === 2 ? 'p' : 'r'
                ];
            }

            $response = [
                'list'      =>  $list
            ];

            $status = 200;
        }
        else
        {
            $cek = 'none';
            $status = 404;
        }

        $data = [
            'message'       =>  $status !== 200 ? 'Data tidak ditemukan' : '',
            'response'      =>  $status === 200 ? $response : ''
        ];


        return response()->json($data, $status);

        
    }

    // widget modal distributor
    public function wigetModalDistributor($request)
    {

        //
        $Config = new Config;

        //
        $order_id = $request->id;

        $cekorders = tblOrders::from('orders as o')
        ->select(
            'o.id', 'o.type', 'o.company_id', 'uc.type as company_type'
        )
        ->leftJoin('user_companies as uc', function($join)
        {
            $join->on('uc.id', '=', 'o.company_id');
        })
        ->where([
            'o.id'        =>  $order_id
        ])->first();

        //cekorderitem
        $getoi = tblOrderItems::where([
            'order_id'      =>  $order_id,
            'status'        =>  1
        ])->get();

        foreach($getoi as $row)
        {
            $product_id[] = $row->product_id;
        }


        $getlist = tblProductStocks::from('product_stocks as ps')
        ->select(
            'p.id', 'p.name', 'p.description', 'p.price', 'p.price_discount', 'p.price_reseller', 'p.weight', 'p.weight_type',
            'pi.id as product_images'
        )
        ->leftJoin('products as p', function($join)
        {
            $join->on('p.id', '=', 'ps.product_id')
            ->where(['p.status'=>1]);
        })
        ->leftJoin('product_images as pi', function($join)
        {
            $join->on('pi.product_id', '=', 'p.id')
            ->where(['pi.status'=>1]);
        })
        ->where([
            ['p.type', '=', 1],
            ['p.status', '=', 1],
        ])
        ->whereNotIn('p.id', $product_id)
        ->get();


        if( count($getlist) > 0 )
        {
            
            foreach($getlist as $row)
            {

                if( $cekorders->company_type == '2')
                {
                    $getcekprice = tblProductPrices::select(
                        'price'
                    )
                    ->where([
                        'product_id'        =>  $row->id,
                        'company_id'        =>  $cekorders->company_id
                    ])->first();

                    $price_reseller = $getcekprice === null ? $row->price_reseller : $getcekprice->price;
                }
                else
                {

                    $price_reseller = $row->price_reseller;
                }

                $images = $row->product_images === null ? 'none/no-product-image.png' : 'product/' . $row->product_images . '.jpg';

                $list[] = [
                    'id'                =>  $row->id,
                    'name'              =>  $row->name,
                    'image'             =>  $Config->apps()['storage']['URL'] .'/images/' .$images,
                    'description'       =>  $row->description,
                    'price'             =>  $row->price,
                    'price_discount'    =>  $row->price_discount,
                    'price_reseller'    =>  $price_reseller,
                    'weight'            =>  $row->weight,
                    'weight_type'       =>  $row->weight_type,
                    'price_show'        =>  $cekorders->type === 1 || $cekorders->type === 2 ? 'p' : 'r'
                ];
            }

            $response = [
                'list'      =>  $list
            ];

            $status = 200;
        }
        else
        {
            $cek = 'none';
            $status = 404;
        }

        $data = [
            'message'       =>  $status !== 200 ? 'Data tidak ditemukan' : '',
            'response'      =>  $status === 200 ? $response : ''
        ];


        return response()->json($data, $status);

        
    }


    public function view(Request $request)
    {

        //
        $Config = new Config;

        $type = $request->type;


        $getlist = tblData::from('products as p')
        ->select(
            'p.id', 'p.name', 'p.description', 'p.price', 'p.price_discount', 'p.price_reseller', 'p.weight', 'p.weight_type',
            'pi.id as product_images'
        )
        ->where([
            ['p.status', '=', 1],
        ]);
        if( $type != '-1')
        {
            $getlist = $getlist->where([
                'p.type'        =>  $type
            ]);
        }
        $getlist = $getlist->leftJoin('product_images as pi', function($join)
        {
            $join->on('pi.product_id', '=', 'p.id')
            ->where(['pi.status'=>1]);
        })
        ->get();


        if( count($getlist) > 0 )
        {
            $cek = 'ada';

            foreach($getlist as $row)
            {
                $images = $row->product_images === null ? 'none/no-product-image.png' : 'product/' . $row->product_images . '.jpg';

                $list[] = [
                    'id'                =>  $row->id,
                    'name'              =>  $row->name,
                    'image'             =>  $Config->apps()['storage']['URL'] .'/images/' .$images,
                    'description'       =>  $row->description,
                    'price'             =>  $row->price,
                    'price_discount'    =>  $row->price_discount,
                    'price_reseller'    =>  $row->price_reseller,
                    'weight'            =>  $row->weight,
                    'weight_type'       =>  $row->weight_type
                ];
            }

            $response = [
                'list'      =>  $list
            ];

            $status = 200;
        }
        else
        {
            $cek = 'none';
            $status = 404;
        }

        $data = [
            'message'       =>  $status !== 200 ? 'Data tidak ditemukan' : '',
            'response'      =>  $status === 200 ? $response : ''
        ];


        return response()->json($data, $status);
    }

    public function distributor(Request $request)
    {
        $Config = new Config;

        $getproduct = tblData::from('products as p')
        ->select(
            'p.id', 'p.name', 'p.price',
            DB::raw('IFNULL(pi.token, "") as product_images')
        )
        ->leftJoin('product_images as pi', function($join)
        {
            $join->on('pi.product_id', '=', 'p.id')
            ->where(['pi.status'=>1]);
        })
        ->where([
            'company_id'        =>  trim($request->id)
        ])
        ->orderBy('id', 'asc')
        ->get();

        if ( count($getproduct) > 0 )
        {


            foreach($getproduct as $row)
            {
                //
                $images = $row->product_images === "" ? '/images/none/no-product-image.png' : '/images/product/' . $row->product_images . '.jpg';

                $list[] = [
                    'id'        =>  $row->id,
                    'name'      =>  $row->name,
                    'price'     =>  $row->price,
                    'images'    =>  $Config->apps()["URL"]["STORAGE"] . $images
                ];
            }


            return response()->json($list, 200);
        }

        return response()->json(['message'=>'Data tidak ditemukan'], 404);
    }



    public function viewDistributor(Request $request)
    {
        $Config = new Config;

        $getlist = tblProductStocks::from('product_stocks as ps')
        ->select(
            'p.id', 'p.name', 'p.description', 'p.price', 'p.price_discount', 'p.price_reseller', 'p.weight', 'p.weight_type',
            'pi.id as product_images'
        )
        ->leftJoin('products as p', function($join)
        {
            $join->on('p.id', '=', 'ps.product_id');
        })
        ->leftJoin('product_images as pi', function($join)
        {
            $join->on('pi.product_id', '=', 'p.id')
            ->where(['pi.status'=>1]);
        })
        ->where([
            ['ps.company_id', '=', $request->companyid],
            ['p.status', '=', 1],
            ['ps.status', '=', 1],
        ])
        ->get();


        if( count($getlist) > 0 )
        {
            $cek = 'ada';

            foreach($getlist as $row)
            {
                $images = $row->product_images === null ? 'none/no-product-image.png' : 'product/' . $row->product_images . '.jpg';

                $list[] = [
                    'id'                =>  $row->id,
                    'name'              =>  $row->name,
                    'image'             =>  $Config->apps()['storage']['URL'] .'/images/' .$images,
                    'description'       =>  $row->description,
                    'price'             =>  $row->price,
                    'price_discount'    =>  $row->price_discount,
                    'price_reseller'    =>  $row->price_reseller,
                    'weight'            =>  $row->weight,
                    'weight_type'       =>  $row->weight_type
                ];
            }

            $response = [
                'list'      =>  $list
            ];

            $status = 200;
        }
        else
        {
            $cek = 'none';
            $status = 404;
        }

        $data = [
            'message'       =>  $status !== 200 ? 'Data tidak ditemukan' : '',
            'response'      =>  $status === 200 ? $response : ''
        ];


        return response()->json($data, $status);
    }

    // public function list(Request $request)
    // {

    //     //
    //     $Config = new Config;


    //     $type = $request->type;


    //     //
    //     $getlist = tblData::from('products as p')
    //     ->select(
    //         'p.id', 'p.name', 'p.description', 'p.price', 'p.price_discount', 'p.price_reseller', 'p.weight', 'p.weight_type',
    //         'pi.id as product_images'
    //     )
    //     ->leftJoin('product_images as pi', function($join)
    //     {
    //         $join->on('pi.product_id', '=', 'p.id')
    //         ->where(['pi.status'=>1]);
    //     })
    //     ->where([
    //         ['p.status', '=', 1]
    //     ])->get();


    //     if( count($getlist) > 0 )
    //     {


    //         //
            
    //         foreach($getlist as $row)
    //         {
    //             // cek price
    //             //distributor
                
    //             if( $cekcomp->type == '2')
    //             {
    //                 $getcekprice = tblProductPrices::select(
    //                     'price'
    //                 )
    //                 ->where([
    //                     'product_id'        =>  $row->id,
    //                     'company_id'        =>  $companyid
    //                 ])->first();

    //                 $price_reseller = $getcekprice === null ? $row->price_reseller : $getcekprice->price;
    //             }
    //             else
    //             {

    //                 $price_reseller = $row->price_reseller;
    //             }


    //             $images = $row->product_images === null ? 'none/no-product-image.png' : 'product/' . $row->product_images . '.jpg';

    //             $list[] = [
    //                 'id'                =>  $row->id,
    //                 'name'              =>  $row->name,
    //                 'image'             =>  $Config->apps()['storage']['URL'] .'/images/' .$images,
    //                 'description'       =>  $row->description,
    //                 'price'             =>  $row->price,
    //                 'price_discount'    =>  $row->price_discount,
    //                 'price_reseller'    =>  $row->price_reseller,
    //                 'weight'            =>  $row->weight,
    //                 'weight_type'       =>  $row->weight_type,
    //                 'price_show'        =>  $type === '1' || $type === '2' ? 'p' : 'r'
    //             ];
    //         }

    //         $response = [
    //             'list'      =>  $list
    //         ];

    //         $status = 200;
    //     }
    //     else
    //     {
        
    //         $status = 404;
    //     }

    //     $data = [
    //         'message'       =>  $status !== 200 ? 'Data tidak ditemukan' : '',
    //         'response'      =>  $status === 200 ? $response : '',
    //         'companyid'     =>  $companyid
    //     ];


    //     return response()->json($data, $status);
    // }

}