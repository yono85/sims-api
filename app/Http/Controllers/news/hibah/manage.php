<?php
namespace App\Http\Controllers\news\hibah;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\hibahs as tblHibahs;

class manage extends Controller
{
    //
    public function create(Request $request)
    {
        $Config = new Config;

        //
        $type = trim($request->type);

        if( $type == 'add')
        {
            //
            $addnew = new \App\Http\Controllers\models\hibah;
            $addnew = $addnew->new($request);

            $data = [
                'message'       =>  'Pengumuma berhasil disimpan'
            ];
    
            return response()->json($data, 200);
        }

        //SUNTING

        $data = [
            'message'       =>  'Pengumuma berhasil diperbaharui'
        ];

        return response()->json($data, 200);
    }

    //
    public function view(Request $request)
    {
        $Config = new Config;

        //
        $getdata = tblHibahs::where([
            'id'        =>  trim($request->id)
        ]);

        $count = $getdata->count();

        if($count == 0)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 200);
        }

        //
        $viewdata = $getdata->first();

        $data = [
            'message'       =>  '',
            'response'      =>  [
                'id'            =>  $viewdata->id,
                'name'          =>  $viewdata->name,
                'type'          =>  $viewdata->type,
                'uraian'        =>  $viewdata->text_code,
                'start'         =>  $Config->roleFormatDate($viewdata->start_date),
                'end'           =>  $Config->roleFormatDate($viewdata->end_date),
                'publish'       =>  $viewdata->publish
            ]
        ];

        return response()->json($data, 200);

    }
}