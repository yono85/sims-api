<?php
namespace App\Http\Controllers\testing\data;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\users as tblUsers;
use DB;

class getdata extends Controller
{
    //
    public function users(Request $request)
    {
        $userid = trim($request->id);


        $getuser = tblUsers::from('users as u')
        ->select(
            'u.id',
            'uc.aside_menu'
        )
        ->leftJoin('user_configs as uc', function($join)
        {
            $join->on('uc.user_id', '=', 'u.id');
        })
        ->where([
            'u.id'        =>  $userid
        ])
        ->first();

        $dt = json_decode($getuser->aside_menu);
        // // $dt = json_decode( trim($getuser->aside_menu) , true);

        // foreach($dt as $row => $value)
        // {
        //     $menu[] = [
        // //         'name'          =>  $row,
        // //         'id'            =>  $value['title'],
        // //         'icon'          =>  $value['icon'],
        // //         'type'          =>  $value['type'],
        // //         'url'           =>  $value['url'],
        // //         'arrow'         =>  $value['arrow'],
        // //         'child'         =>  $value['child']
        //     ];
        // }



        $data = [
            'response'          =>  
            [
                'menu'          =>  $dt
            ]
        ];

        return response()->json($data, 200);
    }


    public function getasside(Request $request)
    {

        $level = $request->level;
        $sublevel = $request->sublevel;
        
        if( $level == 1)
        {
            $asidelevel = $this->produsen();
        }
        else
        {
            $asidelevel = $this->distributor();
        }

        $data = [
            'menu'      =>  $asidelevel[$sublevel]['menu'],
            'submenu'   =>  $asidelevel[$sublevel]['submenu']
        ];


        $asidemenu = $this->createaside($data);

        $up = DB::table('user_configs')
        ->where([
            'user_id'       =>  $request->id
        ])
        ->update([
            'aside_menu'        =>  json_encode($asidemenu)
        ]);

        // return $data;
        return response()->json($asidemenu, 200);
    }

    public function produsen()
    {
        $data = [
            '1'         =>  [ //administator
                'menu'          =>  'dashboard,marketing,admin,pengaturan',
                'submenu'       =>  [
                    'marketing'     =>  'orders,customers',
                    'admin'         =>  'veriforders,verifbulking,shiping',
                    'pengaturan'    =>  'pengguna,distributor,manageglobal'
                ]
            ],
            '2'         =>  [
                'menu'          =>  'dashboard,marketing,admin,pengaturan',
                'submenu'       =>  [
                    'marketing'     =>  'orders,customers',
                    'admin'         =>  'veriforders,verifbulking,shiping',
                    'pengaturan'    =>  'pengguna,distributor,manageglobal'
                ]
            ],
            '3'         =>  [
                'menu'          =>  'dashboard,marketing',
                'submenu'       =>  [
                    'marketing'     =>  'orders,customers'
                ]
            ],
            '4'         =>  [
                'menu'          =>  'dashboard,production',
                'submenu'       =>  [
                    'production'    =>  'stockproduct'
                ]
            ],
            '5'         =>  [
                'menu'          =>  'dashboard,admin',
                'submenu'       =>  [
                    'admin'         =>  'veriforders,verifbulking,shiping',
                ]
            ],
            '6'         =>  [
                'menu'          =>  'dashboard,admin',
                'submenu'       =>  [
                    'admin'         =>  'shiping',
                ]
            ],
        ];



        return $data;

    }

    public function distributor()
    {
        $data = [
            '1'         =>  [ //Administrator
                'menu'          =>  'dashboard,marketing,stock,admin,pengaturan',
                'submenu'       =>  [
                    'marketing'     =>  'orders,customers',
                    'stock'         =>  'orderstock',
                    'admin'         =>  'veriforders,paymentbulking,shiping',
                    'pengaturan'    =>  'pengguna,manageglobal'
                ]
            ],
            '2'         =>  [ //supervisor
                'menu'          =>  'dashboard,marketing,stock,admin,pengaturan',
                'submenu'       =>  [
                    'marketing'     =>  'orders,customers',
                    'stock'         =>  'orderstock',
                    'admin'         =>  'veriforders,paymentbulking,shiping',
                    'pengaturan'    =>  'pengguna,manageglobal'
                ]
            ],
            '3'         =>  [ //marketing
                'menu'          =>  'dashboard,marketing',
                'submenu'       =>  [
                    'marketing'     =>  'orders,customers'
                ]
            ],
            '5'         =>  [
                'menu'          =>  'dashboard,stock,admin',
                'submenu'       =>  [
                    'stock'         =>  'orderstock',
                    'admin'         =>  'veriforders,paymentbulking,shiping'
                ]
            ],
            '6'         =>  [
                'menu'          =>  'dashboard,admin',
                'submenu'       =>  [
                    'admin'         =>  'shiping',
                ]
            ],
        ];



        return $data;

    }

