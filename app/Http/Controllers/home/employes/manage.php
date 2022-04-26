<?php
namespace App\Http\Controllers\home\employes;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\Http\Controllers\account\index as Account;
use App\user_employes as tblUserEmployes;
use App\document_employes as tblDocumentEmployes;
use App\users as tblUsers;
use DB;

class manage extends Controller
{
    //
    public function createAccount(Request $request)
    {
        $CekAccount = new Account;
        $account = $CekAccount->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);
                
        //
        $employe_id = trim($request->employe_id);

        // CHECK
        $cek = tblUserEmployes::where([
            'id'        =>  $employe_id
        ])
        ->first();

        //jika id tidak ditemukan
        if( $cek == null)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data,404);
        }


        //CHECK EMAIL
        $cekemail = tblUsers::where([
            'email'     =>  $cek->email
        ])->first();

        //JIKA EMAIL TERDAFTAR TETAPI LEVEL NYA BUKAN 1
        if( $cekemail != null && $cekemail->level != 1)
        {
            $data = [
                'message'       =>  'Alamat email telah terdaftar sebagai partner'
            ];
            return response()->json($data, 401);
        }

        //JIKA EMAIL NULL MAKA BUAT AKUN USER
        if( $cekemail == null )
        {
             // DATA USER    
            $dataUser = [
                'name'          =>  $cek->name,
                'email'         =>  $cek->email,
                'password'      =>  '',
                'username'      =>  '',
                'company_id'    =>  $cek->company_id,
                'level'         =>  1,
                'sub_level'     =>  3,
                'gender'        =>  $cek->gender,
                'phone'         =>  $cek->phone,
                'phone_code'        =>  62,
                'admin_id'          =>  $account['id'],
                'company_id'        =>  $cek->company_id
            ];

            //add user
            $addUser = new \App\Http\Controllers\models\users;
            $addUser = $addUser->createEmploye($dataUser);
        }

        //JIKA EMAIL ADA DAN LEVEL 1 MAKA HANYA UPDATE
        // 1. BUAT MENU HOME
        // 2. UPDATE TABLE EMPLOYE DI FIELD EMPLOYE ID
        $createMenu = new \App\Http\Controllers\home\menu\manage;
        $createMenu = $createMenu->createMenus(['employe_id'=>$employe_id]);
        
        //UPDATE FIELD USER ID
        $updateemploye = tblUserEmployes::where([
            'id'        =>  $employe_id
        ])
        ->update([
            'user_id'       => $cekemail === null ? $addUser['id'] : $cekemail['id']
        ]);

        //
        $data = [
            'message'       =>  'success'
        ];

        return response()->json($data,200);
    }


    public function create(Request $request)
    {

        $Config = new Config;

        // IF NEW CREATE
        if( trim($request->type) == 'new')
        {
            //check
            $check = tblUserEmployes::where([
                ['name', 'like', '%' . trim($request->name) . '%' ],
                ['birth', '=', $Config->changeFormatDate(trim($request->tgllahir))],
                ['status', '=', 1]
            ])->count();

            //reject insert
            if( $check > 0)
            {
                $data = [
                    'message'       =>  'Nama karyawan dengan tanggal lahir yang sama telah diinput sebelumnya',
                    'focus'         =>  'name'
                ];

                return response()->json($data, 403);
            }


            //insert
            $addEmploye = new \App\Http\Controllers\models\employe;
            $addEmploye = $addEmploye->main($request);

            $data = [
                'message'           =>  'Data karyawan berhasil disimpan'
            ];

            return response()->json($data, 200);

        }
        

        //SUNTING
        $update = new \App\Http\Controllers\models\employe;
        $update = $update->update($request);
        
        $data = [
            'message'       =>  'Data karyawan berhasil disunting'
        ];


        return response()->json($data, 200);
    }


    public function show(Request $request)
    {
        $Config = new Config;

        //
        $getdata = tblUserEmployes::from("user_employes as ue")
        ->select(
            "ue.id", "ue.name","ue.gender","ue.groups as divisi","ue.joins", "ue.leaves","ue.birth", "ue.place_birth", "ue.address", "ue.kodepos", "ue.phone", "ue.email", "ue.provinsi", "ue.city", "ue.kecamatan"
        )
        ->where([
            'ue.id'            =>  $request->id
        ])
        ->first();


        //get city
        $bps = new \App\Http\Controllers\data\bps;

        $listcity = $getdata->provinsi === 0 ? '' : $bps->citydata($getdata->provinsi);
        $listkecamatan = $getdata->city === 0 ? '' : $bps->kecamatandata($getdata->city);

        $data = [
            'message'       =>  '',
            'response'       =>  [
                'id'                =>  $getdata->id,
                'name'              =>  $getdata->name,
                'gender'            =>  $getdata->gender,
                'birth'             =>  $Config->roleFormatDate($getdata->birth),
                'place_birth'       =>  $getdata->place_birth,
                'divisi'            =>  $getdata->divisi,
                'joins'             =>  $Config->roleFormatDate($getdata->joins),
                'leaves'             =>  ($getdata->leaves === '' ? '' : $Config->roleFormatDate($getdata->leaves)),
                'address'           =>  $getdata->address,
                'kodepos'           =>  $getdata->kodepos,
                'phone'             =>  $getdata->phone,
                'email'             =>  $getdata->email,
                'provinsi'          =>  $getdata->provinsi === 0 ? '-1' : $getdata->provinsi,
                'city'              =>  $getdata->city === 0 ? '-1' : $getdata->city,
                'kecamatan'         =>  $getdata->kecamatan === 0 ? '-1' : $getdata->kecamatan,
                'listcity'          =>  $listcity,
                'listkecamatan'     =>  $listkecamatan
            ]
        ];

        return response()->json($data, 200);
    }


    public function createdocument(Request $request)
    {
        $Config = new Config;
        $file = $request->file('file');
        $doctype = trim($request->document_type);
        if( trim($request->type) === "new")
        {

            $exp = $Config->changeFormatDate(trim($request->expired_date));
            $date = date('Y-m-d', strtotime($exp . '-' . trim($request->durasi_reminder) . ' day' ) );
            

            //CHECK KTP
            // if( $doctype == '1')
            // {
                
            //     $check = tblDocumentEmployes::where([
            //         'type'          =>  1,
            //         'employe_id'    =>  trim($request->employe_id),
            //         'status'        =>  1
            //     ])->count();

            //     if( $check > 0)
            //     {
            //         return response()->json([
            //             'message'       =>  'Dokumen KTP sudah ada'
            //         ], 403);
            //     }
            // }

            // //CHECK MCU
            // if( $doctype == '2')
            // {
                
            //     $check = tblDocumentEmployes::where([
            //         'type'          =>  2,
            //         'employe_id'    =>  trim($request->employe_id),
            //         'status'        =>  1
            //     ])->count();

            //     if( $check > 0)
            //     {
            //         return response()->json([
            //             'message'       =>  'Dokumen MCU sudah ada'
            //         ], 403);
            //     }
            // }


            // //CHECK SK
            // if( $doctype == '3')
            // {
                
                $check = tblDocumentEmployes::where([
                    'type'          =>  $doctype,
                    'employe_id'    =>  trim($request->employe_id),
                    'status'        =>  1
                ]);
                if( $doctype == '3')
                {
                    $check = $check->where([
                        'subtype'       =>  trim($request->skil_type)
                    ]);
                }
                $check = $check->count();

                if( $check > 0)
                {
                    return response()->json([
                        'message'       => ($doctype === '1' ? 'Dokumen KTP sudah ada' : ( $doctype === '2' ? 'Dokumen MCU sudah ada' : 'Sertifikat Keahlian sudah ada' ) )
                    ], 403);

                }
            // }


            

            //CHECK DURATION REMINDER
            //BEFORE UPLOAD
            $datacheckduration = [
                'expired'       =>  trim($request->expired_date),
                'duration'      =>  trim($request->durasi_reminder)
            ];

            $checkduration = new \App\Http\Controllers\models\reminder;
            $checkduration = $checkduration->checkduration($datacheckduration);

            if( $checkduration != '')
            {
                return $checkduration;
            }
    
            $dataupload = [
                'user_id'           =>  trim($request->user_id),
                'employe_id'        =>  trim($request->employe_id),
                'type'              =>  $doctype,
                'subtype'           =>  trim($request->skil_type),
                'expired'           =>  trim($request->expired_date),
                'reminder'          =>  trim($request->durasi_reminder),
                'file'              =>  $file,
                'url'               =>  '/upload/documents/employe/',
                'path'              =>  '/documents/employe/'
            ];
    
            $upload = new \App\Http\Controllers\models\upload;
            $upload = $upload->employe($dataupload);
    
            //reminder
            $remind = new \App\Http\Controllers\models\reminder;
            $remind = $remind->documentemploye($request);


            //success
            $data = [
                'message'       =>  'Dokumen berhasil disimpan'
            ];
    
            return response()->json($data,200);
        }

        //sunting

    }

    //DOCUMENT DELETE
    public function documentdelete(Request $request)
    {
        $Config = new Config;

        $id = trim($request->delete_id);

        $getdata = tblDocumentEmployes::where([
            'id'        =>  $id
        ])->first();


        //delete table document
        $delete = tblDocumentEmployes::where([
            'id'            =>  $id,
            'status'        =>  1
        ])
        ->update([
            'status'        =>  0
        ]);

        //
        if( $delete )
        {
            $trash = new \App\Http\Controllers\tdparty\s3\index;
            $trash = $trash->delete([
                'file'      =>  $getdata->path
            ]);
        }

        $data = [
            'message'       =>  'Dokumen berhasil dihapus',
            'file'          =>  $getdata->path
        ];

        return response()->json($data, 200);

    }
}