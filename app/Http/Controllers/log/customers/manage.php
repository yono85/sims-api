<?php
namespace App\Http\Controllers\log\customers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\users as tblUsers;
use App\customers as tblCustomers;
use App\Http\Controllers\log\customers\index as AddLogs;
use DB;

class manage extends Controller
{
    //
    private function data($request)
    {
        $getCustomers = tblCustomers::where([
            'id'        =>  $request['customer_id']
        ])->first();


        $getUsers = tblUsers::where([
            'id'        =>  $request['user_id']
        ])->first();


        $data = [
            'customers'     =>  $getCustomers,
            'users'         =>  $getUsers
        ];

        return $data;
    }


    public function Add($request)
    {
        $AddLogs = new AddLogs;

        //
        $getdata = [
            'customer_id'       =>  $request['customer_id'],
            'user_id'           =>  $request['user_id']
        ];

        $DataCU = $this->data($getdata);

        $text = [
            'title' =>  'Customer Baru',
            'body'  =>  'Customer ' . $DataCU['customers']['name'] . ' behasil ditambahkan',
            'customers' =>  [
                'id'        =>  $DataCU['customers']['id'],
                'name'      =>  $DataCU['customers']['name']
            ],
            'users'     =>  [
                'id'        =>  $DataCU['users']['id'],
                'name'      =>  $DataCU['users']['name']
            ]
        ];

        $dataadd = [
            'type'      =>  1, //history
            'sub_type'  =>  1, //new
            'text'      =>  $text,
            'customers' =>  $DataCU['customers'],
            'users'     =>  $DataCU['users']
        ];


        //insert add logs
        $AddLogs->main($dataadd);
    }


    public function orders($request)
    {
        $AddLogs = new AddLogs;

        //
        $getdata = [
            'customer_id'       =>  $request['customer_id'],
            'user_id'           =>  $request['user_id']
        ];

        $DataCU = $this->data($getdata);

        $text = [
            'title' =>  'Melakukan order',
            'body'  =>  'Customer ' . $DataCU['customers']['name'] . ' melakukan order #<a href="/dashboard/orders?q='.$request['invoice'].'" target="_blank">'.$request['invoice'].'</a>',
            'customers' =>  [
                'id'        =>  $DataCU['customers']['id'],
                'name'      =>  $DataCU['customers']['name']
            ],
            'users'     =>  [
                'id'        =>  $DataCU['users']['id'],
                'name'      =>  $DataCU['users']['name']
            ],
            'orders'        =>  [
                'id'        =>  $request['order_id']
            ]
        ];

        $dataadd = [
            'type'      =>  1, //history
            'sub_type'  =>  2, //add orders
            'text'      =>  $text,
            'customers' =>  $DataCU['customers'],
            'users'     =>  $DataCU['users']
        ];


        //insert add logs
        $AddLogs->main($dataadd);
    }


    public function CBO($request)
    {
        $AddLogs = new AddLogs;

        //
        $getdata = [
            'customer_id'       =>  $request['customer_id'],
            'user_id'           =>  $request['user_id']
        ];

        $DataCU = $this->data($getdata);

        $text = [
            'title' =>  'CBO (Menambah alamat)',
            'body'  =>  'Customer ' . $DataCU['customers']['name'] . ' menjadi status CBO menambah alamat ' . $request['label'],
            'customers' =>  [
                'id'        =>  $DataCU['customers']['id'],
                'name'      =>  $DataCU['customers']['name']
            ],
            'users'     =>  [
                'id'        =>  $DataCU['users']['id'],
                'name'      =>  $DataCU['users']['name']
            ],
            'address'   =>  [
                'id'            =>  $request['address_id']
            ]
        ];

        $dataadd = [
            'type'      =>  1, //history
            'sub_type'  =>  3, //CBO
            'text'      =>  $text,
            'customers' =>  $DataCU['customers'],
            'users'     =>  $DataCU['users']
        ];


        // update table customer to CBO
        $upCustomers = DB::table('customers')
        ->where([
            'id'        =>  $request['customer_id']
        ])
        ->update([
            'taging'   =>  '[2]'
        ]);

        //insert add logs
        $AddLogs->main($dataadd);
    }


