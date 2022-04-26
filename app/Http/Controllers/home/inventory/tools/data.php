<?php
namespace App\Http\Controllers\home\inventory\tools;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\assets as tblAssets;
use App\asset_types as tblAssetTypes;
use App\Http\Controllers\config\index as Config;
use DB;

class data extends Controller
{
    //
    public function types(Request $request)
    {
        $getdata = tblAssetTypes::where([
            'status'        =>  1
        ])
        ->get();

        $data = [
            'message'       =>  '',
            'response'      =>  $getdata
        ];

        return response()->json($data,200);
    }

    //LIST MODAL
    public function listmodal(Request $request)
    {
        $Config = new Config;

        //
        $search = '%' . trim($request->search) . '%';
        $paging = trim($request->pg);

        //
        $getdata = tblAssets::from('assets as a')
        ->select(
            'a.id', 'a.name', 'a.code',
            DB::raw('IFNULL(po.name, "") as project')
        )
        ->leftJoin('po_orders as po', function($join)
        {
            $join->on('po.id', '=', 'project_id')
            ->where([
                'po.status'     =>  1
            ]);
        })
        ->where([
            ['a.name', 'like', $search],
            ['a.status', '=', 1]
        ]);

        $count = $getdata->count();
        
        //empty
        if($count == 0)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];
    
            return response()->json($data, 404);
        }

        $gettable = $getdata->orderBy('a.name', 'asc')
        ->take($Config->table(['paging'=>$paging])['paging_item'])
        ->skip($Config->table(['paging'=>$paging])['paging_limit'])
        ->get();

        foreach($gettable as $row)
        {
            $list[] = [
                'id'            =>  $row->id,
                'name'          =>  $row->name,
                'code'          =>  $row->code,
                'status'        =>  $row->project === '' ? 'true' : 'false',
                'error'         =>  $row->project === '' ? '' : $row->project
            ];
        }

        //
        $data = [
            'message'       =>  '',
            'response'          =>  $list
        ];

        return response()->json($data, 200);
    }
}