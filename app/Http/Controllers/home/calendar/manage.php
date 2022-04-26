<?php
namespace App\Http\Controllers\home\calendar;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\employe_attendances as tblEmployeAttendances;

class manage extends Controller
{
    public function attendance(Request $request)
    {
        $Config = new Config;

        $thismonth = date('Y-m', time());
        $employe_id = trim($request->id);

        $getdata = tblEmployeAttendances::from('employe_attendances as ea')
        ->select(
            'ea.id'
        )
        ->where([
            ['ea.employe_id', '=', $employe_id],
            ['ea.created_at', 'like', '%' . $thismonth .'%']
        ]);
        

        if( $getdata->count() == 0)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }


        //
        foreach($getdata->get() as $row)
        {
            $list[] = [
                'id'            =>  $row->id
            ];
        }

        // DATA
        $data = [
            'message'       =>  '',
            'response'      =>  $list
        ];

        return response()->json($data, 200);

        
    }
}