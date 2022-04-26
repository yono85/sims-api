<?php
namespace App\Http\Controllers\data\cs;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\users as tblUsers;


class manage extends Controller
{
    //
    public function list(Request $request)
    {
        $companyid = trim($request->companyid);

        $getdata = tblUsers::from('user_configs as uc')
        ->select(
            'u.id', 'u.name'
        )
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'uc.user_id');
        })
        ->where([
            'u.registers'       =>  1,
            'u.status'          =>  1
        ])
        ->whereIn('u.sub_level', ['1','2','3']);
        if( $companyid != ' -1')
        {
           $getdata = $getdata->where([
               'uc.company_id'=>$companyid
           ]);
        }
        $getdata = $getdata->get();

        if( count($getdata) > 0 )
        {
            foreach($getdata as $row)
            {
                $list[] = [
                    'id'            =>  $row->id,
                    'name'          =>  $row->name
                ];
            }

            $data = [
                'message'           =>  '',
                'list'              =>  $list
            ];
    
            return response()->json($data, 200);
        }

        $data = [
            'message'           =>  'Data tidak ditemukan'
        ];


        return response()->json($data, 404);
    }
}