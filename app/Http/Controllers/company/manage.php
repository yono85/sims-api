<?php
namespace App\Http\Controllers\company;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\asset_types as tblAssetTypes;
use App\doc_emp_subtypes as tblDocEmpSubtypes;
use App\companies as tblCompanies;
use App\document_companies as tblDocumentCompanies;
use App\doc_comp_types as tblDocCompTypes;
use App\Http\Controllers\config\index as Config;

class manage extends Controller
{
    //label
    public function label(Request $request)
    {
        $Config = new Config;

        //
        $id = trim($request->id);
        $name = trim($request->name);

        //
        $update = tblCompanies::where([
            'id'        =>  $id
        ])
        ->update([
            'name'      =>  $name
        ]);

        $data = [
            'message'       =>  'Data berhasil diperbaharui'
        ];

        return response()->json($data, 200);
    }

    //contact
    public function contact(Request $request)
    {
        $Config = new Config;

        //
        $id = trim($request->id);
        $phone = trim($request->contact_phone);
        $email = trim($request->contact_email);
        $website = trim($request->contact_website);

        $contact = [
            'phone'     =>  $phone,
            'email'     =>  $email,
            'website'   =>  $website
        ];

        //
        $update = tblCompanies::where([
            'id'        =>  $id
        ])
        ->update([
            'contact'       =>  json_encode($contact)
        ]);

        $data = [
            'message'       =>  'Data berhasil diperbaharui'
        ];

        return response()->json($data, 200);
    }

    //OWNER
    public function owner(Request $request)
    {
        $Config = new Config;

        //
        $id = trim($request->id);
        $name = trim($request->owner_name);
        $phone = trim($request->owner_email);
        $email = trim($request->owner_phone);

        $owner = [
            'name'      =>  $name,
            'phone'     =>  $phone,
            'email'     =>  $email
        ];

        //
        $update = tblCompanies::where([
            'id'        =>  $id
        ])
        ->update([
            'owner'       =>  json_encode($owner)
        ]);

        $data = [
            'message'       =>  'Data berhasil diperbaharui'
        ];

        return response()->json($data, 200);
    }

    // ADDRESS
    public function address(Request $request)
    {
        $Config = new Config;

        //
        $id = trim($request->id);
        $address = trim($request->address);
        $address_array = explode(",", trim($request->address_array));
        $address_list = explode(", ", trim($request->city));
        $kodepos = trim($request->address_kodepos);


        $address = [
            'name'          =>  $address,
            'provinsi'      =>  $address_list[2],
            'city'          =>  $address_list[1],
            'kecamatan'     =>  $address_list[0]
        ];

        //
        $update = tblCompanies::where([
            'id'        =>  $id
        ])
        ->update([
            'address'       =>  json_encode($address),
            'provinsi'      =>  $address_array[0],
            'city'          =>  $address_array[1],
            'kecamatan'     =>  $address_array[2],
            'kodepos'       =>  $kodepos
        ]);

        $data = [
            'message'       =>  'Data berhasil diperbaharui'
        ];

        return response()->json($data, 200);
    }


    //DOCUMENT
    public function document(Request $request)
    {

        // CHECK DURASI REMINDER DATE
        $check = tblDocumentCompanies::where([
            ['type', '=', trim($request->document_type)],
            ['name', '=', trim($request->document_label)],
            ['status', '=', 1]
        ])->count();

        if($check > 0 )
        {
            $data = [
                'message'       =>  'Nama dokumen dengan jenis yang sama telah di input sebelumnya',
                'focus'         =>  'document_label'
            ];

            return response()->json($data, 401);
        }

        $file = $request->file('file');
        // 
        $dataupload = [
            'name'              =>  trim($request->document_label),
            'file'              =>  $file,
            'url'               =>  '/upload/documents/companies/',
            'path'              =>  '/documents/companies/',
            'user_id'           =>  trim($request->user_id),
            'duration'          =>  trim($request->duration),
            'expired'           =>  trim($request->expired_date),
            'type'              =>  trim($request->document_type)
        ];


        //CHECK DURATION REMINDER
        if( trim($request->duration) == '1')
        {
            $datacheckduration = [
                'expired'       =>  trim($request->expired_date),
                'duration'      =>  trim($request->duration)
            ];

            $checkduration = new \App\Http\Controllers\models\reminder;
            $checkduration = $checkduration->checkduration($datacheckduration);

            if( $checkduration != '')
            {
                return $checkduration;
            }
        }

        $addnew = new \App\Http\Controllers\models\upload;
        $addnew = $addnew->companies($dataupload);

        $gettype = tblDocCompTypes::where([
            'id'        =>  trim($request->document_type),
            'status'    =>  1    
        ])->first();

        //REMINDER
        $datareminder = [
            'expired'       =>  trim($request->expired_date),
            'duration'      =>  trim($request->duration),
            'link_id'       =>  $addnew['id'],
            'name'          =>  $gettype->name . ' ' . trim($request->document_label),
            'subtype'       =>  trim($request->document_type)
        ];

        $remind = new \App\Http\Controllers\models\reminder;
        $remind = $remind->documencompanies($datareminder);

        //list
        $list = new \App\Http\Controllers\manage\data;
        $list = $list->datadocument();

        //
        $data = [
            'message'       =>  'Dokumen berhasil di tambahkan',
            'list'          =>  $list
        ];

        return response()->json($data, 200);
    }