    public function CBT($request)
    {
        $AddLogs = new AddLogs;

        //
        $getdata = [
            'customer_id'       =>  $request['customer_id'],
            'user_id'           =>  $request['user_id']
        ];

        $DataCU = $this->data($getdata);

        $text = [
            'title' =>  'CBT',
            'body'  =>  'Customer ' . $DataCU['customers']['name'] . ' menjadi status CBT pada order #<a href="/dashboard/orders?q='.$request['invoice'].'" target="_blank">'.$request['invoice'].'</a>',
            'customers' =>  [
                'id'        =>  $DataCU['customers']['id'],
                'name'      =>  $DataCU['customers']['name']
            ],
            'users'     =>  [
                'id'        =>  $DataCU['users']['id'],
                'name'      =>  $DataCU['users']['name']
            ],
            'orders'    =>  [
                'id'        =>  $request['order_id']
            ]
        ];

        $dataadd = [
            'type'      =>  1, //history
            'sub_type'  =>  4, //CBT
            'text'      =>  $text,
            'customers' =>  $DataCU['customers'],
            'users'     =>  $DataCU['users']
        ];

        // update table customer to CBT
        $upCustomers = DB::table('customers')
        ->where([
            'id'        =>  $request['customer_id']
        ])
        ->update([
            'taging'   =>  '[3]'
        ]);

        //insert add logs
        $AddLogs->main($dataadd);
    }


    public function CST($request)
    {
        $AddLogs = new AddLogs;

        //
        $getdata = [
            'customer_id'       =>  $request['customer_id'],
            'user_id'           =>  $request['user_id']
        ];

        $DataCU = $this->data($getdata);

        $text = [
            'title' =>  'CST',
            'body'  =>  'Customer ' . $DataCU['customers']['name'] . ' menjadi status CST pada order #<a href="/dashboard/orders?q='.$request['invoice'].'" target="_blank">'.$request['invoice'].'</a>',
            'customers' =>  [
                'id'        =>  $DataCU['customers']['id'],
                'name'      =>  $DataCU['customers']['name']
            ],
            'users'     =>  [
                'id'        =>  $DataCU['users']['id'],
                'name'      =>  $DataCU['users']['name']
            ],
            'orders'    =>  [
                'id'        =>  $request['order_id']
            ]
        ];

        $dataadd = [
            'type'      =>  1, //history
            'sub_type'  =>  5, //CST
            'text'      =>  $text,
            'customers' =>  $DataCU['customers'],
            'users'     =>  $DataCU['users']
        ];

        // update table customer to CBT
        $upCustomers = DB::table('customers')
        ->where([
            'id'        =>  $request['customer_id']
        ])
        ->update([
            'taging'   =>  '[4]'
        ]);

        //insert add logs
        $AddLogs->main($dataadd);
    }

    public function update($request)
    {
        $AddLogs = new AddLogs;

        //
        $getdata = [
            'customer_id'       =>  $request['customer_id'],
            'user_id'           =>  $request['user_id']
        ];

        $DataCU = $this->data($getdata);

        $text = [
            'title' =>  $request['title'],
            'body'  =>  $request['body'],
            'customers' =>  [
                'id'        =>  $DataCU['customers']['id'],
                'name'      =>  $DataCU['customers']['name']
            ],
            'users'     =>  [
                'id'        =>  $DataCU['users']['id'],
                'name'      =>  $DataCU['users']['name']
            ]
        ];

        $dataadd = [
            'type'      =>  $request['type'], //update
            'sub_type'  =>  $request['sub_type'], //add address
            'text'      =>  $text,
            'customers' =>  $DataCU['customers'],
            'users'     =>  $DataCU['users']
        ];

        //insert add logs
        $AddLogs->main($dataadd);
    }

}