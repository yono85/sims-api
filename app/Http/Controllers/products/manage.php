<?php
namespace App\Http\Controllers\products;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\products as tblProducts;
use App\product_image as tblProductImages;
use App\Http\Controllers\config\index as Config;

class manage extends Controller
{
    
    //
    public function create(Request $request)
    {

        if( $request->type == 'new')
        {
            $product = $this->new($request);
        }
        else
        {
            $product = $this->edit($request);
        }


        return $product;
    }

    public function new($request)
    {

        $Config = new Config;

        $account = new \App\Http\Controllers\account\index;
        $account = $account->viewtype([
            'type'          =>  'key',
            'token'         =>  $request->header('key')
        ]);

        // CEK SAME NAME
        $cek = tblProducts::where([
            'name'          =>  ltrim(trim($request->name)),
            'status'        =>  1
        ])->count();

        if( $cek > 0)
        {
            return response()->json([
                'focus'     =>  'name',
                'message'   =>'Nama produk yang sama telah ada sebelumnya'
            ], 401);
        }

        //
        $newid = $Config->createnewidnew([
            'value'     =>  tblProducts::count(),
            'length'    =>  7
        ]);


        $newadd                     =   new tblProducts;
        $newadd->id                 =   $newid;
        $newadd->type               =   trim($request->maklonID) === "-1" ? 1 : 2;
        $newadd->token              =   md5($newid);
        $newadd->name               =   trim($request->name);
        $newadd->description        =   trim($request->description);
        $newadd->price              =   trim($request->price);
        $newadd->price_discount     =   0;
        $newadd->discount           =   0;
        $newadd->price_reseller     =   trim($request->priceDistributor);
        $newadd->price_maklon       =   trim($request->priceMaklon);
        $newadd->weight             =   trim($request->weight);
        $newadd->weight_type        =   trim($request->weightType);
        $newadd->max                =   1000;
        $newadd->user_id            =   $account['id'];
        $newadd->company_id         =   trim($request->maklonID) === "-1" ? $account['config']['company_id'] : trim($request->maklonID);
        $newadd->status             =   1;
        $newadd->save();


        if( trim($request->file('image')) != '')
        {
            $newidImg = $Config->createnewidnew([
                'value'     =>  tblProductImages::count(),
                'length'    =>  15
            ]);

            $newaddImg              =   new tblProductImages;
            $newaddImg->id          =   $newidImg;
            $newaddImg->token       =   md5($newidImg) . '.jpg';
            $newaddImg->product_id  =   $newid;
            $newaddImg->user_id     =   $account['id'];
            $newaddImg->status      =   1;
            $newaddImg->save();

            $dataupload = [
                'name'          =>  md5($newidImg),
                'file'          =>  trim($request->file('image')),
                'path'          =>  'images/product/',
                "URL"           =>  $Config->apps()["URL"]["STORAGE"] . "/s3/upload/transfer"
            ];
    
            $upload = new \App\Http\Controllers\tdparty\s3\herbindo;
            $upload = $upload->transfer($dataupload);
        }

        $data = [
            'message'   =>  'Data berhasil disimpan'
        ];

        return response()->json($data, 200);

        //
        // return response()->json(['message'=>'Data gagal disimpan'], 401);
    }

    public function edit($request)
    {

        $Config = new Config;
        $account = new \App\Http\Controllers\account\index;
        $account = $account->viewtype([
            'type'          =>  'key',
            'token'         =>  $request->header('key')
        ]);

        //
        $productID = trim($request->productID);
        $name = ltrim(trim($request->name));
        $maklonID = trim($request->maklonID);
        $image = trim($request->image);
        $weight = trim($request->weight);
        $weightType = trim($request->weightType);
        $price = trim($request->price);
        $priceDistributor = trim($request->priceDistributor);
        $priceMaklon = trim($request->priceMaklon);
        $description = trim($request->description);

        $cek = tblProducts::where([
            ['id', '!=', $productID],
            ['name', '=', $name],
            ['status', '=', 1]
        ])->count();

        if( $cek > 0)
        {
            return response()->json([
                'focus'     =>  'name',
                'message'   =>'Nama produk yang sama telah ada sebelumnya'
            ], 401);
        }

        $getpd = tblProducts::where([
            'id'        =>  $productID
        ])->first();

        $upPd = tblProducts::where([
            'id'            =>  $productID
        ])
        ->update([
            'type'          =>  $maklonID === '-1' ? 1 : 2,
            'name'          =>  $name,
            'description'   =>  $description,
            'price'         =>  $price,
            'price_reseller'    =>  $priceDistributor,
            'price_maklon'      =>  $priceMaklon,
            'weight'            =>  $weight,
            'weight_type'       =>  $weightType,
            'company_id'        =>  $maklonID === '-1' ? $getpd->company_id : $maklonID
        ]);

        //upload image
        if($image != "")
        {
            //update product images
            $chimg = tblProductImages::where([
                'product_id'        =>  $productID,
                'status'            =>  1
            ])
            ->update([
                'status'        =>  0
            ]);

            $newidImg = $Config->createnewidnew([
                'value'     =>  tblProductImages::count(),
                'length'    =>  15
            ]);

            $newaddImg              =   new tblProductImages;
            $newaddImg->id          =   $newidImg;
            $newaddImg->token       =   md5($newidImg) . '.jpg';
            $newaddImg->product_id  =   $productID;
            $newaddImg->user_id     =   $account['id'];
            $newaddImg->status      =   1;
            $newaddImg->save();

            $dataupload = [
                'name'          =>  md5($newidImg),
                'file'          =>  trim($request->file('image')),
                'path'          =>  'images/product/',
                "URL"           =>  $Config->apps()["URL"]["STORAGE"] . "/s3/upload/transfer"
            ];
    
            $upload = new \App\Http\Controllers\tdparty\s3\herbindo;
            $upload = $upload->transfer($dataupload);
        }

        return response()->json([
            'message'       =>  'Produk berhasil disunting'
        ], 200);
    }

    public function detail(Request $request)
    {
        $Config = new Config;


        $getproduct = tblProducts::from('products as p')
        ->select(
            'p.id', 'p.name', 'p.type', 'p.company_id',
            'p.weight', 'p.weight_type', 'p.price', 'p.price_reseller', 'p.price_maklon', 'p.description',
            'pi.token as images',
            'uc.name as company_name'
        )
        ->leftJoin('product_images as pi', function($join)
        {
            $join->on('pi.product_id', '=', 'p.id')
            ->where(['pi.status'=>1]);
        })
        ->leftJoin('user_companies as uc', function($join)
        {
            $join->on('uc.id', '=', 'p.company_id');
        })
        ->where([
            'p.id'            =>  trim($request->id)
        ])->first();


        $data = [
            'message'   =>  '',
            'response'  =>  [
                'id'            =>  $getproduct->id,
                'type'          =>  $getproduct->type,
                'companyID'     =>  $getproduct->company_id,
                'name'          =>  $getproduct->name,
                'weight'        =>  $getproduct->weight,
                'weightType'    =>  $getproduct->weight_type,
                'price'         =>  $getproduct->price,
                'priceDistributor'  =>  $getproduct->price_reseller,
                'priceMaklon'       =>  $getproduct->price_maklon,
                'images'            =>  $Config->apps()['storage']['URL'] . ($getproduct->images === null ? '/images/none/no-product-image.png' : '/images/product/' . $getproduct->images),
                'imageStatus'       =>  $getproduct->images === null ? 'false' : 'true',
                'description'       =>  $getproduct->description,
                'companyName'       =>  $getproduct->company_name
            ]
        ];

        return response()->json($data, 200);
    }
}