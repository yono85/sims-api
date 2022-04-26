<?php
namespace App\Http\Controllers\home\menu;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;

class template extends Controller
{
    //LIST
    public function lists($request)
    {
        $data = [
            "admin"         =>  [
                '1'         =>  'absen,kalender',
                '2'         =>  'visit,task,gaji,rptabsen',
                '3'         =>  'karyawan,partner,penggajian,rptpenjualan,config',
                '4'         =>  'inventory'
            ],
            "owner"         =>  [
                '1'         =>  'absen,kalender',
                '2'         =>  'visit,task,gaji,rptabsen',
                '3'         =>  'karyawan,partner,rptpenjualan'
            ],
            "ceo"           =>  [
                '1'         =>  'absen,kalender',
                '2'         =>  'visit,task,gaji,rptabsen',
                '3'         =>  'karyawan,partner,rptpenjualan'
            ],
            "cfo"           =>  [
                '1'         =>  'absen,kalender',
                '2'         =>  'visit,task,gaji,rptabsen',
                '3'         =>  'karyawan,penggajian'
            ],
            "cmo"           =>  [
                '1'         =>  'absen,kalender',
                '2'         =>  'visit,task,gaji,rptabsen',
                '3'         =>  'partner,rptpenjualan'
            ],
            "spvm"          =>  [
                '1'         =>  'absen,kalender',
                '2'         =>  'visit,task,gaji,rptabsen',
                '3'         =>  'partner,rptpenjualan'
            ],
            "keuangan"           =>  [
                '1'         =>  'absen,kalender',
                '2'         =>  'visit,task,gaji,rptabsen',
                '3'         =>  'karyawan,penggajian'
            ],
            "kryw"           =>  [
                '1'         =>  'absen,kalender',
                '2'         =>  'visit,task,gaji,rptabsen',
            ]
            

        ];

        return $data[$request];
    }

    //ROLES
    public function roles($request)
    {
        //
        $level = $request->level;
        $sublevel = $request->sublevel;
        $groups = $request->groups;

        //ADMIN DAN OWNER
        if( $level == '10001' )
        {
            if( $sublevel == '0')
            {
                // ADMIN
                $list = $this->lists('admin');
            }
            else
            {
                // OWENER
                $list = $this->lists('owner');
            }
        }
        //DIREKSI
        else if( $level == '10002')
        {
            //CEO
            if($sublevel == '10001')
            {
                $list = $this->lists('ceo');
            }
            elseif ($sublevel == '10002')
            {
                $list = $this->lists('cfo');
            }
            elseif ($sublevel == '10003')
            {
                $list = $this->lists('cmo');
            }
            else
            {
                $list = $this->lists('coo');
            }
        }
        //SUPERVISOR
        elseif($level == '10004')
        {
            //MARKOM
            if($groups == '10003')
            {
                $list = $this->lists('spvm');
            }
        }
        //KARYAWAN
        else
        {
            //KEUANGAN
            if($groups == '10002')
            {
                $list = $this->lists('keuangan');
            }
            else
            {
                $list = $this->lists('kryw');
            }
        }

        return $list;
        
    }

    public function menu($request)
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
                'disabled'      =>  'false',
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
                'url'           =>  '/home/absen/report',
                'modal'         =>  '',
                'disabled'      =>  'false',
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
            ],
            'penggajian'         =>  [
                'title'         =>  'Penggajian Karyawan',
                'image'         =>  '/assets/icon/r$_pencil.png',
                'url'           =>  '#',
                'modal'         =>  '',
                'disabled'      =>  'true',
                'status'        =>  'active'
            ],
            'inventory'         =>  [
                'title'         =>  'Inventory',
                'image'         =>  '/assets/icon/r$_pencil.png',
                'url'           =>  '/home/inventory',
                'modal'         =>  '',
                'disabled'      =>  'false',
                'status'        =>  'active'
            ],
        ];

        $menu = explode(",", $request);
        foreach($menu as $row)
        {
            $child[] = $data[$row];
        }
        return $child;
    }
}