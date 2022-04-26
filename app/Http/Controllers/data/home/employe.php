<?php
namespace App\Http\Controllers\data\home;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\user_employes as tblUserEmployes;

class employe extends Controller
{
    //
    public function list(Request $request)
    {

        $getdata = tblUserEmployes::from('user_employes as ue')
        ->select(
            'ue.id', 'ue.name', 'ue.gender', 'ue.birth',
            'el.name as level_name', 'el.alias as level_alias'
        )
        ->leftJoin('employe_levels as el', function($join)
        {
            $join->on('el.id', '=', 'ue.level');
        })
        ->where([
            'ue.company_id' =>  trim($request->compid),
            'ue.status'     =>  1
        ])
        ->get();

        if( count($getdata) > 0)
        {
            $data = [
                'message'       =>  '',
                'response'      =>  $getdata
            ];
    
            return response()->json($data, 200);
        }


        // if null
        $data = [
            'message'       =>  'Data tidak ditemukan'
        ];

        return response()->json($data, 404);
    }
}