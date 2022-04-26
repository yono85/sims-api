<?php
namespace App\Http\Controllers\data\company;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\user_companies as tblUserCompanies;

class manage extends Controller
{
    //
    public function list(Request $request)
    {

        //
        $getdata = tblUserCompanies::from('user_companies as uc')
        ->select(
            'uc.id', 'uc.name', 'uc.type', 'ct.name as type_label'
        )
        ->leftJoin('company_types as ct', function($join)
        {
            $join->on('ct.id', '=', 'uc.type');
        })
        ->where([
            ['uc.status', '=', 1]
        ])->get();


        if( count($getdata) > 0 )
        {


            foreach($getdata as $row)
            {
                $list[] = [
                    'id'            =>  $row->id,
                    'name'          =>  $row->name,
                    'type'          =>  $row->type,
                    'type_label'    =>  $row->type_label
                ];
            }


            $data = [
                'message'       =>  '',
                'list'          =>  $list
            ];

            return response()->json($data, 200);
        }


        $data = [
            'message'           =>  'Data tidak ditemukan'
        ];


        return response()->json($data, 404);
    }
}