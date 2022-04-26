<?php
namespace App\Http\Controllers\home\menu;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\Http\Controllers\account\index as Account;
use App\user_employes as tblUserEmployes;
use App\employe_menus as tblEmployeMenus;

class index extends Controller
{
    //
    public function main(Request $request)
    {


        $CekAccount = new Account;
        $account = $CekAccount->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);

        $data = [
            'message'       =>  '',
            'response'      =>  $this->temp($request->level)
        ];

        return response()->json($data, 200);
    }

    private function temp($request)
    {
        $level = $this->level($request);

        $li = 1;
        foreach($level as $row)
        {
            $child[] = [
                'li'    =>  $li++,
                'menu'  =>  $this->menu($row)
            ];
        }

        return $child;
    }

    private function level($request)
    {
        $data = [
            "10001"         =>  [
                '1'         =>  'absen,kalender',
                '2'         =>  'visit,task,gaji,rptabsen',
                '3'         =>  'karyawan,partner,rptpenjualan,config',
            ],
            "10002"         =>  [
                '1'         =>  'absen,kalender',
                '2'         =>  'visit,task,gaji'
            ]
        ];


        return $data[$request];
    }

    private function menu($request)
    {
        $Config = new Config;
        $data = [
            'kalender'      =>  [
                'title'         =>  'Kalender Absen',
                'image'         =>  '',
                'url'           =>  '/home/calendar',
                'incontent'     =>  [
                    'top'           =>  $Config->namahari(date('Y-m-d'), time()),
                    'bottom'        =>  date('d', time())
                ],
                'modal'         =>  '',
                'disabled'      =>  'false',
                'status'        =>  'active'
            ],
            'absen'         =>  [
                'title'         =>  'Absen',
                'image'         =>  '/assets/icon/r$_find.png',
                'url'           =>  '#',
                'modal'         =>  'callAbsen',
                'disabled'      =>  'false',
                'status'        =>  'active'
            ],
            'task'         =>  [
                'title'         =>  'Task',
                'image'         =>  '/assets/icon/r$_task.png',
                'url'           =>  '/home/task',
                'modal'         =>  '',
                'disabled'      =>  'true',
                'status'        =>  'active'
            ],
            'rptpenjualan'         =>  [
                'title'         =>  'Report Penjualan',
                'image'         =>  '/assets/icon/r$_chart.png',
                'url'           =>  '#',
                'modal'         =>  '',
                'disabled'      =>  'true',
                'status'        =>  'active'
            ],
            'partner'         =>  [
                'title'         =>  'Partner',
                'image'         =>  '/assets/icon/r$_partner.png',
                'url'           =>  '#',
                'modal'         =>  '',
                'disabled'      =>  'true',
                'status'        =>  'active'
            ],
            'karyawan'         =>  [
                'title'         =>  'Karyawan',
                'image'         =>  '/assets/icon/r$_book.png',
                'url'           =>  '/home/employe',
                'modal'         =>  '',
                'disabled'      =>  'false',
                'status'        =>  'active'
            ],
            'rptabsen'         =>  [
                'title'         =>  'Report Absen',
                'image'         =>  '/assets/icon/r$_chart.png',
                'url'           =>  '#',
                'modal'         =>  '',
                'disabled'      =>  'true',
                'status'        =>  'active'
            ],
            'dashboard'         =>  [
                'title'         =>  'Dashboard',
                'image'         =>  '/assets/icon/r$_chart.png',
                'url'           =>  '#',
                'modal'         =>  '',
                'disabled'      =>  'true',
                'status'        =>  'active'
            ],
            'gaji'         =>  [
                'title'         =>  'Gaji',
                'image'         =>  '/assets/icon/r$_pencil.png',
                'url'           =>  '#',
                'modal'         =>  '',
                'disabled'      =>  'true',
                'status'        =>  'active'
            ],
            'visit'         =>  [
                'title'         =>  'Visit',
                'image'         =>  '/assets/icon/r$_keynote.png',
                'url'           =>  '/home/visit',
                'modal'         =>  '',
                'disabled'      =>  'true',
                'status'        =>  'active'
            ],
            'config'         =>  [
                'title'         =>  'Pengaturan',
                'image'         =>  '/assets/icon/r$_gear.png',
                'url'           =>  '/home/pengaturan',
                'modal'         =>  '',
                'disabled'      =>  'true',
                'status'        =>  'active'
            ]
        ];

        $menu = explode(",", $request);
        foreach($menu as $row)
        {
            $child[] = $data[$row];
        }
        return $child;
    }


    

    
}