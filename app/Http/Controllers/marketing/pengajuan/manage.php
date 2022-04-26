<?php
namespace App\Http\Controllers\marketing\pengajuan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\po_orders as tblPoOrders;
use App\Http\Controllers\config\index as Config;
use App\user_employes as tblUserEmployes;
use App\po_order_employes as tblPoOrderEmployes;
use App\po_order_tools as tblPoOrderTools;
use App\assets as tblAssets;
use App\ordernew as tblOrderNew;
use App\notifications as tblNotifications;
use DB;

class manage extends Controller
{
    //
    public function create(Request $request)
    {
        // $Config = new Config;

        // IF NEW CREATE
        if( trim($request->type) == 'new')
        {

            //insert
            $addNew = new \App\Http\Controllers\models\poorders;
            $addNew = $addNew->main($request);


            if( trim($request->file('file')) != '')
            {
                $dataupload = [
                    'user_id'       =>  trim($request->user_id),
                    'link_id'       =>  $addNew['id'],
                    'file'          =>  $request->file('file'),
                    'type'          =>  1,  //po
                    'subtype'       =>  0,
                    'url'           =>  '/upload/documents/po/',
                    'path'          =>  '/documents/po/'
                ];

                $upload = new \App\Http\Controllers\models\upload;
                $upload = $upload->main($dataupload);
            }

            //notif for sdm
            $datanotif = [
                'user_id'       =>  $request->user_id,
                'token'         =>  $addNew['token'],
                'text_sdm'          =>  trim($request->name) .': '.trim($request->sdm),
                'text_tools'          =>  trim($request->name) .': '. trim($request->tools)
            ];

            $notifsdm = new \App\Http\Controllers\notification\reminder\index;
            $notifsdm = $notifsdm->sdm($datanotif);

            $notiftools = new \App\Http\Controllers\notification\reminder\index;
            $notiftools = $notiftools->tools($datanotif);

            $data = [
                'message'           =>  'Pengajuan Project berhasil disimpan'
            ];

            return response()->json($data, 200);

        }
        

        //SUNTING
        if( trim($request->file('file')) != '')
        {
            $dataupload = [
                'user_id'       =>  trim($request->user_id),
                'link_id'       =>  $request->project_id,
                'file'          =>  $request->file('file'),
                'type'          =>  1,  //po
                'subtype'       =>  0,
                'url'           =>  '/upload/documents/po/',
                'path'          =>  '/documents/po/'
            ];

            $upload = new \App\Http\Controllers\models\upload;
            $upload = $upload->main($dataupload);
        }

        $update = new \App\Http\Controllers\models\poorders;
        $update = $update->update($request);

        
        $data = [
            'message'       =>  'Pengajuan Project berhasil disunting'
        ];


        return response()->json($data, 200);
    }

    //
    public function show(Request $request)
    {
        $Config = new Config;

        $getdata = tblPoOrders::from('po_orders as po')
        ->select(
            'po.id', 'po.name', 'po.price', 'po.address', 'po.startdate', 'po.enddate', 'po.sdm', 'po.tools', 'po.progress',
            'po.customer_id', 'c.name as customer',
            'ct.alias as customer_type',
            DB::raw('IFNULL(ud.file, "") as file')
        )
        ->leftJoin('customers as c', function($join)
        {
            $join->on('c.id', '=', 'po.customer_id');
        })
        ->leftJoin('customer_types as ct', function($join)
        {
            $join->on('ct.id', '=', 'c.type');
        })
        ->leftJoin('upload_documents as ud', function($join)
        {
            $join->on('ud.link_id', '=', 'po.id')
            ->where([
                'ud.type'       =>  1,
                'ud.status'     =>  1
            ]);
        })
        ->where([
            'po.id'         =>  trim($request->id)
        ])
        ->first();

        $data = [
            'message'       =>  '',
            'response'       =>  [
                'id'                =>  $getdata->id,
                'name'              =>  $getdata->name,
                'price'             =>  $getdata->price,
                'startdate'         =>  $getdata->startdate,
                'enddate'           =>  $getdata->enddate,
                'sdm'               =>  $getdata->sdm,
                'tools'             =>  $getdata->tools,
                'progress'          =>  $getdata->progress,
                'address'           =>  $getdata->address,
                'file'              =>  $getdata->file,  
                'customer'          =>  [
                    'id'                =>  $getdata->customer_id,
                    'name'              =>  $getdata->customer_type . ' ' . $getdata->customer
                ]
            ]
        ];

        return response()->json($data, 200);
    }


