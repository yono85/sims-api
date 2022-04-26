<?php
namespace App\Http\Controllers\home\menu;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\home\menu\template as Temps;
use App\user_employes as tblUserEmployes;
use App\employe_menus as tblEmployeMenus;

class manage extends Controller
{
    //
    public function createMenus($request)
    {
        $temp = new Temps;

        //jika level owner
        $id = trim($request['employe_id']);

        $getdata = tblUserEmployes::where([
            'id'        =>  $id
        ])
        ->first();

        $list = $temp->roles($getdata);

        //CEK EMPLOYE
        $cek = tblEmployeMenus::where([
            'employe_id'        =>  $id
        ])->count();

        if( $cek == 0)
        {
            //add on table
            $addMenu = new \App\Http\Controllers\models\employe;
            $addMenu->menus([
                'id'        =>  $id,
                'menu'      =>  $list
            ]);
        }

        // $data = [
        //     'message'       =>  '',
        //     'list'          =>  $list
        // ];

        // return response()->json($data,200);
    }


    public function getMenus(Request $request)
    {
        $temp = new Temps;

        //request
        $id = trim($request->id);
        $getdata = tblEmployeMenus::where([
            'employe_id'        =>  $id
        ])->first();

        $list = json_decode($getdata->menu);
        $no = 1;
        foreach($list as $row)
        {
            // $submenu = explode(',', $row);
            // $child = [];
            // foreach($submenu as $rowx)
            // {
            //     $child[] = $rowx;
            // }

            $menu[] = [
                'li'    =>  $no++,
                'menu'  =>  $temp->menu($row)
            ];
        }

        $data = [
            'response'            =>  $menu
        ];

        return response()->json($data,200);
    }


    public function createIn(Request $request)
    {
        $temp = new Temps;

        //jika level owner
        $id = trim($request->id);

        $getdata = tblUserEmployes::where([
            'id'        =>  $id
        ])
        ->first();

        $list = $temp->roles($getdata);

        //CEK EMPLOYE
        $cek = tblEmployeMenus::where([
            'employe_id'        =>  $id
        ])->count();

        if( $cek == 0)
        {
            //add on table
            $addMenu = new \App\Http\Controllers\models\employe;
            $addMenu->menus([
                'id'        =>  $id,
                'menu'      =>  $list
            ]);
        }

        $data = [
            'message'       =>  '',
            'list'          =>  $list
        ];

        return response()->json($data,200);
    }
}