    public function createaside($request)
    {
        $menu = explode(",", $request['menu']);
        $submenu = $request['submenu'];

        //
        $cmenu = $this->menu();
        $csubmenu = $this->submenu();

        //
        foreach($menu as $m)
        {
            $vmenu = $cmenu[$m];

            if( $vmenu['type'] != '')
            {
                $vsubmenu = explode(",", $submenu[$m]);
                $child = [];

                foreach($vsubmenu as $s)
                {
                    
                    $child[] = [
                        'title'         =>  $csubmenu[$s]['title'],
                        'url'           =>  $csubmenu[$s]['url'],
                    ];
                }
            }
            else
            {
                $child = '';
            }

            //
            $list[] = [
                // $m      =>  [
                    'title'     =>  $vmenu['title'],
                    'type'      =>  $vmenu['type'],
                    'url'       =>  $vmenu['url'],
                    'arrow'     =>  $vmenu['arrow'],
                    'icon'      =>  $vmenu['icon'],
                    'child'     =>  $child
                // ]
            ];
        }

        //
        return $list;

    }

    //menu
    public function menu()
    {

        $data = [
                'dashboard'     =>  [
                    'title'         =>  'Dashboard',
                    'icon'          =>  'icon fa flaticon2-line-chart',
                    'type'          =>  '',
                    'arrow'         =>  '',
                    'url'           =>  '/dashboard',
                    'child'         =>  ''
                ],//end dashboard
                'marketing'     =>  [
                    'title'         =>  'Marketing',
                    'icon'          =>  'icon fa flaticon-businesswoman',
                    'type'          =>  'collaps',
                    'arrow'         =>  'icon icon-keyboard_arrow_down arrow-icon',
                    'child'         =>  '',
                    'url'           =>  ''
                ], //end marketing
                'stock'         =>  [
                    'title'         =>  'Stock Barang',
                    'icon'          =>  'icon fa flaticon2-open-box',
                    'type'          =>  'collaps',
                    'arrow'         =>  'icon icon-keyboard_arrow_down arrow-icon',
                    'child'         =>  '',
                    'url'           =>  ''
                ], //end stock
                'admin'             =>  [
                    'title'         =>  'Admin',
                    'icon'          =>  'icon sli_icon-users',
                    'type'          =>  'collaps',
                    'arrow'         =>  'icon icon-keyboard_arrow_down arrow-icon',
                    'child'         =>  '',
                    'url'           =>  ''
                ], //end admin
                'pengaturan'             =>  [
                    'title'         =>  'Pengaturan',
                    'icon'          =>  'icon fa flaticon-cogwheel-1',
                    'type'          =>  'collaps',
                    'arrow'         =>  'icon icon-keyboard_arrow_down arrow-icon',
                    'child'         =>  '',
                    'url'           =>  ''
                ], //end pengaturan
                'production'             =>  [
                    'title'         =>  'Produksi',
                    'icon'          =>  'icon sli_icon-box',
                    'type'          =>  'collaps',
                    'arrow'         =>  'icon icon-keyboard_arrow_down arrow-icon',
                    'child'         =>  '',
                    'url'           =>  ''
                ], //end pengaturan
        ];


        return $data;
        

    }

