<?php
namespace App\Http\Controllers\testing\home;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class reportatt extends Controller
{
    //
    public function main(Request $request)
    {

        $id = trim($request->id);
        $date = trim($request->date);


        $getdata = DB::table('vw_user_employes as ue')
        ->select(
            'ue.id','ue.name','ue.gender','ue.status',
            DB::raw('IFNULL(cta.total, 0) as total_time'),
            DB::raw('IFNULL(ctas.total, 0) as total_sick')
        )
        ->leftJoin('count_time_attendances as cta', function($join) use ($date)
        {
            $join->on('cta.employe_id', '=', 'ue.id')
            ->where([
                ['cta.date', 'like', '%' . $date . '%']
            ]);
        })
        ->leftJoin('count_sick_attendances as ctas', function($join) use ($date)
        {
            $join->on('ctas.employe_id', '=', 'ue.id')
            ->where([
                ['ctas.date', 'like', '%' . $date . '%']
            ]);
        })
        ->where([
            ['ue.status', '=', 1],
            ['ue.id', '=', $id]
        ]);

        if($getdata->count() > 0)
        {
            foreach($getdata->get() as $row)
            {
                $list[] = [
                    'id'        =>  $row->id,
                    'name'      =>  $row->name,
                    'total_time'    =>  $row->total_time,
                    'total_sick'    =>  $row->total_sick
                ];
            }

            $data = [
                'list'      =>  $list
            ];

            return response()->json($data,200);
        }


        return response()->json([
            'message'       =>  'Not Found!'
        ], 404);

    }
}