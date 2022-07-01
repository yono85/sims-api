<?php
namespace App\Http\Controllers\config;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\users as tblUsers;
use App\user_configs as tblUserConfigs;
use DB;

class aside extends Controller
{


    //view aside
    public function view(Request $request)
    {
        $userid = $request['id'];

        //
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

        $data = [
            'response'  =>  [
                'menu'          =>  $dt
            ]
        ];

        return response()->json($data, 200);
    }

    //
    public function test(Request $request)
    {
        $data = [
            'level'         =>  $request->level,
            'sublevel'      =>  $request->sublevel,
            'user_id'       =>  $request->id
        ];

        $create = $this->createaside($data);


        $update = tblUserConfigs::where([
            'user_id'           =>  $request->id
        ])
        ->update([
            'aside_menu'        =>  json_encode($create)
        ]);

        return $create;

    }

    public function viewSingle(Request $request)
    {
        $data = [
            'level'         =>  $request->level,
            'sublevel'      =>  $request->sublevel,
        ];

        $create = $this->createaside($data);

        return response()->json($create, 200);
    }

    //
    public function createaside($request)
    {
        $level = $request['level'];
        $sublevel = $request['sublevel'];


        $asidelevel = $level === '1' ? $this->dikmental() : $this->lembaga();

        $data = [
            'menu'      =>  $asidelevel[$sublevel]['menu'],
            'submenu'   =>  $asidelevel[$sublevel]['submenu']
        ];


        //getaside template
        $asidetemp = $this->tempaside($data);

        // $update = tblUserConfigs::where([
        //     'user_id'           =>  $request['user_id']
        // ])
        // ->update([
        //     'aside_menu'        =>  json_encode($asidetemp)
        // ]);

        return $asidetemp;
    }