    //TYPE TOOLS
    public function typetools(Request $request)
    {

        $check = tblAssetTypes::where([
            ['name', 'like', '%' . trim($request->label) . '%'],
            ['status', '=', 1]
        ])->count();

        if( $check > 0 )
        {
            $data = [
                'message'       =>  'Jenis alat sudah ada sebelumnya'
            ];

            return response()->json($data,401);
        }


        //addnew
        $addnew = new \App\Http\Controllers\models\assets;
        $addnew = $addnew->types($request);

        $list = new \App\Http\Controllers\manage\data;
        $list = $list->datatools();

        $data = [
            'message'       =>  'Jenis Alat berhasil ditambahkan',
            'list'          =>  $list
        ];

        return response()->json($data,200);
    }


    //TYPE SK
    public function typesk(Request $request)
    {

        $check = tblDocEmpSubtypes::where([
            ['name', 'like', '%' . trim($request->label) . '%'],
            ['status', '=', 1]
        ])->count();

        if( $check > 0 )
        {
            $data = [
                'message'       =>  'Jenis SK sudah ada sebelumnya'
            ];

            return response()->json($data,401);
        }


        //addnew
        $addnew = new \App\Http\Controllers\models\employe;
        $addnew = $addnew->types($request);

        $list = new \App\Http\Controllers\manage\data;
        $list = $list->datask();

        $data = [
            'message'       =>  'Jenis Alat berhasil ditambahkan',
            'list'          =>  $list
        ];

        return response()->json($data,200);
    }


    //DELETE
    public function deletedocument(Request $request)
    {
        $id = trim($request->id);
        
        $getdata = tblDocumentCompanies::where([
            'id'        =>  $id
        ])->first();


        $update = tblDocumentCompanies::where([
            'id'            =>  $id,
            'status'        =>  1
        ])
        ->update([
            'status'            =>  0
        ]);

        //DELETE
        if( $update )
        {
            $trash = new \App\Http\Controllers\tdparty\s3\index;
            $trash = $trash->delete([
                'file'      =>  $getdata->path
            ]);
        }

        //REMINDER NOTIF
        $dataremindernotif = [
            'type'      =>  4,
            'subtype'   =>  $getdata->type,
            'link_id'   =>  trim($request->id)
        ];

        $remindernotif = new \App\Http\Controllers\models\reminder;
        $remindernotif = $remindernotif->delete($dataremindernotif);

        $data = [
            'message'           =>  'Data berhasil di hapus',
            'response'          =>  [
                'id'                =>  $id
            ]
        ];
        
        return response()->json($data, 200);
    }

    //TYPE TOOLS
    public function deletetypetools(Request $request)
    {
        $id = trim($request->id);
        $update = tblAssetTypes::where([
            'id'            =>  $id,
            'status'        =>  1
        ])
        ->update([
            'status'            =>  0
        ]);

        $data = [
            'message'           =>  'Data berhasil di hapus',
            'response'          =>  [
                'id'                =>  $id
            ]
        ];
        
        return response()->json($data, 200);
    }

    //TYPE SK
    public function deletetypesk(Request $request)
    {
        $id = trim($request->id);
        $update = tblDocEmpSubtypes::where([
            'id'            =>  $id,
            'status'        =>  1
        ])
        ->update([
            'status'            =>  0
        ]);

        $data = [
            'message'           =>  'Data berhasil di hapus',
            'response'          =>  [
                'id'                =>  $id
            ]
        ];
        
        return response()->json($data, 200);
    }

}