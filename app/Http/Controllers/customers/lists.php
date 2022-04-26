<?php
namespace App\Http\Controllers\customers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\customers as tblData;
use App\Http\Controllers\config\index as Config;
use App\Http\Controllers\access\manage as Refresh;
use DB;

class lists extends Controller
{
    //
    public function widget(Request $request)
    {
        //
        $Config = new Config;


        // $Refresh = new Refresh;
        // $Refresh = $Refresh->refresh();


        // $dataaccount = [
        //     'type'          =>  'key',
        //     'token'         =>  $request->header('key')
        // ];

        
        // $getaccount = new \App\Http\Controllers\account\index;
        // $getaccount = $getaccount->viewtype($dataaccount);

        //
        $getlist = tblData::from('customers as c')
        ->where([
            ['c.status', '=', 1],
            ['c.search', 'like', '%' . $request->src . '%'],
            ['c.company_id', '=', $request->companyid]
        ]);
        if( $request->sublevel != '1' )
        {
            $getlist = $getlist->where([
                'c.user_id'     =>  trim($request->id)
            ]);
        }
        
        $count = $getlist->count();
        


        //
        if( $count > 0 )
        {

            $paging = trim($request->pg);
            $item = $Config->scroll(['paging'=>$paging])['paging_item'];
            $limit = $Config->scroll(['paging'=>$paging])['paging_limit'];

            $getdata = $getlist->orderBy('c.name', 'asc')
            ->take($item)
            ->skip($limit)
            ->get();

            foreach($getdata as $row)
            {
                $list[] = [
                    'id'                =>  $row->id,
                    'name'              =>  $row->name,
                    'phone'             =>  $row->phone
                ];
            }


            $data = [
                "message"           =>  "",
                "response"          =>  [
                    "list"          =>  $list,
                    "countpage"         =>  ceil($count / $item)
                ]
            ];

            return response()->json($data, 200);
        }
        else
        {
           $data = [
               "message"            =>  "Data tidak ditemukan",
               "response"           =>  ""
           ];

           return response()->json($data, 404);
        }

    }
}