    public function tempaside($request)
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
                'title'     =>  $vmenu['title'],
                'type'      =>  $vmenu['type'],
                'url'       =>  $vmenu['url'],
                'arrow'     =>  $vmenu['arrow'],
                'icon'      =>  $vmenu['icon'],
                'child'     =>  $child
            ];
        }

        //
        return $list;
    }

    //level dikmental admin
    public function dikmental()
    {
        $data = [
            '1'         =>  [ //administator
                'menu'          =>  'dashboard,hibah,page,pengaturan',
                'submenu'       =>  [
                    'hibah'         =>  'permintaan,lembaga,adminlembaga',
                    'page'          =>  'hibah,pengumuman',
                    'pengaturan'    =>  'pengguna'
                ]
            ],
            '2'         =>  [ //super admin
                'menu'          =>  'dashboard,hibah,page,pengaturan',
                'submenu'       =>  [
                    'hibah'         =>  'permintaan,lembaga,adminlembaga',
                    'page'          =>  'hibah,pengumuman',
                    'pengaturan'    =>  'pengguna'
                ]
            ]
        ];

        return $data;

    }


    //lembaga
    public function lembaga()
    {
        $data = [
            '1'         =>  [ //
                'menu'          =>  'dashboard,profilelmg,hibah',
                'submenu'       =>  [
                    'hibah'     =>  'pengajuan',
                    // 'pengaturan'    =>  'compro'
                ]
            ],
            '2'         =>  [ //
                'menu'          =>  'dashboard',
                'submenu'       =>  [
                    'hibah'     =>  'pengajuan',
                    // 'pengaturan'    =>  'compro'
                ]
            ],
            '3'         =>  [ //admin keuangan
                'menu'          =>  'dashboard',
                'submenu'       =>  [
                    'hibah'     =>  'pengajuan',
                    // 'pengaturan'    =>  'compro'
                ]
            ]
        ];

        return $data;
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
                'hibah'     =>  [
                    'title'         =>  'Pelayanan',
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
                'hrd'             =>  [
                    'title'         =>  'HRD',
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
                'page'             =>  [
                    'title'         =>  'News',
                    'icon'          =>  'icon fa flaticon2-browser-1',
                    'type'          =>  'collaps',
                    'arrow'         =>  'icon icon-keyboard_arrow_down arrow-icon',
                    'child'         =>  '',
                    'url'           =>  ''
                ], //end pengaturan
                'inventory'             =>  [
                    'title'         =>  'Inventori',
                    'icon'          =>  'icon sli_icon-social-dropbox',
                    'type'          =>  'collaps',
                    'arrow'         =>  'icon icon-keyboard_arrow_down arrow-icon',
                    'child'         =>  '',
                    'url'           =>  ''
                ], //end pengaturan
                'financial'             =>  [
                    'title'         =>  'Keuangan',
                    'icon'          =>  'icon sli_icon-social-dropbox',
                    'type'          =>  'collaps',
                    'arrow'         =>  'icon icon-keyboard_arrow_down arrow-icon',
                    'child'         =>  '',
                    'url'           =>  ''
                ], //end pengaturan
                'profilelmg'     =>  [
                    'title'         =>  'Profile Lembaga',
                    'icon'          =>  'icon fa flaticon2-website',
                    'type'          =>  '',
                    'arrow'         =>  '',
                    'url'           =>  '/dashboard/lembaga/profile',
                    'child'         =>  ''
                ],
        ];


        return $data;
        

    }

    //sub menu
    public function submenu()
    {

        $data = [
            'pengajuan'         =>  [ //marketing
                'title'             =>  'Pengajuan Hibah',
                'url'               =>  '/dashboard/pelayanan/pengajuan'
            ],
            'permintaan'         =>  [ //marketing
                'title'             =>  'Permintaan Hibah',
                'url'               =>  '/dashboard/pelayanan/permintaan'
            ],
            'lembaga'         =>  [ //marketing
                'title'             =>  'Lembaga',
                'url'               =>  '/dashboard/pelayanan/lembaga'
            ],
            'adminlembaga'         =>  [ //marketing
                'title'             =>  'Users',
                'url'               =>  '/dashboard/pelayanan/users'
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
                'title'             =>  'Shiping',
                'url'               =>  '/dashboard/shiping'
            ],
            'orderstock'        =>  [ //admin distributor
                'title'             =>  'Order Stock',
                'url'               =>  '/dashboard/orderstock'
            ],
            'pengguna'        =>  [ //pengaturan produsen, distributor
                'title'             =>  'Pengguna',
                'url'               =>  '/dashboard/manage/pengguna'
            ],
            'partner'        =>  [ //pengaturan produsen
                'title'             =>  'Partner',
                'url'               =>  '/dashboard/partner'
            ],
            'manageglobal'          =>  [ //pengaturan produsen, distributor
                'title'             =>  'Pengaturan Umum',
                'url'               =>  '/dashboard/pengaturan-umum'
            ],
            'stockproduct'          =>  [ //production produsen
                'title'             =>  'Stock Produk',
                'url'               =>  '/dashboard/stockproduct'
            ],
            'product'           =>  [ //production produsen
                'title'             =>  'Produk',
                'url'               =>  '/dashboard/product'
            ],
            'prepare'           =>  [ //production produsen
                'title'             =>  'Prepare',
                'url'               =>  '/dashboard/prepare'
            ],
            'inorder'           =>  [ //production produsen
                'title'             =>  'Order',
                'url'               =>  '/dashboard/financial/orders'
            ],
            'employe'           =>  [ //production produsen
                'title'             =>  'Karyawan',
                'url'               =>  '/dashboard/hrd/employe'
            ],
            'asset'           =>  [ //production produsen
                'title'             =>  'Aset',
                'url'               =>  '/dashboard/inventory/tools'
            ],
            'ordersdm'           =>  [ //production produsen
                'title'             =>  'Pengajuan Operasional',
                'url'               =>  '/dashboard/hrd/pengajuan-sdm'
            ],
            'ordertools'           =>  [ //production produsen
                'title'             =>  'Pengajuan Alat',
                'url'               =>  '/dashboard/inventory/pengajuan-alat'
            ],
            'consumable'           =>  [ //production produsen
                'title'             =>  'Consumable',
                'url'               =>  '/dashboard/inventory/consumable'
            ],
            'compro'                =>  [ //production produsen
                'title'                 =>  'Profil Lembaga',
                'url'                   =>  '/dashboard/lembaga/profile'
            ],
            'hibah'                 =>  [ //production produsen
                'title'                 =>  'Hibah',
                'url'                   =>  '/dashboard/news/hibah'
            ],
            'pengumuman'                =>  [ //production produsen
                'title'                 =>  'Pengumuman',
                'url'                   =>  '/dashboard/news/pengumuman'
            ],
            'orderconsum'                =>  [ //production produsen
                'title'                 =>  'Order Consumable',
                'url'                   =>  '/dashboard/inventory/consumable/out'
            ],
        ];


        return $data;
    }

}