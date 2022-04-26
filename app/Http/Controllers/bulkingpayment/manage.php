<?php
namespace App\Http\Controllers\bulkingpayment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\orders as tblOrders;
use App\order_bulkings as tblOrderBulkings;
use App\Http\Controllers\access\manage as Refresh;
use App\Http\Controllers\config\index as Config;
use App\app_metode_payments as tblAppMetodePayments;
use App\app_bank_lists as tblAppBanks;
use App\user_companies as tblUserCompanies;
use App\bulking_images as tblBulkingImages;

class manage extends Controller
{
    //
    public function listwg(Request $request)
    {


        //
        $src = trim($request->src);
        $comp = trim($request->comp);
        $orderid = trim($request->orderid);


        //
        $getoders = tblOrders::from('orders as o')
        ->select(
            'o.id', 'o.token', 'o.invoice', 'o.field', 'o.expire_payment_date as exp'
        )
        ->where([
            ['o.search', 'like', '%' . $src . '%'],
            ['o.company_id', '=', $comp],
            ['o.bulking', '=', 1],
            ['o.bulking_keep', '=', 0],
            ['o.paid', '=', 1],
            ['o.bulking_paid', '=', 0],
            ['o.status',    '=',    1]
        ]);
        if( $orderid != '')
        {
            $getoders = $getoders->whereNotIn('id', json_decode($orderid));
        }
        $getoders = $getoders->get();


        if( count($getoders) > 0 )
        {

            foreach($getoders as $row)
            {
                $field = json_decode($row->field, true);

                $list[] = [
                    'id'        =>  $row->id,
                    'url'           =>  '/invoice/v1?token=' . $row->token,
                    'invoice'       =>  $row->invoice,
                    'date'          =>  date('d/m/Y', strtotime($row->exp)),
                    'customers'     =>  $field['customers'],
                    'payment'       =>  $field['payment']
                ];
            }

            $data = [
                'message'       =>  '',
                'response'      =>  $list
            ];
    
            return response()->json($data, 200);
        }


        $data = [
            'message'       =>  'Data tidak ditemukan'
        ];

        return response()->json($data, 404);
    }

    // BULKING FORM UPLOAD
    public function check(Request $request)
    {
        $bulking_id = trim($request->bulking_id);
        $item = trim($request->item);

        //create
        // $Config = new Config;

        // $account = $Refresh['refresh']['account'];

        //ceate new
        if( $bulking_id === '')
        {

            $response = $this->create($request);

        }
        // view data bulking
        else 
        {

            
            
            // $cekob = tblOrderBulkings::where([
            //     'id'            =>  $bulking_id
            // ])
            // ->first();

            // if( $cekob->status == 0)
            // {
            //     return response()->json([
            //         'message'               =>  'Data tidak ditemukan'
            //     ], 404);
            // }

            // if( $cekob->paid == 0 )
            // {
                $response = $this->update($request,'true');
            // }

            // $response = $this->view(['bulking_id'=>$bulking_id]);

            // $response = $this->update([
            //     'bulking_id'        =>  $request->bulking_id,
            //     'item'              =>  $request->item,
            //     'total'             =>  $request->total,
            //     'qty'               =>  $request->qty
            // ]);



        }

        return response()->json($response, 200);
    }


    //crete new bulking
    public function create($request)
    {
        //
        $Config = new Config;

        // ceking refresh
        // $Refresh = new Refresh;
        // $Refresh = $Refresh->refresh();
        // $account = $Refresh['refresh']['account'];

        $account = new \App\Http\Controllers\account\index;
        $account = $account->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);

        //
        $item = $request['item'];


        $newid = $Config->createnewidnew([
            'value'         =>  tblOrderBulkings::count(),
            'length'        =>  15
        ]);

        $invoice = date('ymd', time() ) . $Config->createuniq(['length'=>6,'value'=>$newid]) . 'BLK';

        $cekcomp = tblUserCompanies::where([
            'id'        =>  $account['config']['company_id']
        ])->first();
        $expire = date('Y-m-d H:i:s', strtotime( date('Y-m-d H:i:s', time()) . '+'.$cekcomp->expire_payment.' day'));

        $new                    =   new tblOrderBulkings;
        $new->id                =   $newid;
        $new->token             =   md5($newid);
        $new->invoice           =   $invoice;
        $new->order_id          =   $item;
        $new->search            =   '';
        $new->user_id           =   $account['id'];
        $new->payment_user      =   0;
        $new->payment_name      =   '';
        $new->payment_date      =   '';
        $new->payment_total     =   0;
        $new->company_user      =   $account['config']['company_id'];
        $new->total_paid        =   $request['total'];
        $new->quantity          =   $request['qty'];
        $new->company           =   $account['config']['produsen_id'];
        $new->payment_company   =   0;
        $new->expire_payment_date = $expire;
        $new->paid              =   0;
        $new->paid_date         =   '';
        $new->paid_user_id      =   0;
        $new->status            =   1;
        $new->save();
        
