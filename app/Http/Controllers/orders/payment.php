<?php
namespace App\Http\Controllers\orders;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\orders as tblOrders;
use App\app_bank_lists as tblBanks;
use App\app_metode_payments as tblMetodePayments;
use App\users as tblUsers;
use DB;

class payment extends Controller
{

    //
    public function banklist(Request $request)
    {
        
        $list = $this->viewbanklist();

        $data = [
            'message'           =>  '',
            'response'          =>  $list
        ];

        return response()->json($data, 200);
    }


    //
    public function viewbanklist()
    {
        $Config = new Config;

        //
        $banklist = tblBanks::where([
            'status'            =>  1
        ])
        ->get();

        //
        foreach($banklist as $row)
        {
            $images = $row->image === '' ? 'none/no-product-image.png' : 'bank/' . $row->image;

            $list[] = [
                'id'            =>  $row->id,
                'name'          =>  $row->name,
                'label'         =>  $row->label,
                'kode'          =>  $row->kode,
                'image'         =>  $Config->apps()['storage']['URL'] .'/images/' .$images,
            ];

        }

        return $list;
    }

    //
    public function metode(Request $request)
    {

        //
        $Config = new Config;
        
        $token = $request->header('key');

        //
        $account = new \App\Http\Controllers\account\index;
        $account = $account->viewtype([
            'type'      =>  'key',
            'token'     =>   $request->header('key')
        ]);

        //cek orders
        $cekorders = tblOrders::from('orders as o')
        ->where([
            'o.id'          =>  $request->id
        ])
        ->first();


        //
        $getdata = tblMetodePayments::where([
            // 'company_id'    =>  $account['config']['company_id'],
            'type'          =>  1,
            'status'        =>  1
        ]);
        if( $cekorders->type == '3')
        {

            $getdata = $getdata->where([
                'company_id'        =>  $account['config']['produsen_id']
            ]);
        }
        if( $cekorders->type == '1' || $cekorders->type == '2' || $cekorders->type == '4')
        {
            $getdata = $getdata->where([
                'company_id'        =>  $account['config']['company_id']
            ]);

            if( trim($request->cod) === 'yes' )
            {
                $getcod = tblMetodePayments::where([
                    'bank_id'       =>  trim($request->courier),
                    'type'          =>  2,
                    'status'        =>  1
                ])->first();

                $list[] = [
                    'id'        =>  $getcod->id,
                    'type'      =>  $getcod->type,
                    'name'      =>  $getcod->name,
                    'account_name'  =>  '',
                    'account_norek' =>  '',
                    'label'     =>  '',
                    'images'    =>  $Config->apps()['storage']['URL'] . '/images/bank/cod.png'
                ];
            }
        }
        $getdata = $getdata->get();

        if( count($getdata) < 1 )
        {
            $data = [
                'message'           =>  'Data tidak ditemukan'
            ];
    
            return response()->json($data, 404);
        }
        //
        foreach($getdata as $row)
        {

            if( $row->type == '1')
            {
                $getbank = tblBanks::where([
                    'id'        =>  $row->bank_id
                ])->first();

            }

            //
            $list[] = [
                'id'        =>  $row->id,
                'type'      =>  $row->type,
                'name'      =>  $row->type === 1 ? 'Bank ' . $getbank->label : $row->name,
                'account_name'  =>  $row->type === 1 ? $row->account_name : '',
                'account_norek' =>  $row->type === 1 ? $row->account_norek : '',
                'label'     =>  $row->type === 1 ? ($row->account_name . ' - ' . $row->account_norek) : '',
                'images'    =>  $Config->apps()['storage']['URL'] . '/images/' . ($row->type === 1 ? ('bank/' . $getbank->image) : 'bank/cod.png' )
            ];
        }


        //
        $data = [
            'message'           =>  '',
            'list'              =>  $list
        ];


        return response()->json($data, 200);


    }
}