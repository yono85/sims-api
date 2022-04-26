<?php
namespace App\Http\Controllers\data;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use DB;

class consumable extends Controller
{
    //
    public function main(Request $request)
    {
        $Config = new Config;

        //
        $src = '%' . trim($request->q) . '%';
        $paging = trim($request->p);


        //
        $getdata = DB::table('vw_consumable')
        ->select(
            'id', 'name as label'
        )
        ->where([
            ['name', 'like', $src]
        ]);
        
        $count = $getdata->count();

        if( $count == 0)
        {
            $data = [
                'message'       =>  '',
                'response'      =>  '',
                'cek'           =>  $count
            ];

            return response()->json($data, 404);
        }


        //
        $gettable = $getdata->get();

        $item = $Config->scroll(['paging'=>$paging])['paging_item'];
        $limit = $Config->scroll(['paging'=>$paging])['paging_limit'];
        $countpage = ceil( $count /$item);

        $data = [
            'message'       =>  '',
            'response'      =>  [
                'list'          =>  $gettable,
                'lastpaging'    =>  $countpage         
            ],
            'cek'           =>  $count
        ];

        return response()->json($data, 200);
        
    }
}