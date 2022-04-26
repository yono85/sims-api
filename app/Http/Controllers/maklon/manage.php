<?php
namespace App\Http\Controllers\maklon;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\user_companies as tblUserCompanies;

class manage extends Controller
{
    // LIST MAKLON
    public function list(Request $request)
    {
        $getdata = tblUserCompanies::from('user_companies as uc')
        ->select(
            'uc.id', 'uc.name'
        )
        ->where([
            'uc.type'               =>  3,
            'uc.produsen_id'        =>  trim($request->produsenid),
            'uc.status'             =>  1
        ]);


        if( $getdata->count() > 0 )
        {

            $getdata = $getdata->get();

            foreach($getdata as $row)
            {
                $list[] = [
                    'id'            =>  $row->id,
                    'name'          =>  $row->name
                ];
            }

            $data = [
                'list'      =>  $list
            ];

            return response()->json($data, 200);
        }


        return response()->json(['message'=>'Data tidak ditemukan'], 404);

    }


}