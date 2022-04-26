<?php
namespace App\Http\Controllers\notification\reminder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\notifications as tblNotifications;
use App\Http\Controllers\config\index as Config;

class index extends Controller
{
    //
    public function main(Request $request)
    {
        $Config = new Config;

        //
        $level = trim($request->level);


        //
        $getdata = tblNotifications::from('notifications as n')
        ->select(
            'n.id', 'n.type', 'n.from_id', 'n.to_id', 'n.content', 'n.created_at'
        )
        ->where([
            'n.open'        =>  0
        ]);

        $count = $getdata->count();

        if( $count == 0 )
        {
            $data = [
                'message'       =>  'Tidak ada pemberitahuan'
            ];

            return response()->json($data, 404);
        }


        $gettable = $getdata->orderBy('n.id', 'desc')
        ->get();
        foreach($gettable as $row)
        {
            $content = json_decode($row->content);

            $list[] = [
                'id'        =>  $row->id,
                'from'      =>  $row->from_id,
                'to'        =>  $row->to_id,
                'content'       =>  [
                    'title'             =>  $content->title,
                    'text'           =>  $content->text,
                    'icon'              =>  $content->icon,
                    'cmd'               =>  $content->cmd,
                    'link'              =>  $content->link
                ],
                'date'          =>  $Config->timeago($row->created_at)
            ];
        }

        $data = [
            'message'       =>  '',
            'response'          =>  [
                'count'         =>  $count,
                'list'          =>  $list
            ]
        ];

        return response()->json($data, 200);
    }

    //notif permintaan SDM
    public function sdm($request)
    {

        $data = [
            'from'  =>  $request['user_id'],
            'to'    => '-1',
            'type'  => 1,
            'level' => 4,
            'content' => [
                'title'     =>  'Pengajuan (Tenaga Operasional)',
                'text'      =>  trim($request['text_sdm']),
                'link'      =>  '#',
                'cmd'       =>  'cmd-verif-modal-sdm',
                'icon'      =>  'sli_icon-user',
                'token'        =>  $request['token']
            ]
        ];


        $addnew = new \App\Http\Controllers\models\notification\index;
        $addnew = $addnew->main($data);
    }


    public function tools($request)
    {

        $data = [
            'from'  =>  $request['user_id'],
            'to'    => '-1',
            'type'  => 1,
            'level' => 5,
            'content' => [
                'title'     =>  'Pengajuan (Alat)',
                'text'      =>  trim($request['text_tools']),
                'link'      =>  '#',
                'cmd'       =>  'cmd-verif-modal-tools',
                'icon'      =>  'sli_icon-user',
                'token'        =>  $request['token']
            ]
        ];


        $addnew = new \App\Http\Controllers\models\notification\index;
        $addnew = $addnew->main($data);
    }

}