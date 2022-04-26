<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\reminders as tblReminders;
use DB;

class reminder extends Controller
{
    //
    public function main($request)
    {
        $Config = new Config;

        //
        $newid = $Config->createnewidnew([
            'value'         =>  tblReminders::count(),
            'length'        =>  15
        ]);

        $token  = md5($newid);

        $addnew                 =   new tblReminders;
        $addnew->id             =   $newid;
        $addnew->token          =   $token;
        $addnew->type           =   $request['type']; //1. doc employe, 2. kalibrasi tools, 3. expired consum
        $addnew->subtype        =   $request['subtype']; //
        $addnew->notification   =   json_encode($request['notification']);
        $addnew->link_id        =   $request['link_id'];
        $addnew->token_notif    =   '';
        $addnew->date           =   $request['date'];
        $addnew->sending        =   0;
        $addnew->status         =   1;
        $addnew->save();

    }

    //check durasi expired
    public function checkduration($request)
    {
        $Config = new Config;


        $expired = $Config->changeFormatDate($request['expired']);
        $duration = $request['duration'];

        $days = date('Y-m-d', time());
        $change = date('Y-m-d', strtotime($expired .  '-'.$duration.' day') );

        if( strtotime($days) >= strtotime($change) )
        {
            $data = [
                'message'       =>  'Expired date tidak boleh sama atau dibawah tanggal sekarang'
            ];

            return response()->json($data, 403);
        }
        
        return "";
    }


    //DOCUMENT EMPLOYE
    public function documentemploye($request)
    {

        $Config = new Config;

        //
        $type = trim($request->document_type);
        $sk_type = trim($request->skil_type);
        $exp = $Config->changeFormatDate(trim($request->expired_date));
        $duration = trim($request->durasi_reminder);
        $date = date('Y-m-d', strtotime($exp . '-' . $duration . ' day' ) );
        
        //
        $getemploye = DB::table('user_employes')
        ->where([
            'id'        =>  trim($request->employe_id)
        ])
        ->first();

        if( $type == '1')
        {
            $title = 'Dokumen KTP Expired!';
            $text = 'KTP an. ' . $getemploye->name . ' akan berakhir masa berlakunya';
            $level = 1;
        }
        else if( $type == '2')
        {
            $title = 'Dokumen MCU Expired!';
            $text = 'MCU an. ' . $getemploye->name . ' akan berakhir masa berlakunya';
            $level = 2;
        }
        else
        {
            $getsk = DB::table('doc_emp_subtypes')
            ->where([
                'id'        =>  $sk_type
            ])->first();

            $title = 'Dokumen SK Expired!';
            $text = 'SK '.$getsk->name.' an. ' . $getemploye->name . ' akan berakhir masa berlakunya';
            $level = 3;
        }

        //DATA REMINDER
        $data = [
            'type'      =>  1, //document employe
            'subtype'  =>  $level,
            'notification'  =>  [
                'from'          =>  -1,
                'to'            =>  -1,
                'type'          =>  1, //admin
                'level'         =>  4, //HRD
                'content'           =>  [
                    'title'             =>  $title,
                    'text'              =>  $text,
                    'link'              =>  '/dashboard/hrd/employe',
                    'cmd'               =>  '',
                    'icon'              =>  'sli_icon-note',
                    'token'             =>  ''
                ]
            ],
            'link_id'       =>  trim($request->employe_id),
            'date'          =>  $date
        ];


        //OPEN NOTIFICATION
        $datareminder = $this->remindernotif([
            'type'      =>  1,
            'subtype'   =>  $type,
            'link_id'   =>  trim($request->employe_id)
        ]);

        // ADD REMINDER
        $addnew = $this->main($data);

    }