    //sub menu
    public function submenu()
    {

        $data = [
            'orders'         =>  [ //marketing
                'title'             =>  'Orders',
                'url'               =>  '/dashboard/orders'
            ],
            'customers'         =>  [ //marketing
                'title'             =>  'Customers',
                'url'               =>  '/dashboard/customers'
            ],
            'veriforders'        =>  [ //admin produsen, distributor
                'title'             =>  'Verifikasi Pembayaran',
                'url'               =>  '/dashboard/veriforders'
            ],
            'paymentbulking'        =>  [ //admin distributor
                'title'             =>  'Pembayaran Bulking',
                'url'               =>  '/dashboard/bulkingpayment'
            ],
            'verifbulking'        =>  [ //admin produsen
                'title'             =>  'Verifikasi Bulking',
                'url'               =>  '/dashboard/verifbulking'
            ],
            'shiping'        =>  [ //admin produsen, distributor
                'title'             =>  'Shping',
                'url'               =>  '/dashboard/shiping'
            ],
            'orderstock'        =>  [ //admin distributor
                'title'             =>  'Order Stock',
                'url'               =>  '/dashboard/orderstock'
            ],
            'pengguna'        =>  [ //pengaturan produsen, distributor
                'title'             =>  'Pengguna',
                'url'               =>  '/dashboard/pengguna'
            ],
            'distributor'        =>  [ //pengaturan produsen
                'title'             =>  'Distributor',
                'url'               =>  '/dashboard/distributor'
            ],
            'manageglobal'          =>  [ //pengaturan produsen, distributor
                'title'             =>  'Pengaturan Umum',
                'url'               =>  '/dashboard/mgglobal'
            ],
            'stockproduct'          =>  [ //production produsen
                'title'             =>  'Stock Produk',
                'url'               =>  '/dashboard/stockproduct'
            ],
        ];


        return $data;
    }

    public function xprodusen()
    {
        $Config = new Config;

        $URL = $Config->URI();


        //
        $data = [

            //administrator
            '1'         =>  [ 
                'dashboard'     =>  [
                    'title'         =>  'Dashboard',
                    'icon'          =>  'icon fa flaticon2-line-chart',
                    'type'          =>  '',
                    'url'           =>  $URL . '/dashboard'
                ],//end dashboard
                'marketing'     =>  [
                    'title'         =>  'Marketing',
                    'icon'          =>  'icon fa flaticon-businesswoman',
                    'type'          =>  'collaps2',
                    'arrow'         =>  'icon icon-keyboard_arrow_down arrow-icon',
                    'child'         =>  [
                        // 'orders'        =>  [
                        //     'title'             =>  'Orders',
                        //     'url'               =>  $URL . '/dashboard/orders'
                        // ],
                        // 'customers'        =>  [
                        //     'title'             =>  'Customers',
                        //     'url'               =>  $URL . '/dashboard/customers'
                        // ],
                    ]
                ], //end marketing
                // 'stock'         =>  [
                //     'title'         =>  'Stock Barang',
                //     'icon'          =>  'icon fa flaticon2-open-box',
                //     'type'          =>  'collaps3',
                //     'arrow'         =>  'icon icon-keyboard_arrow_down arrow-icon',
                //     'child'         =>  [
                //         'orderstock'        =>  [
                //             'title'             =>  'Order Stock',
                //             'url'               =>  $URL . '/dashboard/orderstock'
                //         ]
                //     ]
                // ], //end stock
                'admin'             =>  [
                    'title'         =>  'Admin',
                    'icon'          =>  'icon sli_icon-users',
                    'type'          =>  'collaps4',
                    'arrow'         =>  'icon icon-keyboard_arrow_down arrow-icon',
                    'child'         =>  [
                        // 'veriforder'        =>  [
                        //     'title'             =>  'Verifikasi Pembayaran',
                        //     'url'               =>  $URL . '/dashboard/veriforders'
                        // ],
                        // 'bulkingpayment'        =>  [
                        //     'title'             =>  'Pembayaran Bulking',
                        //     'url'               =>  $URL . '/dashboard/bulkingpayment'
                        // ],
                        // 'verifbulking'        =>  [
                        //     'title'             =>  'Verifikasi Bulking',
                        //     'url'               =>  $URL . '/dashboard/verifbulking'
                        // ],
                        // 'ordershiping'        =>  [
                        //     'title'             =>  'Shping',
                        //     'url'               =>  $URL . '/dashboard/shiping'
                        // ]
                    ]
                ], //end admin
                'pengaturan'             =>  [
                    'title'         =>  'Pengaturan',
                    'icon'          =>  'icon fa flaticon-cogwheel-1',
                    'type'          =>  'collaps5',
                    'arrow'         =>  'icon icon-keyboard_arrow_down arrow-icon',
                    'child'         =>  [
                        // 'pengguna'        =>  [
                        //     'title'             =>  'Pengguna',
                        //     'url'               =>  $URL . '/dashboard/pengguna'
                        // ],
                        // 'distributor'        =>  [
                        //     'title'             =>  'Distributor',
                        //     'url'               =>  $URL . '/dashboard/distributor'
                        // ],
                        // 'mgglobal'          =>  [
                        //     'title'             =>  'Pengaturan Umum',
                        //     'url'               =>  $URL . '/dashboard/mgglobal'
                        // ],
                    ]
                ], //end pengaturan
            ],
                        
        ];

        return $data;
    }


