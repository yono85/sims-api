<?php
namespace App\Http\Controllers\data\lembaga;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\lembaga_types as tblLembagaTypes;
use App\lembagas as tblLembagas;

class manage extends Controller
{

    //list type lembaga
    public function type(Request $request)
    {
        $Config = new Config;

        //
        $getdata = tblLembagaTypes::where([
            'status'      =>  1
        ])->get();

        $data = [
            'message'       =>  '',
            'response'      =>  [
                'list'          =>  $getdata
            ]
        ];

        return response()->json($data, 200);        
    }


    //LIST
    public function list(Request $request)
    {
        $Config = new Config;

        //
        $src = '%' . trim($request->q) . '%';
        $paging = trim($request->pg);

        //
        $getdata = tblLembagas::where([
            ['name', 'like', $src]
        ]);

        $count = $getdata->count();

        if( $count == 0)
        {
            $data = [
                'message'       =>  '',
                'response'      =>  ''
            ];

            return response()->json($data, 404);
        }

        
        $item = $Config->scroll(['paging'=>$paging])['paging_item'];
        $limit = $Config->scroll(['paging'=>$paging])['paging_limit'];
        $countpage = ceil( $count /$item);

        $gettable = $getdata->skip($limit)
        ->take($item)
        ->get();

        foreach($gettable as $row)
        {
            $list[] = [
                'id'        =>  $row->id,
                'label'     =>  $row->name
            ];
        }

        $data = [
            'message'       => '',
            'response'      =>  [
                'list'          =>  $list,
                'lastpaging'    =>  $countpage
            ]
        ];

        return response()->json($data, 200);

    }
}