    // REMINDER ASSET TOOLS
    public function assettools($request)
    {
        $Config = new Config;

        $exp = $Config->changeFormatDate($request['expired']);
        $duration = $request['duration'];
        $date = date('Y-m-d', strtotime($exp . '-' . $duration . ' day' ) );

        //DATA REMINDER
        $data = [
            'type'      =>  2, //asset tools
            'subtype'   =>  0,
            'notification'  =>  [
                'from'          =>  -1,
                'to'            =>  -1,
                'type'          =>  1, //admin
                'level'         =>  5, //Inventory
                'content'           =>  [
                    'title'             =>  'Kalibrasi Alat Expired!',
                    'text'              =>  $request['name'] . ' akan habis masa kalibrasinya',
                    'link'              =>  '/dashboard/inventory/tools',
                    'cmd'               =>  '',
                    'icon'              =>  'sli_icon-social-dropbox',
                    'token'             =>  ''
                ]
            ],
            'link_id'       =>  $request['link_id'],
            'date'          =>  $date
        ];

        //OPEN NOTIFICATION
        $datareminder = $this->remindernotif([
            'type'      =>  2,
            'subtype'   =>  0,
            'link_id'   =>  $request['link_id']
        ]);

        $addnew = $this->main($data);
    }

    // REMINDER CONSUMABLE
    public function consumable($request)
    {
        $Config = new Config;

        $exp = $Config->changeFormatDate($request['expired']);
        $duration = $request['duration'];
        $date = date('Y-m-d', strtotime($exp . '-' . $duration . ' day' ) );

        //DATA REMINDER
        $data = [
            'type'      =>  3, //consumable
            'subtype'   =>  0,
            'notification'  =>  [
                'from'          =>  -1,
                'to'            =>  -1,
                'type'          =>  1, //admin
                'level'         =>  5, //Inventory
                'content'           =>  [
                    'title'             =>  'Consumable Expired!',
                    'text'              =>  $request['name'] . ' akan habis masa berlakunya',
                    'link'              =>  '/dashboard/inventory/consumable',
                    'cmd'               =>  '',
                    'icon'              =>  'sli_icon-social-dropbox',
                    'token'             =>  ''
                ]
            ],
            'link_id'       =>  $request['link_id'],
            'date'          =>  $date
        ];

        //OPEN NOTIFICATION
        $datareminder = $this->remindernotif([
            'type'      =>  3,
            'subtype'   =>  0,
            'link_id'   =>  $request['link_id']
        ]);

        $addnew = $this->main($data);
    }

    // DOCUMENT COMPANIES
    public function documencompanies($request)
    {
        $Config = new Config;

        $exp = $Config->changeFormatDate($request['expired']);
        $duration = $request['duration'];
        $date = date('Y-m-d', strtotime($exp . '-' . $duration . ' day' ) );

        //DATA REMINDER
        $data = [
            'type'      =>  4, //document companies
            'subtype'   =>  $request['subtype'],
            'notification'  =>  [
                'from'          =>  -1,
                'to'            =>  -1,
                'type'          =>  1, //admin
                'level'         =>  2, //Super Admin
                'content'           =>  [
                    'title'             =>  'Dokumen Perusahaan Expired!',
                    'text'              =>  $request['name'] . ' akan habis masa berlakunya',
                    'link'              =>  '/dashboard/manage/company/profile',
                    'cmd'               =>  '',
                    'icon'              =>  'sli_icon-social-dropbox',
                    'token'             =>  ''
                ]
            ],
            'link_id'       =>  $request['link_id'],
            'date'          =>  $date
        ];

        //OPEN NOTIFICATION
        $datareminder = $this->remindernotif([
            'type'      =>  4,
            'subtype'   =>  $request['subtype'],
            'link_id'   =>  $request['link_id']
        ]);

        $addnew = $this->main($data);
    }




    // REMINDER GET TOKEN NOTIF
    public function remindernotif($request)
    {
        $getreminder = tblReminders::where([
            'type'          =>  $request['type'],
            'subtype'       =>  $request['subtype'],
            'link_id'       =>  $request['link_id'],
            'status'        =>  1
        ])
        ->orderBy('id', 'desc')
        ->first();
        
        if( $getreminder != null)
        {
            $opennotif = new \App\Http\Controllers\models\notification\index;
            $opennotif = $opennotif->open([
                'token'     =>  $getreminder->token_notif
            ]);
        }

    }

    // DELETE REMINDER
    public function delete($request)
    {
        //delete notif
        $datareminder = $this->remindernotif([
            'type'      =>  4,
            'subtype'   =>  $request['subtype'],
            'link_id'   =>  $request['link_id']
        ]);

        $update = tblReminders::where([
            'type'          =>  $request['type'],
            'subtype'       =>  $request['subtype'],
            'link_id'       =>  $request['link_id'],
            'status'        =>  1
        ])
        ->update([
            'status'        =>  0
        ]);
    }
}