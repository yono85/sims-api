<?php
namespace App\Http\Controllers\companies;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\user_companies as tblUserCompanies;

class index extends Controller
{
    //
    public function lists(Request $request)
    {

        $getdata = tblUserCompanies::where([
            'produsen_id'   =>  trim($request->compid),
            'verify'        =>  1,
            'status'        =>  1
        ])->get();

        if( count($getdata) > 0 )
        {
            foreach($getdata as $row)
            {
                $list[] = [
                    'id'        =>  $row->id,
                    'type'      =>  $row->type,
                    'name'      =>  $row->name
                ];
            }

            $data = [
                'message'      =>   '',
                'list'          =>  $list
            ];
            
            return response()->json($data, 200);
        }
        else
        {

            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];
    
    
            return response()->json($data,404);
        }
    }
}