        $upod = tblOrders::whereIn('id', json_decode($item))
        ->update([
            'bulking_keep'      =>  1
        ]);

        // return
        $update = $this->updatefield(['bulking_id'=>$newid]);
        $response = $this->view(['bulking_id'=>$newid]);

        $data = [
            'message'       =>  '',
            // 'refresh'       =>  $Refresh,
            'response'      =>  $response
        ];

        return $data;
    }


    //
    public function updatefield($request)
    {
        $Config = new Config;

        //
        $bulking_id = trim($request['bulking_id']);

        $getbulking = tblOrderBulkings::from('order_bulkings as ob')
        ->select(
            'ob.id', 'ob.invoice', 'ob.order_id', 'ob.user_id','ob.payment_user', 'ob.company_user', 'ob.payment_company',
            'uccp.name as company_name',
            'uc.name as customer_name',
            'ucc.name as customer_company',
            'ampc.name as c_bank_type', 'ampc.account_name as c_bank_account', 'ampc.account_norek as c_bank_norek',
            'ablc.name as c_bank_name',
            //produsen
            'abl.name as produsen_bank_name', 'abl.image as produsen_bank_image',
            'amp.name as produsen_bank_type', 'amp.account_name as produsen_bank_account', 'amp.account_norek as produsen_bank_norek',
            //upload
            'bi.id as upload_id', 'bi.url as upload_image', 'bi.created_at as upload_date',
            'uu.name as upload_user'
        )
        ->leftJoin('users as uc', function($join)
        {
            $join->on('uc.id', '=', 'ob.user_id');
        })
        ->leftJoin('user_companies as ucc', function($join)
        {
            $join->on('ucc.id', '=', 'ob.company_user');
        })
        // payment user
        ->leftJoin('app_metode_payments as ampc', function($join)
        {
            $join->on('ampc.id', '=', 'ob.payment_user');
        })
        ->leftJoin('app_bank_lists as ablc', function($join)
        {
            $join->on('ablc.id', '=', 'ampc.bank_id');
        })
        // company produsen
        ->leftJoin('user_companies as uccp', function($join)
        {
            $join->on('uccp.id', '=', 'ob.company');
        })
        //payment produsen
        ->leftJoin('app_metode_payments as amp', function($join)
        {
            $join->on('amp.id', '=', 'ob.payment_company');
        })
        ->leftJoin('app_bank_lists as abl', function($join)
        {
            $join->on('abl.id', '=', 'amp.bank_id');
        })
        //upload
        ->leftJoin('bulking_images as bi', function($join)
        {
            $join->on('bi.bulking_id', '=', 'ob.id')
            ->where(['bi.status'=>1]);
        })
        ->leftJoin('users as uu', function($join)
        {
            $join->on('uu.id', '=', 'bi.user_id');
        })
        ->where([
            'ob.id'        =>  $bulking_id
        ])
        ->first();

        //
        $getod = tblOrders::whereIn('id', json_decode($getbulking->order_id))
        ->get();

        $serch[] = $getbulking->invoice;

        foreach($getod as $row)
        {
            $field = json_decode($row->field, true);

            $list[] = [
                // 'order'     =>  [
                    'id'            =>  $row->id,
                    'invoice'       =>  $row->invoice,
                    'url'           =>  '/invoice/v1?token=' . $row->token,
                    'customer_name' =>  $field['customers']['name'],
                    'customer_phone'    =>  $field['customers']['phone'],
                    'total'             =>  $field['payment']['total_reseller'],
                    'exp'               =>  $row->expire_payment_date
                // ]
            ];

            $serch[] = $row->invoice;
        }


        $orders = [
            'id'            =>  $getbulking->id,
            'invoice'       =>  $getbulking->invoice,
            'company'       =>  $getbulking->company_name,
            'payment'       =>  [
                'id'            =>  $getbulking->payment_company,
                'type'          =>  $getbulking->produsen_bank_type . ' Bank',
                'name'          =>  $getbulking->produsen_bank_name,
                'image'         =>  $Config->apps()['storage']['URL'] . '/images/bank/' . $getbulking->produsen_bank_image,
                'account'        =>  $getbulking->produsen_bank_account,
                'norek'         =>  $getbulking->produsen_bank_norek
            ]
        ];

        $upload = [
            'id'        =>  $getbulking->upload_id,
            'image'     =>  $getbulking->upload_image,
            'date'      =>  $getbulking->upload_date,
            'user'      =>  $getbulking->upload_user
        ];

        $customers = [
            'user'          =>  [
                'id'            =>  $getbulking->user_id,
                'name'          =>  $getbulking->customer_name
            ],
            'company'       =>  [
                'id'            =>  $getbulking->company_user,
                'name'          =>  $getbulking->customer_company
            ],
            'payment'       =>  [
                'id'            =>  $getbulking->payment_user,
                'name'          =>  $getbulking->c_bank_name,
                'type'          =>  $getbulking->c_bank_type . ' Bank',
                'account'        =>  $getbulking->c_bank_account,
                'norek'         =>  $getbulking->c_bank_norek
            ]
        ];


        $field = [
            'orders'        =>  $orders,
            'customers'     =>  $customers,
            'list'          =>  json_encode($list),
            'upload'        =>  $upload
        ];
        
        $update = tblOrderBulkings::where([
            'id'        =>  $bulking_id
        ])
        ->update([
            'field'     =>  $field,
            'search'    =>  $serch
        ]);

        // return response()->json([
        //     'response'  =>  json_encode($geting, true),
        //     'list'      =>  $list
        // ],
        //     200);


    }

    //
    public function update($request,$ref)
    {
        // if( $ref == 'true')
        // {
        //     $Refresh = new Refresh;
        //     $Refresh = $Refresh->refresh();
        // }

        //
        $bulking_id = trim($request['bulking_id']);
        $item = trim($request['item']);
        $total = trim($request['total']);
        $qty = trim($request['qty']);

        //update bulking
        $upbulking = tblOrderBulkings::where([
            'id'            =>  $bulking_id
        ])
        ->update([
            'order_id'      =>  $item,
            'total_paid'    =>  $total,
            'quantity'      =>  $qty
        ]);

        $uporder = tblOrders::whereIn('id', [$item])
        ->update([
            'bulking_keep'          =>  1
        ]);

        $up = $this->updatefield(['bulking_id'=>$bulking_id]);
        $response = $this->view(['bulking_id'=>$bulking_id]);

        $data = [
            'message'       =>  '',
            // 'refresh'       =>  $ref == 'true' ? $Refresh : '',
            'response'      =>  $response
        ];

        return $data;
    }

    //view
    public function view($request)
    {
        $bulking_id = $request['bulking_id'];

        $getdata = tblOrderBulkings::from('order_bulkings as ob')
        ->select(
            'ob.id', 'ob.invoice', 'ob.company_user', 'ob.paid',
            'ob.field', 'ob.total_paid as total', 'ob.quantity'
        )
        ->where([
            'ob.id'            =>  $bulking_id
        ])->first();


        $getbank = tblAppMetodePayments::where([
            'type'              =>  1,
            'company_id'        =>  ''
        ])
        ->get();

        //
        // $banklist = new \App\Http\Controllers\orders\payment;
        // $banklist = $banklist->viewbanklist();

        //bank cliend
        $bankclient  = new \App\Http\Controllers\companies\manage;
        $bankclient = $bankclient->paymentlist([
            'company_id'        =>  $getdata->company_user,
            'type'              =>  1
        ]);


        $field = json_decode($getdata->field);

        $data = [
            'id'            =>  $getdata->id,
            'invoice'       =>  $getdata->invoice,
            'total'         =>  $getdata->total,
            'paid'          =>  $getdata->paid,
            'quantity'      =>  $getdata->quantity,
            'field'         =>  $field,
            'detail'        =>  json_decode($field->list),
            // 'bank'          =>  $banklist,
            'bank_user'     =>  $bankclient,
            'thisdate'      =>  date('Y-m-d', time()),
            'orders'        =>  $field->orders
        ];

        return $data;
        
    }

    // delete
    public function deletelist(Request $request)
    {
        $order_id = trim($request->order_id);
        $bulking_id = trim($request->bulking_id);
        $total = trim($request->total);
        $item = trim($request->item);
        $qty = trim($request->qty);

        if( $bulking_id != '' )
        {
            //update 
            $upod = tblOrders::where([
                'id'            =>  $order_id
            ])
            ->update([
                'bulking_keep'  =>  0
            ]);

            if( $item == '')
            {
                $upbul = tblOrderBulkings::where([
                    'id'            =>  $bulking_id
                ])
                ->update([
                    'status'        =>  0
                ]);

                $status = 404;
            }
            else
            {
                $this->update($request,'false');
                $status = 200;
            }

            $data = [
                'order_id'          =>  $order_id,
                'bulking_id'        =>  $bulking_id,
                'total'             =>  $total,
                'item'              =>  $item,
                'qty'               =>  $qty
            ];

            return response()->json($data, $status);
        }

        return response()->json(['message'=>'success'], 200);
    }

    //
    public function delete(Request $request)
    {
        $id = trim($request->id);

        //
        $check = tblOrderBulkings::where([
            'id'        =>  $id,
            'status'    =>  1
        ])->first();

        //
        if( $check == null )
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }

        $updateod = tblOrders::whereIn('id', json_decode($check->order_id))
        ->update([
            'bulking_keep'      =>  0
        ]);

        $updateob = tblOrderBulkings::where([
            'id'            =>  $id
        ])
        ->update([
            'status'        =>  0
        ]);

        $data = [
            'message'       =>  'Pembayaran bulking berhasil dibatalkan'
        ];

        return response()->json($data, 200);
    }

    //
    public function metodepayment(Request $request)
    {
        $Config = new Config;

        $bulking_id = trim($request->id);

        $getordersbulking = tblOrderBulkings::where([
            'id'            =>  $bulking_id
        ])->first();

        $getdata = tblAppMetodePayments::where([
            'type'          =>  1,
            'company_id'    =>  $getordersbulking->company,
            'status'        =>  1
        ])
        ->get();


        foreach($getdata as $row)
        {

            if( $row->type == '1')
            {
                $getbank = tblAppBanks::where([
                    'id'        =>  $row->bank_id
                ])->first();

            }

            //
            $list[] = [
                'id'        =>  $row->id,
                'type'      =>  $row->type,
                'name'      =>  'Bank ' . $getbank->label,
                'account_name'  =>  $row->account_name,
                'account_norek' =>  $row->account_norek,
                'label'     =>  ($row->account_name . ' - ' . $row->account_norek),
                'images'    =>  $Config->apps()['storage']['URL'] . '/images/' . ('bank/' . $getbank->image)
            ];
        }

         //
         $data = [
            'message'           =>  $getordersbulking->company,
            'list'              =>  $list
        ];


        return response()->json($data, 200);

    }

    public function setmetodepayment(Request $request)
    {
        $bulking_id = trim($request->bulking_id);
        $bank_id = trim($request->payment_id);

        //
        $up = tblOrderBulkings::where([
            'id'        =>  $bulking_id
        ])
        ->update([
            'payment_company'       =>  $bank_id
        ]);

        //
        $update = $this->updatefield(['bulking_id'=>$bulking_id]);

        return response()->json([
            'message'       =>  'success'
        ], 200);
    }

    //upload 
    public function upload(Request $request)
    {
        //
        $Config = new Config;

        // ceking refresh
        // $Refresh = new Refresh;
        // $Refresh = $Refresh->refresh();

        // $account = $Refresh['refresh']['account'];
        $account = new \App\Http\Controllers\account\index;
        $account = $account->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);
        
        // //
        $bulking_id = trim($request->bulking_id);

        // $type = $request->type;
        $bank = trim($request->bank);
        // $name = $request->name;
        $date = trim($request->date);
        $total = trim($request->total);
        $file = $request->file('file');
        $payment_id = trim($request->payment_id);


        // $data = [
        //     'bulking_id'        =>  $bulking_id,
        //     'bank'              =>  $bank,
        //     'total'             =>  $total,
        //     'date'              =>  $date,
        //     // 'file'              =>  $file,
        //     'payment_id'        =>  $payment_id
        // ];

        $upob = tblOrderBulkings::from('order_bulkings as ob')
        ->where([
            'ob.id'      =>  $bulking_id
        ])
        ->update([
            'ob.payment_user'       =>  $bank,
            'ob.payment_date'       =>  $date,
            'ob.payment_total'      =>  $total,
            'ob.payment_company'    =>  $payment_id,
            'ob.paid'               =>  1
        ]);


        //
        $newid = $Config->createnewidnew([
            'value'     =>  tblBulkingImages::count(),
            'length'    =>  15
        ]);
        $token = md5($newid);

        //
        $newimg = new tblBulkingImages;
        $newimg->id             =   $newid;
        $newimg->token          =   $token;
        $newimg->url            =   $Config->apps()['storage']['URL'] . '/images/bulking/' . $token . '.jpg';
        $newimg->bulking_id       =   $bulking_id;
        $newimg->user_id        =   $account['id'];
        $newimg->status         =   1;
        $newimg->save();
        
        
        //upload transfer
        if( $file != '')
        {
            $dataupload = [
                'name'          =>  $token,
                'file'          =>  $file,
                'path'          =>  'images/bulking/',
                "URL"           =>  $Config->apps()["URL"]["STORAGE"] . "/s3/upload/transfer"
            ];
    
            $upload = new \App\Http\Controllers\tdparty\s3\herbindo;
            $upload = $upload->transfer($dataupload);
        }
        
        //
        $update = $this->updatefield(['bulking_id'=>$bulking_id]);
        $view = $this->view(['bulking_id'=>$bulking_id]);


        $data = [
            'message'       =>  '',
            // 'refresh'       =>  $Refresh,
            'response'      =>  $view
        ];


        return response()->json($data, 200);
    }

    //
    public function test(Request $request)
    {
        $bulking_id = $request->id;

        $update = $this->view(['bulking_id'=>$bulking_id]);

        return $update;
    }
}