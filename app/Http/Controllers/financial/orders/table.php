<?php
namespace App\Http\Controllers\financial\orders;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use DB;

class table extends Controller
{
    //
    public function main(Request $request)
    {
        $Config = new Config;

        //
        $src = '%' . str_replace(';', '', trim($request->search) ) . '%';
        $paging = trim($request->paging);
        $sort = trim($request->sort_name);
        $progress = trim($request->selected_status);

        $getdata = DB::table('vw_ordernews as vo')
        ->select(
            'vo.id','vo.token', 'vo.code', 'vo.poid', 'vo.contentpo', 'vo.progress','vo.date',
            'ud.url as document'
        )
        ->leftJoin('upload_documents as ud', function($join)
        {
            $join->on('ud.link_id', '=', 'vo.id')
            ->where([
                'ud.type'       =>  1,
                'ud.subtype'    =>  0,
                'ud.status'     =>  1
            ]);
        })
        ->where([
            ['vo.search','like', $src],
            ['vo.status', '=', 1]
        ]);
        if( $progress != '-1')
        {
            $getdata = $getdata->where([
                'vo.progress'      =>  $progress
            ]);
        }

        $count = $getdata->count();

        if( $count == 0)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }


        $gettable = $getdata->orderBy('vo.id', $sort)
        ->take($Config->table(['paging'=>$paging])['paging_item'])
        ->skip($Config->table(['paging'=>$paging])['paging_limit'])
        ->get();

        foreach($gettable as $row)
        {

            $list[] = [
                'id'            =>  $row->id,
                'token'         =>  $row->token,
                'code'          =>  $row->code,
                'poid'          =>  $row->poid,
                'po'            =>  json_decode($row->contentpo),
                'progress'      =>  $row->progress,
                'date'          =>  $Config->timeago($row->date),
                'document'      =>  $row->document
            ];
        }


        $data = [
            'message'       =>  '',
            'response'      =>  [
                'list'          =>  $list,
                'paging'        =>  $paging,
                'total'         =>  $count,
                'countpage'     =>  ceil($count / $Config->table(['paging'=>$paging])['paging_item'] )
            ]
        ];

        return response()->json($data, 200);

    }
}