<?php
namespace App\Http\Controllers\shiping;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\order_shipings as tblOrderShipings;
use App\orders as tblOrders;

class manage extends Controller
{
    //
    public function addnoresi(Request $request)
    {
        // requset 
        $noresi = trim($request->noresi);
        $id = trim($request->id);

        //account
        $account = new \App\Http\Controllers\account\index;
        $account = $account->viewtype([
            'type'          =>  'key',
            'token'         =>  $request->header('key')
        ]);


        $ceking = tblOrderShipings::where([
            ['id', '!=', $id],
            ['noresi', '=', $noresi],
            ['status', '=', 1]
        ])
        ->count();

        if( $ceking > 0 )
        {
            $data = [
                'message'       =>  'Nomor Resi telah digunakan!'
            ];

            return response()->json($data, 401);
        }

        $cek = tblOrderShipings::where([
            'id'            =>  $id,
            'status'        =>  1
        ])
        ->first();

        

        if( $cek->noresi == '' )
        {

            $upos = tblOrderShipings::where([
                'id'            =>  $id
            ])
            ->update([
                'noresi'        =>  $noresi
            ]);

            // insert table notif auto sender

            //add notif
            $datanotif = [
                "account"           =>  $account,
                "order_id"          =>  $cek->order_id
            ];

            $addnotif = new \App\Http\Controllers\notification\index;
            $addnotif = $addnotif->notifFromShiping($datanotif);

            $message = 'Nomor Resi berhasil disimpan';
        }
        else
        {
            

            if( $noresi != $cek->noresi)
            {
                //
                $upos = tblOrderShipings::where([
                    'id'            =>  $id
                ])
                ->update([
                    'noresi'        =>  $noresi
                ]);

                $message = 'Nomor Resi berhasil di update';
            }
            else {
                $message = '';
            }
        }

        $updatefield = new \App\Http\Controllers\orders\manage;
        $updatefield = $updatefield->updatefield([
            'order_id'      =>  $cek->order_id
        ]);

        $data = [
            'message'           =>  $message
        ];

        return response()->json($data, 200);
    }

    public function pickup(Request $request)
    {
        //
        $id = trim($request->id);


        //account
        $account = new \App\Http\Controllers\account\index;
        $account = $account->viewtype([
            'type'          =>  'key',
            'token'         =>  $request->header('key')
        ]);


        // cek order
        $cekos = tblOrderShipings::from('order_shipings as os')
        ->select(
            'os.order_id', 'os.pickup_status', 'os.courier_name', 'acl.pickup', 'acc.dir'
        )
        ->leftJoin('app_courier_lists as acl', function($join)
        {
            $join->on('acl.id', '=', 'os.courier_id');
        })
        ->leftJoin('app_courier_configs as acc', function($join)
        {
            $join->on('acc.id', '=', 'acl.config_id');
        })
        ->where([
            'os.id'        =>  $id
        ])
        ->first();


        //jika status pickup 1
        if( $cekos->pickup_status == 1 )
        {
            $data =[
                'message'       =>  'Proses pickup telah diproses sebalumnya'
            ];

            return response()->json($data, 500);
        }

        // jika function pickup belum tersedia
        if( $cekos->pickup == 0 )
        {
            $data =[
                'message'       =>  'Fitur pickup untuk kurir '. $cekos->courier_name.' belum tersedia'
            ];

            return response()->json($data, 404);
        }

        //root trd party
        $root = '\App\Http\Controllers\tdparty\courier\/' . $cekos->dir . '\index';
        $root = str_replace('/', '', $root);
        $root = new $root;

        //cek jika local
        if( env('APP_ENV') == 'local' )
        {
            $root = $root->TestPickup($cekos->order_id);
        }
        else
        {
            $root = $root->pickup($cekos->order_id);
            
        }

        if( $root['status'] != '200') //jika error saat proses
        {
            $data =[
                'message'       =>  $root['message'],
                'status'        =>  $root['status']
            ];

            return response()->json($data, 500);
        }
        else
        {
            $upshiping = tblOrderShipings::where([
                'id'        =>  $id
            ])
            ->update([
                'pickup_status'     =>  1,
                'noresi'            =>  $root['response']->awb_no
            ]);

            $updatefield = new \App\Http\Controllers\orders\manage;
            $updatefield = $updatefield->updatefield([
                'order_id'      =>  $cekos->order_id
            ]);

            //notification
            $datanotif = [
                "account"           =>  $account,
                "order_id"          =>  $cekos->order_id
            ];

            $addnotif = new \App\Http\Controllers\notification\index;
            $addnotif = $addnotif->notifFromShiping($datanotif);

            $data = [
                'message'       =>  'Pickup berhasil di proses',
                'response'      =>  $root['response']->awb_no
            ];
    
            return response()->json($data,200);
        }
        
    }

}