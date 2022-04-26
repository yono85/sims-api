<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\consumables as tblConsumables;
use App\consumable_outs as tblConsumableOuts;
use App\consumable_notifs as tblConsumableNotifs;
use App\Http\Controllers\config\index as Config;

class consumable extends Controller
{
    //
    public function main($request)
    {
        $Config = new Config;

        //
        $newid = $Config->createnewidnew([
            'value'         =>  tblConsumables::count(),
            'length'        =>  9
        ]);

        $token = md5($newid);

        $addnew                         =   new tblConsumables;
        $addnew->id                     =   $newid;
        $addnew->token                  =   $token;
        $addnew->name                   =   trim($request->name);
        $addnew->code                   =   trim($request->code);
        $addnew->type                   =   trim($request->type_tools);
        $addnew->quantity               =   str_replace(".", "", trim($request->quantity));
        $addnew->quantity_limit         =   trim($request->quantity_limit);
        $addnew->expired_date           =   trim($request->expired_date) === '' ? '' : $Config->changeFormatDate(trim($request->expired_date));
        $addnew->expired_status         =   trim($request->expired_status);
        $addnew->reminder_duration      =   trim($request->reminder_duration);
        $addnew->description            =   trim($request->description);
        $addnew->user_id                =   trim($request->user_id);
        $addnew->status                 =   1;
        $addnew->save();

        $data = [
            'id'            =>  $newid,
            'token'         =>  $token
        ];

        return $data;
    }


    public function update($request)
    {
        $Config = new Config;

        $update = tblConsumables::where([
            'id'            =>  trim($request->id)
        ])
        ->update([
            'code'                  =>  trim($request->code),
            'name'                  =>  trim($request->name),
            'type'                  =>  trim($request->type_tools),
            'quantity'              =>  trim($request->quantity),
            'quantity_limit'        =>  trim($request->quantity_limit),
            'expired_status'      =>  trim($request->expired_status),
            'expired_date'        =>  trim($request->expired_status) === '0' ? '' : $Config->changeFormatDate(trim($request->expired_date)),
            'description'           =>  trim($request->description),
            'reminder_duration'     =>  trim($request->reminder_duration)
        ]);
    }

    //consumable create order
    public function order($request)
    {
        $Config = new Config;

        $newid = $Config->createnewidnew([
            'value'         =>  tblConsumableOuts::count(),
            'length'        =>  15
        ]);

        $addnew                 =   new tblConsumableOuts;
        $addnew->id             =   $newid;
        $addnew->consumable_id  =   $request['id'];
        $addnew->poid           =   $request['poid'];
        $addnew->quantity       =   $request['quantity'];
        $addnew->user_id        =   $request['user_id'];
        $addnew->status         =   1;
        $addnew->save();


        $change = $this->changequantity($request);

        $data = [
            'message'       =>  'Data berhasil di tambahkan'
        ];

        return response()->json($data,200); 
    }

    public function checkstock($request)
    {
        $req_quantity = trim($request['quantity']);

        //
        $getdata = tblConsumables::where([
            'id'        =>  trim($request['id'])
        ])->first();

        
        $quantity = $getdata['quantity'];

        if( $quantity < $req_quantity)
        {
            $data = [
                'message'       =>  'Permintaan quantity melebihi stock yang ada',
                'focus'         =>  'quantity'
            ];

            return response()->json($data, 403);
        }


        if( $request['type'] == 'update')
        {
            $update = $this->updateorder([
                'id'        =>  $request['id'],
                'quantity'  =>  $req_quantity,
                'idout'     =>  $request['idout'],
                'stock'     =>  $getdata->quantity,
                'user_id'   =>  $request['user_id']
            ]);
    
            return $update;
        }


        $add = $this->order([
            'id'        =>  $request['id'],
            'quantity'  =>  $request['quantity'],
            'poid'      =>  $request['poid'],
            'user_id'   =>  $request['user_id'],
            'stock'     =>  $getdata->quantity
        ]);
        
        return $add;

        
    }


    //update consumable outs
    public function updateorder($request)
    {

        $getout = tblConsumableOuts::where([
            'id'        =>  $request['idout']
        ])->first();

        
        //update quantity out
        $updateouts = tblConsumableOuts::where([
            'id'        =>  $request['idout']
        ])
        ->update([
            'quantity'      =>  ($request['quantity'] + $getout->quantity)
        ]);

        $change = $this->changequantity($request);

        $data = [
            'message'       =>  'Data berhasil di update'
        ];

        return response()->json($data,200); 
    }

    //change quantity consumable
    public function changequantity($request)
    {

        //update quantity consumable
        $update = tblConsumables::where([
            'id'        =>  $request['id']
        ])
        ->update([
            'quantity'  =>  ($request['stock'] - $request['quantity'])
        ]);

        //check limit
        //untuk mengetahui jml quantity dengan stock limit
        $checklimit = $this->checklimit([
            'id'        =>  $request['id'],
            'user_id'   =>  $request['user_id']
        ]);


        
    }


    public function checklimit($request)
    {
        $check = tblConsumables::where([
            'id'        =>  $request['id']
        ])->first();

        if( $check->quantity_limit >= $check->quantity)
        {

            $checknotif = tblConsumableNotifs::where([
                'consumable_id'     =>  $request['id'],
                'status'            =>  1
            ])->count();

            if( $checknotif == 0)
            {

                $Config = new Config;

                $newid = $Config->createnewidnew([
                    'value'         =>  tblConsumableNotifs::count(),
                    'length'        =>  15
                ]);

                $token = md5($newid);

                $addnew                 =   new tblConsumableNotifs;
                $addnew->id             =   $newid;
                $addnew->token          =   $token;
                $addnew->token_notif    =   "";
                $addnew->consumable_id  =   $request['id'];
                $addnew->status         =   1;
                $addnew->save();   


                //add nofication
                $data = [
                    'from'  =>  $request['user_id'],
                    'to'    => '-1',
                    'type'  => 1, //cusumable
                    'level' => 5,
                    'content' => [
                        'title'     =>  'Limit Stock Consumable',
                        'text'      =>  'Stock ' . $check->name . ' sudah mencapai limit',
                        'link'      =>  '/dashboard/inventory/consumable',
                        'cmd'       =>  '',
                        'icon'      =>  'fa flaticon-open-box',
                        'token'        =>  $token
                    ]
                ];

                $addnotif = new \App\Http\Controllers\models\notification\index;
                $addnotif = $addnotif->main($data);

                //update Consumable Notif
                $update = tblConsumableNotifs::where([
                    'id'            =>  $newid
                ])
                ->update([
                    'token_notif'   =>  $addnotif['token']
                ]);
            }
            return "call notif limit consumable";
        }
    }

}