<?php
namespace App\Http\Controllers\notification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\app_notifications as tblAppNotifications;
use App\orders as tblOrders;
use App\user_companies as tablUserCompanies;

class index extends Controller
{
    //
    public function add($request)
    {

        $Config = new Config;

        //
        $newid = $Config->createnewidnew([
            'length'        =>  15,
            'value'         =>  tblAppNotifications::count()
        ]);

        $addnotif               =   new tblAppNotifications;
        $addnotif->id           =   $newid;
        $addnotif->level        =   $request["level"];
        $addnotif->type         =   $request["type"];
        $addnotif->subtype      =   $request["subtype"];
        $addnotif->to_type      =   $request["to_type"];
        $addnotif->to_id        =   $request["to_id"];
        $addnotif->to_companyid =   $request["to_companyid"];
        $addnotif->from_id      =   $request["from_id"];
        $addnotif->from_companyid = $request["from_companyid"];
        $addnotif->content          =   json_encode($request["content"]);
        $addnotif->open             =   0;
        $addnotif->open_date        =   '';
        $addnotif->read_date        =   '';
        $addnotif->status           =   1;
        $addnotif->save();
    }


    // permintaan untuk di verifikasi
    public function verifPayment($request)
    {

        //
        $Config = new Config;

        //
        $getorder = tblOrders::where([
            "id"            =>  $request["order_id"],
            "status"        =>  1
        ])->first();

        //
        $content = [
            "title"         =>  "Verifikasi Pembayaran",
            "body"          =>  "Segera verifikasi pembayaran untuk order dengan nomor invoice " . $getorder->invoice,
            "link"          =>  "/dashboard/veriforders?q=" . $getorder->invoice
        ];

        //
        $datanotif = [
            "level"         =>  $getorder->type > 0 ? 1 : 0,
            "type"          =>  1,
            "subtype"       =>  1, //verifikasi pembayaran
            "to_type"       =>  5,
            "to_id"         =>  0,
            "to_companyid"  =>  $request["account"]["config"]["company_id"],
            "from_id"       =>  $request["account"]["id"],
            "from_companyid"    =>  $request["account"]["config"]["company_id"],
            "content"           =>  $content
        ];
        
        $addnotif = $this->add($datanotif);
    }


    //verifikasi success
    public function verifSuccess($request)
    {

        //
        $getorder = tblOrders::where([
            "id"            =>  $request["order_id"],
            "status"        =>  1
        ])->first();

        // notif for cs
        $toCS = $this->verifSuccessToCS([
            "account"       =>  $request["account"],
            "orders"        =>  $getorder
        ]);

        // notif for shiping
        $toShiping = $this->verifSuccessToShiping([
            "account"       =>  $request["account"],
            "orders"        =>  $getorder
        ]);


       
    }

    protected function verifSuccessToCS($request)
    {

        $getorder = $request["orders"];
        $account = $request["account"];
        
        //
        $content = [
           "title"         =>  "Pembayaran sudah terverifikasi",
           "body"          =>  "Pembayaran dengan nomor invoice " . $getorder->invoice . " berhasil diverifikasi",
           "link"          =>  "/dashboard/orders?q=" . $getorder->invoice
       ];

        //
        $datanotif = [
            "level"         =>  $getorder->type > 0 ? 1 : 0, // end user or CS
            "type"          =>  1, // order
            "subtype"       =>  2, //success verification
            "to_type"       =>  0,
            "to_id"         =>  $getorder->user_id,
            "to_companyid"  =>  $getorder->type > 0 ? $getorder->company_id : 0,
            "from_id"       =>  $request["account"]["id"],
            "from_companyid"    =>  $request["account"]["config"]["company_id"],
            "content"           =>  $content
        ];

        $add = $this->add($datanotif);

    }