    public function sdm(Request $request)
    {
        $Config = new Config;

        //
        $getdata = tblPoOrders::from('po_orders as po')
        ->select(
            'po.id', 'po.name', 'po.code', 'po.sdm', 'po.sdm_status', 'po.startdate', 'po.enddate',
            'u.name as marketing'
        )
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'po.user_id');
        })
        ->where([
            'po.token'          =>  trim($request->token)
        ]);

        $count = $getdata->count();

        if($count == 0 )
        {
            $data = [
                'message'       =>  'Opss.. Terjadi kesalahan, harap refresh halaman'
            ];

            return response()->json($data, 200);
        }

        $getdata = $getdata->first();

        $getlistitem = $this->datalistsdm($getdata->id);
        
        $data = [
            'message'       =>  '',
            'response'      =>  [
                'id'                =>  $getdata->id,
                'name'              =>  $getdata->name,
                'code'              =>  $getdata->code,
                'sdm'               =>  [
                    'text'              =>  $getdata->sdm,
                    'status'            =>  $getdata->sdm_status
                ],
                'startdate'         =>  $getdata->startdate,
                'enddate'           =>  $getdata->enddate,
                'marketing'             =>  $getdata->marketing,
                'listitem'               =>  count($getlistitem) == 0 ? '' : $getlistitem
            ]
        ];

        return response()->json($data, 200);

    }


    // LIST SDM
    public function listsdm(Request $request)
    {
        $datalist = $this->datalistsdm($request->poid);

        $data = [
            'message'       =>  '',
            'response'      =>  $datalist
        ];

        return response()->json($data,200);
    }

    // DATA LIST SDM
    public function datalistsdm($request)
    {

        $getlist = tblPoOrderEmployes::from('po_order_employes as poe')
        ->select(
            'poe.id', 'ue.name', 'ue.phone', 'eg.name as group',
        )
        ->leftJoin('user_employes as ue', function($join)
        {
            $join->on('ue.id', '=', 'poe.employe_id');
        })
        ->leftJoin('employe_groups as eg', function($join)
        {
            $join->on('eg.id', '=', 'ue.groups');
        })
        ->where([
            'poe.po_id'         =>  $request,
            'poe.status'        =>  1
        ])
        ->get();

        return $getlist;
    }

    public function addsdm(Request $request)
    {
        $list = explode(",", trim($request->listitem));
        $listx = json_decode($request->listitemarr);
        $project_id = trim($request->project_id);
        $user_id = trim($request->user_id);


        foreach($list as $row)
        {
            //check
            $check = tblPoOrderEmployes::where([
                'po_id'         =>  $project_id,
                'employe_id'    =>  $row
            ])
            ->first();

            if( $check != null)
            {
                if( $check->status == 0)
                {
                    $update = tblPoOrderEmployes::where([
                        'po_id'         =>  $project_id,
                        'employe_id'    =>  $row,
                        'status'        =>  0
                    ])
                    ->update([
                        'status'        =>  1
                    ]);
                }
            }
            else
            {
                $datanew = [
                    'user_id'       =>  $user_id,
                    'employe_id'    =>  $row,
                    'poid'          =>  $project_id
                ];

                $addnew = new \App\Http\Controllers\models\poorders;
                $addnew = $addnew->poordersdm($datanew);
            }


        }

        // $update = tblUserEmployes::whereIn('id', $listx)
        // ->update([
        //     'project_id'        =>  $project_id
        // ]);


        //
        $data = [
            'message'       =>  '',
            'list'          =>  ''
        ];

        return response()->json($data, 200);
    }


    public function deletesdm(Request $request)
    {
        $id = trim($request->id);

        $getdata = tblPoOrderEmployes::where([
            'id'        =>  $id
        ])->first();

        //update po employe
        $upPo = DB::table('po_order_employes')
        ->where([
            'id'         =>  $id,
            'employe_id'    =>  $getdata->employe_id
        ])
        ->update([
            'status'        =>  0
        ]);

        $upUsers = DB::table('user_employes')
        ->where([
            'id'        =>  $getdata->employe_id
        ])
        ->update([
            'project_id'        =>  0
        ]);

        $listitem = $this->datalistsdm($getdata->po_id);

        $data = [
            'message'       =>  '',
            'response'      =>  count($listitem) == 0 ? '' : $listitem
        ];

        return response()->json($data, 200);
    }


    //VERIFICATION SDM
    public function verifsdm(Request $request)
    {

        $uppo = tblPoOrders::where([
            'id'        =>  trim($request->project_id),
            'sdm_status'    =>  0
        ])
        ->update([
            'sdm_status'        =>  1
        ]);

        //open notifications
        $upnotification = tblNotifications::where([
            'token'     =>  trim($request->notif_token),
            'open'      =>  0
        ])
        ->update([
            'open'      =>  1
        ]);

        //addnew order sdm for project
        $addneworder = new \App\Http\Controllers\models\poorders;
        $addneworder = $addneworder->ordersdm($request);

        $verif = $this->verif([
            'id'        =>  trim($request->project_id),
            'cek'       =>  'sdm'
        ]);

        $data = [
            'message'       =>  'Proses Pengajuan SDM berhasil dikirim'
        ];

        return response()->json($data, 200);
    }


    // ========= TOOLS MANAGEMEN =============================>

    // TOOLS
    public function tools(Request $request)
    {
        $Config = new Config;

        //
        $getdata = tblPoOrders::from('po_orders as po')
        ->select(
            'po.id', 'po.name', 'po.code', 'po.tools', 'po.tools_status', 'po.startdate', 'po.enddate',
            'u.name as marketing'
        )
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'po.user_id');
        })
        ->where([
            'po.token'          =>  trim($request->token)
        ]);

        $count = $getdata->count();

        if($count == 0 )
        {
            $data = [
                'message'       =>  'Opss.. Terjadi kesalahan, harap refresh halaman'
            ];

            return response()->json($data, 200);
        }

        $getdata = $getdata->first();

        $getlistitem = $this->datalisttools($getdata->id);
        
        $data = [
            'message'       =>  '',
            'response'      =>  [
                'id'                =>  $getdata->id,
                'name'              =>  $getdata->name,
                'code'              =>  $getdata->code,
                'tools'               =>  [
                    'text'              =>  $getdata->tools,
                    'status'            =>  $getdata->tools_status
                ],
                'startdate'         =>  $getdata->startdate,
                'enddate'           =>  $getdata->enddate,
                'marketing'             =>  $getdata->marketing,
                'listitem'               =>  count($getlistitem) == 0 ? '' : $getlistitem
            ]
        ];

        return response()->json($data, 200);

    }

    // LIST TOOLS
    public function listtools(Request $request)
    {
        $datalist = $this->datalisttools($request->poid);

        $data = [
            'message'       =>  '',
            'response'      =>  $datalist
        ];

        return response()->json($data,200);
    }

    //DATA LIST TOOLS
    public function datalisttools($request)
    {

        $getlist = tblPoOrderEmployes::from('po_order_tools as pot')
        ->select(
            'pot.id', 'a.name', 'a.code',
        )
        ->leftJoin('assets as a', function($join)
        {
            $join->on('a.id', '=', 'pot.tools_id');
        })
        ->where([
            'pot.po_id'         =>  $request,
            'pot.status'        =>  1
        ])
        ->get();

        return $getlist;
    }

    //ADD TOOLS
    public function addtools(Request $request)
    {
        $list = explode(",", trim($request->listitem));
        $listx = json_decode($request->listitemarr);
        $project_id = trim($request->project_id);
        $user_id = trim($request->user_id);


        foreach($list as $row)
        {
            //check
            $check = tblPoOrderTools::where([
                'po_id'         =>  $project_id,
                'tools_id'    =>  $row
            ])
            ->first();

            if( $check != null)
            {
                if( $check->status == 0)
                {
                    $update = tblPoOrderTools::where([
                        'po_id'         =>  $project_id,
                        'tools_id'    =>  $row,
                        'status'        =>  0
                    ])
                    ->update([
                        'status'        =>  1
                    ]);
                }
            }
            else
            {
                $datanew = [
                    'user_id'       =>  $user_id,
                    'tools_id'      =>  $row,
                    'poid'          =>  $project_id
                ];

                $addnew = new \App\Http\Controllers\models\poorders;
                $addnew = $addnew->poordertools($datanew);
            }


        }

        $update = tblAssets::whereIn('id', $listx)
        ->update([
            'project_id'        =>  $project_id
        ]);


        //
        $data = [
            'message'       =>  '',
            'list'          =>  ''
        ];

        return response()->json($data, 200);
    }

    //DELETE TOOLS
    public function deletetools(Request $request)
    {
        $id = trim($request->id);

        $getdata = tblPoOrderTools::where([
            'id'        =>  $id
        ])->first();

        //update po employe
        $upPo = DB::table('po_order_tools')
        ->where([
            'id'         =>  $id,
            'tools_id'    =>  $getdata->tools_id
        ])
        ->update([
            'status'        =>  0
        ]);

        $upUsers = DB::table('assets')
        ->where([
            'id'        =>  $getdata->tools_id
        ])
        ->update([
            'project_id'        =>  0
        ]);

        $listitem = $this->datalisttools($getdata->po_id);

        $data = [
            'message'       =>  '',
            'response'      =>  count($listitem) == 0 ? '' : $listitem
        ];

        return response()->json($data, 200);
    }

    public function veriftools(Request $request)
    {

        //
        $uppo = tblPoOrders::where([
            'id'        =>  trim($request->project_id),
            'tools_status'    =>  0
        ])
        ->update([
            'tools_status'        =>  1
        ]);

        // open notifications
        $upnotification = tblNotifications::where([
            'token'     =>  trim($request->notif_token),
            'open'      =>  0
        ])
        ->update([
            'open'      =>  1
        ]);

        // addnew order sdm for project
        $addneworder = new \App\Http\Controllers\models\poorders;
        $addneworder = $addneworder->ordertools($request);

        $verif = $this->verif([
            'id'        =>  trim($request->project_id),
            'cek'       =>  'tools'
        ]);

        //
        $data = [
            'message'       =>  'Proses Pengajuan Alat berhasil dikirim'
        ];

        return response()->json($data, 200);
    }
    //
    public function verif($request)
    {
        //
        $Config = new Config;

        //
        $getdata = tblPoOrders::where([
            'id'        =>  $request['id']
        ])
        ->first();

        if($request['cek']  == 'tools' && $getdata->sdm_status == 1)
        {
            $getdata = tblPoOrders::where([
                'id'        =>  $request['id']
            ])
            ->update([
                'progress'      =>  2
            ]);
        }

        if($request['cek']  == 'sdm' && $getdata->tools_status == 1)
        {
            $getdata = tblPoOrders::where([
                'id'        =>  $request['id']
            ])
            ->update([
                'progress'      =>  2
            ]);
        }

        $checkpo = tblPoOrders::from('po_orders as po')
        ->select(
            'po.id','po.token','po.code','po.name','po.startdate','po.enddate', 'po.sdm', 'po.tools', 'po.price', 'po.address',
            'u.name as marketing',
            'c.name as customer_name',
            'ct.alias as customer_type'
        )
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'po.user_id');
        })
        ->leftJoin('customers as c', function($join)
        {
            $join->on('c.id', '=', 'po.customer_id');
        })
        ->leftJoin('customer_types as ct', function($join)
        {
            $join->on('ct.id', '=', 'c.type');
        })
        ->where([
            'po.id'        =>  $request['id']
        ])
        ->first();

        $contentpo = [
            'name'      =>  $checkpo->name,
            'date'      =>  [
                'start'     =>  $Config->roleFormatDate($checkpo->startdate),
                'end'       =>  $Config->roleFormatDate($checkpo->enddate)
            ],
            'code'      =>  $checkpo->code,
            'tools'     =>  $checkpo->tools,
            'sdm'       =>  $checkpo->sdm,
            'marketing' =>  $checkpo->marketing,
            'customer'  =>  $checkpo->customer_type . ' ' . $checkpo->customer_name,
            'price'     =>  $checkpo->price,
            'address'   =>  $checkpo->address
        ];

        //create order
        $datapo = [
            'poid'      =>  $checkpo->id,
            'contentpo' =>  $contentpo
        ];


        $addnew = new \App\Http\Controllers\models\ordernew;
        $addnew = $addnew->main($datapo);

    }

}