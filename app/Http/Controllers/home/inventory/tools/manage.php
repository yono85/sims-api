<?php
namespace App\Http\Controllers\home\inventory\tools;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\assets as tblAssets;
use App\asset_types as tblAssetTypes;
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
            $check = tblAssets::where([
                ['name', 'like', '%' . trim($request->name) . '%' ],
                ['type', '=', $request->type]
            ])
            ->count();

            if( $check > 0 )
            {
                $data = [
                    'message'       =>  'Nama alat dengan type yang sama telah diinput sebelumnya',
                    'focus'         =>  'name'
                ];

                return response()->json($data, 401);
            }


            //CHECK DURATION REMINDER
            if( trim($request->kalibrasi_status) == '1')
            {
                $datacheckduration = [
                    'expired'       =>  trim($request->kalibrasi_date),
                    'duration'      =>  trim($request->durasi_reminder)
                ];
    
                $checkduration = new \App\Http\Controllers\models\reminder;
                $checkduration = $checkduration->checkduration($datacheckduration);
    
                if( $checkduration != '')
                {
                    return $checkduration;
                }
            }

            $addnew = new \App\Http\Controllers\models\assets;
            $addnew = $addnew->main($request);

            //REMINDER
            $datareminder = [
                'expired'       =>  trim($request->kalibrasi_date),
                'duration'      =>  trim($request->durasi_reminder),
                'link_id'       =>  $addnew['id'],
                'name'          =>  trim($request->name)
            ];

            $remind = new \App\Http\Controllers\models\reminder;
            $remind = $remind->assettools($datareminder);

            //
            $data = [
                'message'           =>  'Data alat berhasil disimpan'
            ];

            return response()->json($data, 200);
        }

        
        //CHECK DURATION REMINDER
        if( trim($request->kalibrasi_status) == '1')
        {
            $datacheckduration = [
                'expired'       =>  trim($request->kalibrasi_date),
                'duration'      =>  trim($request->durasi_reminder)
            ];

            $checkduration = new \App\Http\Controllers\models\reminder;
            $checkduration = $checkduration->checkduration($datacheckduration);

            if( $checkduration != '')
            {
                return $checkduration;
            }
        }


        //CEK ASSETS 
        $getasset = tblAssets::where([
            'id'        =>  trim($request->asset_id),
            'status'    =>  1
        ])->first();


        // UPDATE TOOLS
        $update = new \App\Http\Controllers\models\assets;
        $update = $update->update($request);


        if( strtotime($Config->changeFormatDate(trim($request->kalibrasi_date))) > strtotime( $getasset->kalibrasi_date) )
        {
            //REMINDER
            $datareminder = [
                'expired'       =>  trim($request->kalibrasi_date),
                'duration'      =>  trim($request->durasi_reminder),
                'link_id'       =>  trim($request->asset_id),
                'name'          =>  trim($request->name)
            ];
    
            $remind = new \App\Http\Controllers\models\reminder;
            $remind = $remind->assettools($datareminder);

        }


        $data = [
            'message'           =>  'Data alat berhasil di sunting'
        ];

        return response()->json($data, 200);
    }


    public function show(Request $request)
    {
        $Config = new Config;

        $getdata = tblAssets::from('assets as a')
        ->select(
            'a.id', 'a.token', 'a.code', 'a.type', 'a.name', 'a.assesoris', 'a.quantity', 'a.kalibrasi_status', 'a.kalibrasi_date', 'a.description', 'a.reminder_duration'
        )
        ->where([
            'a.id'      =>  $request->id
        ])->first();

        $gettype = tblAssetTypes::where([
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
                'quantity'          =>  $getdata->quantity,
                'assesoris'         =>  $getdata->assesoris === '' ? '' : json_decode($getdata->assesoris),
                'kalibrasi'         =>  [
                    'status'            =>  $getdata->kalibrasi_status,
                    'date'              =>  $Config->roleFormatDate($getdata->kalibrasi_date)
                ],
                'description'           =>  $getdata->description,
                'reminder'              =>  $getdata->reminder_duration
            ]
        ];

        return response()->json($data, 200);
    }

    
}