    public function xdistributor()
    {
        $Config = new Config;

        $URL = $Config->URI();

        //
        $data = [

            // administrator
            '1'         =>  [
                'dashboard'     =>  [
                    'title'         =>  'Dashboard',
                    'icon'          =>  'icon fa flaticon2-line-chart',
                    'type'          =>  '',
                    'url'           =>  $URL . '/dashboard'
                ],//end dashboard
                'marketing'     =>  [
                    'title'         =>  'Marketing',
                    'icon'          =>  'icon fa flaticon-businesswoman',
                    'type'          =>  'collaps2',
                    'arrow'         =>  'icon icon-keyboard_arrow_down arrow-icon',
                    'child'         =>  [
                        // 'orders'        =>  [
                        //     'title'             =>  'Orders',
                        //     'url'               =>  $URL . '/dashboard/orders'
                        // ],
                        // 'customers'        =>  [
                        //     'title'             =>  'Customers',
                        //     'url'               =>  $URL . '/dashboard/customers'
                        // ],
                    ]
                ], //end marketing
                'stock'         =>  [
                    'title'         =>  'Stock Barang',
                    'icon'          =>  'icon fa flaticon2-open-box',
                    'type'          =>  'collaps3',
                    'arrow'         =>  'icon icon-keyboard_arrow_down arrow-icon',
                    'child'         =>  [
                        // 'orderstock'        =>  [
                        //     'title'             =>  'Order Stock',
                        //     'url'               =>  $URL . '/dashboard/orderstock'
                        // ]
                    ]
                ], //end stock
                'admin'             =>  [
                    'title'         =>  'Admin',
                    'icon'          =>  'icon sli_icon-users',
                    'type'          =>  'collaps4',
                    'arrow'         =>  'icon icon-keyboard_arrow_down arrow-icon',
                    'child'         =>  [
                        // 'veriforder'        =>  [
                        //     'title'             =>  'Verifikasi Pembayaran',
                        //     'url'               =>  $URL . '/dashboard/veriforders'
                        // ],
                        // 'bulkingpayment'        =>  [
                        //     'title'             =>  'Pembayaran Bulking',
                        //     'url'               =>  $URL . '/dashboard/bulkingpayment'
                        // ],
                        // 'ordershiping'        =>  [
                        //     'title'             =>  'Shping',
                        //     'url'               =>  $URL . '/dashboard/shiping'
                        // ]
                    ]
                ], //end admin
                'pengaturan'             =>  [
                    'title'         =>  'Pengaturan',
                    'icon'          =>  'icon fa flaticon-cogwheel-1',
                    'type'          =>  'collaps5',
                    'arrow'         =>  'icon icon-keyboard_arrow_down arrow-icon',
                    'child'         =>  [
                        // 'veriforder'        =>  [
                        //     'title'             =>  'Pengguna',
                        //     'url'               =>  $URL . '/dashboard/pengguna'
                        // ],
                    ]
                ], //end pengaturan
                
            ],

            // admin
            '2'         =>  [
                'dashboard'     =>  [
                    'title'         =>  'Dashboard',
                    'icon'          =>  'icon fa flaticon2-line-chart',
                    'type'          =>  '',
                    'url'           =>  $URL . '/dashboard'
                ],//end dashboard
            ]
        ];


        return $data;
    }

    public function vieworder(Request $request)
    {
        $id = $request->id;


        $vieworder = new \App\Http\Controllers\orders\manage;
        $vieworder = $vieworder->view(['order_id'=>$id]);
        $data = [
            "message"       =>  "",
            "view"            =>  $vieworder
        ];

        return response()->json($data, 200);
    }
}

