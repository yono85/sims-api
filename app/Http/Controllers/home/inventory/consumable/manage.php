<?php
namespace App\Http\Controllers\home\inventory\consumable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\consumables as tblConsumables;
use App\consumable_types as tblConsumableTypes;
use App\Http\Controllers\config\index as Config;

class manage extends Controller
{
    //
    public function create(Request $request)
    {
        $Config = new Config;

        //
        if( trim($request->type) == 'new' )
        {
            //
            $check = tblConsumables::where([
                ['name', 'like', '%' . trim($request->name) . '%' ],
                ['code', '=', trim($request->code)],
                ['type', '=', $request->type_tools]
            ])
            ->count();

            if( $check > 0 )
            {
                $data = [
                    'message'       =>  'Consumable dengan kode yang sama sudah diinput sebelumnya',
                    'focus'         =>  'name'
                ];

                return response()->json($data, 401);
            }


            //CHECK DURATION REMINDER
            if( trim($request->expired_status) == '1')
            {
                $datacheckduration = [
                    'expired'       =>  trim($request->expired_date),
                    'duration'      =>  trim($request->reminder_duration)
                ];
    
                $checkduration = new \App\Http\Controllers\models\reminder;
                $checkduration = $checkduration->checkduration($datacheckduration);
    
                if( $checkduration != '')
                {
                    return $checkduration;
                }
            }

            $addnew = new \App\Http\Controllers\models\consumable;
            $addnew = $addnew->main($request);

            //reminder
            $datareminder = [
                'expired'       =>  trim($request->expired_date),
                'duration'      =>  trim($request->reminder_duration),
                'link_id'       =>  $addnew['id'],
                'name'          =>  trim($request->name)
            ];

            $remind = new \App\Http\Controllers\models\reminder;
            $remind = $remind->consumable($datareminder);

            //
            $data = [
                'message'           =>  'Data consumable berhasil disimpan'
            ];

            return response()->json($data, 200);
        }


        $check = tblConsumables::where([
            ['id', '<>', trim($request->id)],
            ['name', 'like', '%' . trim($request->name) . '%' ],
            ['code', '=', trim($request->code)],
            ['type', '=', $request->type_tools]
        ])
        ->count();

        if( $check > 0 )
        {
            $data = [
                'message'       =>  'Consumable dengan kode yang sama sudah diinput sebelumnya',
                'focus'         =>  'name'
            ];

            return response()->json($data, 403);
        }


        //CHECK DURATION REMINDER
        if( trim($request->expired_status) == '1')
        {
            $datacheckduration = [
                'expired'       =>  trim($request->expired_date),
                'duration'      =>  trim($request->reminder_duration)
            ];

            $checkduration = new \App\Http\Controllers\models\reminder;
            $checkduration = $checkduration->checkduration($datacheckduration);

            if( $checkduration != '')
            {
                return $checkduration;
            }
        }

        $getconsumable = tblConsumables::where([
            'id'        =>  trim($request->id),
            'status'    =>  1
        ])->first();


        //UPDATE
        $update = new \App\Http\Controllers\models\consumable;
        $update = $update->update($request);

        if( strtotime($Config->changeFormatDate(trim($request->expired_date))) > strtotime( $getconsumable->expired_date) )
        {
            //REMINDER
            $datareminder = [
                'expired'       =>  trim($request->expired_date),
                'duration'      =>  trim($request->reminder_duration),
                'link_id'       =>  trim($request->id),
                'name'          =>  trim($request->name)
            ];
    
            $remind = new \App\Http\Controllers\models\reminder;
            $remind = $remind->consumable($datareminder);

        }

        $data = [
            'message'           =>  'Data consumable berhasil di sunting'
        ];

        return response()->json($data, 200);
    }

    public function show(Request $request)
    {
        $Config = new Config;

        $getdata = tblConsumables::from('consumables as c')
        ->select(
            'c.id', 'c.token', 'c.code', 'c.type', 'c.name', 'c.quantity', 'c.quantity_limit', 'c.expired_status', 'c.expired_date', 'c.quantity_limit', 'c.description', 'c.reminder_duration'
        )
        ->where([
            'c.id'      =>  $request->id
        ])->first();

        $gettype = tblConsumableTypes::where([
            'status'        =>  1
        ])->get();

        //
        $data   =   [
            'message'       =>  '',
            'response'      =>  [
                'id'                =>  $getdata->id,
                'token'             =>  $getdata->token,
                'code'              =>  $getdata->code,
                'name'              =>  $getdata->name,
                'type'              =>  $getdata->type,
                'type_list'         =>  $gettype,
                'quantity'          =>  [
                    'value'             =>  $getdata->quantity,
                    'limit'             =>  $getdata->quantity_limit
                ],
                'expired'         =>  [
                    'status'            =>  $getdata->expired_status,
                    'date'              =>  $getdata->expired_date === '' ? '' : $Config->roleFormatDate($getdata->expired_date)
                ],
                'description'           =>  $getdata->description,
                'reminder'              =>  $getdata->reminder_duration
            ]
        ];

        return response()->json($data, 200);
    }

}