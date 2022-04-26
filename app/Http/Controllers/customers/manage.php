<?php
namespace App\Http\Controllers\customers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\customers as tblCustomers;
use DB;

class manage extends Controller
{
    //
    public function create(Request $request)
    {

        $Config = new Config;

        // IF NEW CREATE
        if( trim($request->type) == 'new')
        {
            //check
            $check = tblCustomers::where([
                ['name', 'like', '%' . trim($request->name) . '%' ],
                ['type', '=', trim($request->type)],
                ['status', '=', 1]
            ])->count();

            //reject insert
            if( $check > 0)
            {
                $data = [
                    'message'       =>  'Nama Perusahaan sudah ada sebelumnya',
                    'focus'         =>  'name'
                ];

                return response()->json($data, 403);
            }


            //insert
            $addNew = new \App\Http\Controllers\models\customers;
            $addNew = $addNew->main($request);

            if( trim($request->file('file')) != '')
            {
                $dataupload = [
                    'user_id'       =>  trim($request->user_id),
                    'link_id'       =>  $addNew['id'],
                    'file'          =>  $request->file('file'),
                    'type'          =>  2,  //customer
                    'subtype'       =>  0,
                    'url'           =>  '/upload/documents/customer/',
                    'path'          =>  '/documents/customer/'
                ];

                $upload = new \App\Http\Controllers\models\upload;
                $upload = $upload->main($dataupload);
            }

            $data = [
                'message'           =>  'Data Customer berhasil disimpan'
            ];

            return response()->json($data, 200);

        }
        

        //SUNTING
        $update = new \App\Http\Controllers\models\customers;
        $update = $update->update($request);

        if( trim($request->file('file')) != '')
        {
            $dataupload = [
                'user_id'       =>  trim($request->user_id),
                'link_id'       =>  trim($request->customer_id),
                'file'          =>  $request->file('file'),
                'type'          =>  2,  //customer
                'subtype'       =>  0,
                'url'           =>  '/upload/documents/customer/',
                'path'          =>  '/documents/customer/'
            ];

            $upload = new \App\Http\Controllers\models\upload;
            $upload = $upload->main($dataupload);
        }
        
        $data = [
            'message'       =>  'Data Customer berhasil disunting'
        ];


        return response()->json($data, 200);
    }


    //show
    public function show(Request $request)
    {
        $Config = new Config;

        $getdata = tblCustomers::from('customers as c')
        ->select(
            'c.id', 'c.name','c.type', 'c.owner', 'c.phone', 'c.email', 'c.address', 'c.kodepos',
            'c.provinsi', 'c.city', 'c.kecamatan',
            'ct.alias as type_name',
            DB::raw('IFNULL(ud.file, "") as file')
        )
        ->leftJoin('customer_types as ct', function($join)
        {
            $join->on('ct.id', '=', 'c.type');
        })
        ->leftJoin('upload_documents as ud', function($join)
        {
            $join->on('ud.link_id', '=', 'c.id')
            ->where([
                'ud.type'       =>  2,
                'ud.status'     =>  1
            ]);
        })
        ->where([
            'c.id'        =>  trim($request->id)
        ])->first();


        //bps
        $bps = new \App\Http\Controllers\data\bps;

        //list city
        $listcity = $getdata->provinsi === 0 ? '' : $bps->citydata($getdata->provinsi);

        //list kecamatan
        $listkecamatan = $getdata->city === 0 ? '' : $bps->kecamatandata($getdata->city);
        

        $data = [
            'message'       =>  '',
            'response'       =>  [
                'id'                =>  $getdata->id,
                'name'              =>  $getdata->name,
                'type'              =>  $getdata->type,
                'address'           =>  $getdata->address,
                'kodepos'           =>  $getdata->kodepos,
                'owner'             =>  $getdata->owner,
                'phone'             =>  $getdata->phone,
                'email'             =>  $getdata->email,
                'provinsi'          =>  $getdata->provinsi === 0 ? '-1' : $getdata->provinsi,
                'city'              =>  $getdata->city === 0 ? '-1' : $getdata->city,
                'kecamatan'         =>  $getdata->kecamatan === 0 ? '-1' : $getdata->kecamatan,
                'listcity'          =>  $listcity,
                'listkecamatan'     =>  $listkecamatan,
                'file'              =>  $getdata->file
            ]
        ];

        return response()->json($data, 200);
    }
}