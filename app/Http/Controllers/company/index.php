<?php
namespace App\Http\Controllers\company;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\companies as tblCompanies;
use DB;

class index extends Controller
{
    //
    public function profile(Request $request)
    {
        $Config = new Config;

        //
        $getdata = tblCompanies::from('companies as c')
        ->first();

        $owner = json_decode($getdata->owner,true);
        $address = json_decode($getdata->address,true);
        $contact = json_decode($getdata->contact, true);

        $document = new \App\Http\Controllers\manage\data;
        $document = $document->datadocument();

        $tools = new \App\Http\Controllers\manage\data;
        $tools = $tools->datatools();

        $sk = new \App\Http\Controllers\manage\data;
        $sk = $sk->datask();

        //
        $data = [
            'massage'       =>  '',
            'response'      =>  [
                'id'            =>  $getdata->id,
                'token'         =>  $getdata->token,
                'name'          =>  $getdata->name,
                'date'          =>  $Config->timeago($getdata->created_at),
                'address'       =>  [
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
                    'kodepos'       =>  $getdata->kodepos
                ],
                'contact'       =>  [
                    'phone'         =>  $contact['phone'],
                    'email'         =>  $contact['email'],
                    'website'       =>  $contact['website']
                ],
                'owner'         =>  [
                    'name'          =>  $owner['name'],
                    'phone'         =>  $owner['phone'],
                    'email'         =>  $owner['email']
                ],
                'document'      =>  $document,
                'tools'         =>  $tools,
                'sk'            =>  $sk
            ]
        ];

        return response()->json($data, 200);
    }
}