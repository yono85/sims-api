<?php
namespace App\Http\Controllers\lembaga;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\lembagas as tblLembagas;

class data extends Controller
{
    //
    public function show(Request $request)
    {
        //CEK TYPE
        $type = trim($request->type);
        $q = trim($request->q);

        $where = trim($type == 'id') ? 'l.id' : 'l.token';

        $getdata = tblLembagas::from('lembagas as l')
        ->select(
            'l.id', 'l.name','l.email', 'l.phone','l.kumham', 'l.kumham_tgl', 'l.npwp', 'l.provinsi', 'l.city', 'l.kecamatan', 'l.address',
            'l.owner'
        )
        ->where([
            $where          =>  $q,
            'l.status'      =>  1
        ])->first();

        if($getdata == null)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];
    
            return response()->json($data, 404);
        }

        $owner = json_decode($getdata->owner, true);
        $address = json_decode($getdata->address, true);

        $response = [
            'id'        =>  $getdata->id,
            'name'      =>  $getdata->name,
            'owner'     =>  [
                'ketua'         =>  $owner['ketua'],
                'sekretaris'    =>  $owner['sekretaris'],
                'bendahara'     =>  $owner['bendahara']
            ],
            'data'      =>  [
                'kumham'        =>  [
                    'no'            =>  $getdata->kumham,
                    'date'          =>  $getdata->kumham_tgl
                ],
                'npwp'          =>  $getdata->npwp,
                'domisili'      =>  [
                    'no'            =>  '',
                    'date'          =>  ''
                ],
                'sertif'        =>  [
                    'no'            =>  '',
                    'date'          =>  ''
                ],
                'operasional'   =>  [
                    'no'            =>  '',
                    'date'          =>  ''
                ],
                'bank'          =>  [
                    'name'          =>  '',
                    'no'            =>  '',
                    'owner'         =>  ''
                ]
            ],
            'address'   =>  [
                'label'         =>  $address['name'],
                'provinsi'      =>  [
                    'id'            =>  $getdata->provinsi,
                    'name'          =>  $address['provinsi']
                ],
                'city'          =>  [
                    'id'            =>  $getdata->city,
                    'name'          =>  $address['city']
                ],
                'kecamatan'     =>  [
                    'id'            =>  $getdata->kecamatan,
                    'name'          =>  $address['kecamatan']
                ],
                'kelurahan'     =>  'Kel. ' . $address['kelurahan'],
                'kodepos'       =>  $address['kodepos']
            ]
        ];

        $data = [
            'message'       =>  '',
            'response'      =>  $response
        ];

        return response()->json($data, 200);


    }

}