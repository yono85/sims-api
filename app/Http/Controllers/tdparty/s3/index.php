<?php
namespace App\Http\Controllers\tdparty\s3;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;

class index extends Controller
{
    //
    public function upload($request)
    {
        $Config = new Config;
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => $Config->apps()["URL"]['S3'] . '/s3/upload/documents',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        // CURLOPT_SAFE_UPLOAD => false,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $request['data'],
        CURLOPT_HTTPHEADER => [
            "Content-Type: multipart/form-data"
        ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if( $err )
        {
            return $data = [
                'status'        =>  401,
                'message'       =>  $err
            ];
        }
        else
        {
            return $data = [
                'status'        =>  200,
                'response'      =>  $response
            ];
        }
    }

    public function delete($request)
    {
        $Config = new Config;
        $curl = curl_init();

        $data = [
            'file'      =>  $request['file']
        ];

        curl_setopt_array($curl, array(
        CURLOPT_URL => $Config->apps()["URL"]['S3'] . '/s3/delete',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        // CURLOPT_SAFE_UPLOAD => false,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => [
            "Content-Type: multipart/form-data"
        ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if( $err )
        {
            return $data = [
                'status'        =>  401,
                'message'       =>  $err
            ];
        }
        else
        {
            return $data = [
                'status'        =>  200,
                'response'      =>  $response
            ];
        }

    }
}