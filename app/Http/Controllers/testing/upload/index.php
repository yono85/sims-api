<?php
namespace App\Http\Controllers\testing\upload;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Storage;

class index extends Controller
{
    //
    public function image(Request $request)
    {

        $path = 'images/';
        $file = $request->file('image');
        $name = $request->name;
        $upload = Storage::disk('local')
        ->put($path . $name, file_get_contents($file));

        //
        $data = [
            'message'       =>  'Image success di upload',
            'name'          =>  $request->name,
            'file'          =>  $request->file('image')
        ];


        return Response()->json($data, 200);
    }

    public function upload(Request $request)
    {

        $headers = [
            "Content-Type: multipart/form-data"
        ];

        $datapost = [
            'name'          =>  $request->name,
            'file'         =>  new \CURLFile($request->file('file'))
        ];


        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://localhost:8001/s3/upload/transfer',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        // CURLOPT_SAFE_UPLOAD => false,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $datapost,
        CURLOPT_HTTPHEADER => $headers,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if( $err )
        {
            $data = [
                'message'       =>  'Data gagal diupload',
                'status'        =>  401
            ];
        }
        else
        {
            $response = json_decode($response);
            $data = [
                'message'           =>  $response->message,
                'status'            =>  200
            ];
        }


        return $data;
    }

    public function test(Request $request)
    {
        $file = $request->file('file');
        $user_id = $request->user_id;

        $dataupload = [
            'file'      =>  $file,
            'user_id'   =>  $user_id,
            'type'      =>  1,
            'subtype'   =>  2,
            'link_id'   =>  123123,
            'url'       =>  '/upload/documents/po/',
            'path'      =>  '/documents/po/'
        ];

        // $datapost = [
        //     'name'          =>  123123 . '.' . $file->getClientOriginalExtension(),
        //     'file'          =>  new \CURLFile($file),
        //     'path'          =>  '/documents/po/'
        // ];

        // $upload = new \App\Http\Controllers\tdparty\s3\index;
        // $upload = $upload->upload(['data'=>$datapost]);

        $upload = new \App\Http\Controllers\models\upload;
        $upload = $upload->main($dataupload);

        $data = [
            'message'       =>  '',
            'response'      =>  [
                'file'          =>  $file->getClientOriginalName(),
                'ext'           =>  $file->getClientOriginalExtension()
            ]
        ];

        return response()->json($upload, 200);
    }
}