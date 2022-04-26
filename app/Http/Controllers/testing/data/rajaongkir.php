<?php
namespace App\Http\Controllers\testing\data;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;

class rajaongkir extends Controller
{
    //
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

    public function province()
    {
        $key = '2c82d7fb733d585a81194044216daba1';
        $host = 'https://pro.rajaongkir.com/api/province';

        $header = [
            'Content-Type:application/x-www-form-urlencoded',
            'key:' . $key
        ];

        $field = '';


        $data = [
            'field'         =>  $field,
            'method'        =>  'GET',
            'header'        =>  $header,
            'host'          =>  $host
        ];

        $send = $this->main($data);
        
        $response = json_decode($send['response'], true);
        foreach( $response['rajaongkir']['results'] as $row)
        {
            // $list[] = $row['province_id'];
            DB::table('app_province_sample')
            ->insert([
                'id'            =>  $row['province_id'],
                'name'          =>  $row['province']
            ]);
        }

        // return $list;
        return response()->json($response['rajaongkir']['results'], 200);
    }


    public function city()
    {
        $key = '2c82d7fb733d585a81194044216daba1';
        $host = 'https://pro.rajaongkir.com/api/city';

        $header = [
            'Content-Type:application/x-www-form-urlencoded',
            'key:' . $key
        ];

        $field = '';


        $data = [
            'field'         =>  $field,
            'method'        =>  'GET',
            'header'        =>  $header,
            'host'          =>  $host
        ];

        $send = $this->main($data);
        
        $response = json_decode($send['response'], true);
        foreach( $response['rajaongkir']['results'] as $row)
        {

            DB::table('app_city_sample')
            ->insert([
                'id'            =>  $row['city_id'],
                'prov_id'       =>  $row['province_id'],
                'type'          =>  $row['type'],
                'name'          =>  $row['city_name'],
                'kodepos'       =>  $row['postal_code']
            ]);
        }


        return response()->json($response['rajaongkir']['results'], 200);
    }


    public function kecamatan()
    {
        $key = '2c82d7fb733d585a81194044216daba1';
        $host = 'https://pro.rajaongkir.com/api/subdistrict?city=39';

        $header = [
            'Content-Type:application/x-www-form-urlencoded',
            'key:' . $key
        ];

        $field = '';


        $data = [
            'field'         =>  $field,
            'method'        =>  'GET',
            'header'        =>  $header,
            'host'          =>  $host
        ];

        $send = $this->main($data);
        
        $response = json_decode($send['response'], true);
        // foreach( $response['rajaongkir']['results'] as $row)
        // {

            // DB::table('app_kecamatan_sample')
            // ->insert([
            //     'id'            =>  $row['city_id'],
            //     'prov_id'       =>  $row['province_id'],
            //     'type'          =>  $row['type'],
            //     'name'          =>  $row['city_name'],
            //     'kodepos'       =>  $row['postal_code']
            // ]);
        // }


        return response()->json($response['rajaongkir']['results'], 200);
    }


}