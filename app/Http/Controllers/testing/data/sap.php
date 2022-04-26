<?php
namespace App\Http\Controllers\testing\data;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class sap extends Controller
{
    public function main($request)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $request['host'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $request['method'],
            CURLOPT_POSTFIELDS => $request['field'],
            CURLOPT_HTTPHEADER  =>  $request['header']
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if( $err )
        {
            $data = [
                'message'           =>  'Terjadi masalah pada tidak pada cURL API'
            ];
        }
        else
        {
            $data = [
                'message'       =>  '',
                'response'      =>  $response
            ];

        }

        return $data;
    }

    //
    public function origin()
    {

        // return base_path() . 'App/Http/Controllers/testing/data/sapdata.json' ;
        // $path = storage_path() . "/json/sapdata.json"; // ie: /var/www/laravel/app/storage/json/filename.json
        $path = base_path() . '/App/Http/Controllers/testing/data/sap_coverage.json';
        // $path = json_encode($path);
        $json = json_decode(file_get_contents($path), true); 


        


        foreach($json as $row )
        {

            DB::table('app_origin_saps')
            ->where([
                'district_code' =>  $row['district_code']
            ])
            ->update([
                'branch_code'       =>  $row['tlc_branch_code']
            ]);
        }


        // $getdata = DB::table('bck_app_origin_saps')->get();

        // foreach($getdata as $row)
        // {

        //     foreach($json as $rowx)
        //     {
        //         if( $row->district_code == $rowx['district_code'] )
        //         {
        //             DB::table('bck_app_origin_saps')->where(['district_code'=>$row->district_code])
        //             ->insert(['branch_code'=>$rowx['tlc_branch_code']]);
        //         }
        //         else
        //         {
        //             DB::table('bck_app_origin_saps')->where(['district_code'=>$row->district_code])
        //             ->insert(['branch_code'=>'']);
        //         }
        //     }
        //     // if( $rowx->district_code == $json['district_code'])
        //     // {
        //     //     DB::table('bck_app_origin_saps')
        //     //     ->where([
        //     //         'district_code' =>  $row['district_code']
        //     //     ])
        //     //     ->insert([
        //     //         'branch_code'       =>  $row['tlc_branch_code']
        //     //     ]);
        //     // }
        //     // else
        //     // {
                
        //     // }
        //     $list[] = $listjson['district_code'];
        // }

        // return $list;

       



        // return $list;

        // $no = 1;
        // foreach($json as $row )
        // {
        //     // $list[] = $row['city_code'];
        //         DB::table('sap_origin_sample')
        //     ->insert([
        //         'id'                    =>  $no++,
        //         'city_code'             =>  $row['city_code'],
        //         'district_code'         =>  $row['district_code'],
        //         'district_name'         =>  $row['district_name'],
        //         'zone_code'             =>  $row['zone_code'] === null ? '' : $row['zone_code'],
        //         'provinsi_code'         =>  $row['provinsi_code'],
        //         'city_name'             =>  $row['city_name'],
        //         'tlc_branch_code'       =>  $row['tlc_branch_code'] === null ? '' : $row['tlc_branch_code'],
        //         'provinsi_name'         =>  $row['provinsi_name'],
        //         'status'                =>  1,
        //         'origin_id'             =>  0
        //     ]);

        // }

        // return $list;
        // $host = 'https://api.coresyssap.com/master/district/get';
        // $header = [
        //     'api_key:global'
        // ];

        // $data = [
        //     'method'        =>  'GET',
        //     'field'         =>  '',
        //     'header'        =>  $header,
        //     'host'          =>  $host
        // ];

        // $send = $this->main($data);

        // $response = json_decode($send['response'], true);
        // $no = 1;

        // foreach( $response as $row)
        // {

        //     DB::table('sap_origin_sample')
        //     ->insert([
        //         'id'                    =>  $no++,
        //         'city_code'             =>  $row['city_code'],
        //         'district_code'         =>  $row['district_code'],
        //         'district_name'         =>  $row['district_name'],
        //         'zone_code'             =>  $row['zone_code'],
        //         'provinsi_code'         =>  $row['provinsi_code'],
        //         'city_name'             =>  $row['city_name'],
        //         'tlc_branch_code'       =>  $row['tlc_branch_code'],
        //         'provinsi_name'         =>  $row['provinsi_name'],
        //         'status'                =>  1,
        //         'origin_id'             =>  0
        //     ]);


        // }

        // "city_code": "JK00",
        // "district_code": "JK00",
        // "district_name": "JAKARTA",
        // "zone_code": "ZBJK0701",
        // "provinsi_code": "JK",
        // "city_name": "JAKARTA",
        // "tlc_branch_code": "CGK",
        // "provinsi_name": "DKI JAKARTA"


        // return response()->json($response,200);

    }




}