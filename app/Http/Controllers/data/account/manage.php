<?php
namespace App\Http\Controllers\data\account;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\user_sublevels as tblUserSublevels;
use App\Http\Controllers\config\index as Config;


class manage extends Controller
{
    //
    public function sublevel(Request $request)
    {

        $Config = new Config;

        //
        $level = $request->level;
        $sublevel = $request->sublevel;


        $getdata = tblUserSublevels::where([
            'level'         =>  trim($request->level),
            'status'        =>  1
        ]);
        if( $level == '1' && $sublevel > '1')
        {
            $getdata = $getdata->where([
                ['sub_level', '>', 2]
            ]);
        }
        $getdata = $getdata->get();


        if( count($getdata) > 0 )
        {

            foreach($getdata as $row)
            {
                $list[] = [
                    'id'        =>  $row->id,
                    'level'     =>  $row->level,
                    'sublevel'  =>  $row->sub_level,
                    'name'      =>  $row->name,
                    'description'   =>  $row->description
                ];
            }

            $response = [
                'message'       =>  '',
                'response'          => [
                    'list'          =>  $list
                ]
            ];

            return response()->json($response, 200);

        }



        return response()->json(
            [
                'message'       =>  'Data tidak ditemukan'
            ], 404
        );
    }
}