    protected function verifSuccessToShiping($request)
    {

        $getorder = $request["orders"];
        $account = $request["account"];
        
        //
        $content = [
           "title"         =>  "Verifikasi Pengiriman",
           "body"          =>  "Segera lakukan verifikasi pengiriman untuk orderan dengan nomor invoice " . $getorder->invoice,
           "link"          =>  "/dashboard/shiping?q=" . $getorder->invoice
       ];

        if($getorder->type == 3 || $getorder->type == 4)
        {

            $getcomp = tablUserCompanies::where([
                "id"            =>  $getorder->company_id
            ])->first();

            $tocompanyid = $getcomp->produsen_id;
        }
        else
        {
            $tocompanyid = $getorder->type === 0 ? 0 : $getorder->company_id;

        }

        //
        $datanotif = [
            "level"         =>  $getorder->type > 0 ? 1 : 0, // end user or CS
            "type"          =>  1, // order
            "subtype"       =>  3, //verifikasi shiping
            "to_type"       =>  6, // admin shiping
            "to_id"         =>  0,
            "to_companyid"  =>  $tocompanyid,
            "from_id"       =>  $request["account"]["id"],
            "from_companyid"    =>  $request["account"]["config"]["company_id"],
            "content"           =>  $content
        ];

        $add = $this->add($datanotif);
    }



    public function notifFromShiping($request)
    {


        //
        $getorder = tblOrders::where([
            "id"            =>  $request["order_id"],
            "status"        =>  1
        ])->first();


        //
        $content = [
           "title"         =>  "Orderan sedang dikirim",
           "body"          =>  "Orderan dengan nomor inovice " . $getorder->invoice . " sedang dikirim",
           "link"          =>  "/dashboard/orders?q=" . $getorder->invoice
       ];

        //
        $datanotif = [
            "level"         =>  $getorder->type > 0 ? 1 : 0, // end user or CS
            "type"          =>  1, // order
            "subtype"       =>  4, // Success Shiping
            "to_type"       =>  0, // admin shiping
            "to_id"         =>  $getorder->user_id,
            "to_companyid"  =>  $getorder->type === 0 ? 0 : $getorder->company_id,
            "from_id"       =>  $request["account"]["id"],
            "from_companyid"    =>  $request["account"]["config"]["company_id"],
            "content"           =>  $content
        ];

        $add = $this->add($datanotif);
    }


    public function test(Request $request)
    {

        $Config = new Config;

        //
        $type = 1;
        $subtype = 1;
        $totype = 5;
        $toid = 0;
        $tocompid = 0;
        $fromid = 0;
        $fromcompid = 0;


        $newid = $Config->createnewidnew([
            'length'        =>  15,
            'value'         =>  tblAppNotifications::count()
        ]);

        $title = $subtype === 1 ? 'Verifikasi Pembayaran' : ( $subtype === 2 ? 'Pembayaran Berhasil' : ($subtype === 3 ? 'Verifikasi Shiping' : 'Sedang dikirim') );

        $body = $subtype === 1 ? 'Orderan dengan Invoice {{invoice}} menunggu verifikasi' : ( $subtype === 2 ? 'Pembayaran untuk order {{invoice}} berhasil' : ($subtype === 3 ? 'Verifikasi Shiping' : 'Sedang dikirim') );
        
        $content = [
            'title'         =>  $title,
            'body'          =>  'body'
        ];



        $addnotif               =   new tblAppNotifications;
        $addnotif->id           =   $newid;
        $addnotif->level        =   $type > 0 ? 1 : 0;
        $addnotif->type         =   $type;
        $addnotif->subtype      =   $subtype;
        $addnotif->to_type      =   $totype;
        $addnotif->to_id        =   $toid;
        $addnotif->to_companyid =   $tocompid;
        $addnotif->from_id      =   $fromid;
        $addnotif->from_companyid = $fromcompid;
        $addnotif->content          =   json_encode($content);
        $addnotif->read_date        =   '';
        $addnotif->status           =   1;
        $addnotif->save();


        return response()->json($addnotif, 200);
    }


    public function q($request)
    {


        //1 level adalah jenis notifikasi order personal atau order admin/cs
        // 2 type adalah type notifikasi untuk type 1 adalah order
        // 3 subtype adalah subtype dari type jika type 1 maka subtype meliputin sub dari order
        // 4 to type adalah jika untuk global maka tentukan to type misalkan admin verifikasi dgn type 5
        // 5 to id adalah untuk menentukan to id jika global untuk admin verifikasi maka 0
        // 6. to company id adalah to company id yg di tuju
        
        $getorder = tblOrders::where([
            'id'            =>  $request["order_id"],
            'status'        =>  1
        ])->first();


        $data = [
            "field"     =>  json_decode($getorder->field),
            "orders"    =>  $getorder
        ];

        return $data;
    }



}
