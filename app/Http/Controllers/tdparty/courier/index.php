<?php
namespace App\Http\Controllers\tdparty\courier;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\app_courier_lists as tblCourierLists;
use DB;

class index extends Controller
{

    //main
    public function main($request)
    {
        //
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
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if( $err )
        {
            $data = [
                'message'           =>  'Terjadi masalah pada tidak pada cURL API',
                'status'            =>  404
            ];

            return $data;
        }


        if( $httpcode !== 200 )
        {
            $data = [
                'message'           =>  json_decode($response, true)['msg'],
                'status'            =>  404
            ];

            return $data;
        }


        $data = [
            'message'       =>  '',
            'response'      =>  $response
        ];



        return $data;

    }
    

    public function sendpickup($request)
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
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);


        if( $err )
        {
            $data = [
                'message'       =>  'Terjadi masalah pada code proses',
                'status'        =>  500
            ];
        }
        else
        {
            $resp = json_decode($response);

            if( $resp->status == 'fail') //jika field bermasalah
            {
                $data = [
                    'message'       =>  $resp->msg,
                    'status'        =>  $resp->status //500
                ];
            }
            else
            {
                $data = [
                    'message'       =>  '',
                    'status'        =>  200,
                    'response'      =>  $resp->data
                ];
            }

        }


        return $data;
    }
    
    public function test()
    {
        $host = 'https://api.coresyssap.com/master/shipment_cost/publish';
        $method = 'POST';
        $field = [
            'origin'    =>  "JK1002",
            'destination'   =>  "JB1116",
            "weight"        =>  1
        ];
        $header = [
            'api_key:global',
            'Content-Type:application/json'
        ];

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $host,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => json_encode($field),
            CURLOPT_HTTPHEADER  => $header
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $req = json_decode($response, true);
        return response()->json($req['price_detail']['UDRREG'], 200);
    }


    public function testsingle($request)
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
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);


        if( $err )
        {
            $data = [
                'message'       =>  'Terjadi masalah pada code proses',
                'status'        =>  500
            ];
        }
        else
        {
            $resp = json_decode($response);

            if( $resp->status == 'fail') //jika field bermasalah
            {
                $data = [
                    'message'       =>  $resp->msg,
                    'status'        =>  $resp->status //500
                ];
            }
            else
            {
                $data = [
                    'message'       =>  '',
                    'status'        =>  200,
                    'response'      =>  $resp->data
                ];
            }

        }


        return $data;
    }
    
}