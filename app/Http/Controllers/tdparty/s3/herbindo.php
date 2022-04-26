<?php
namespace App\Http\Controllers\tdparty\s3;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class herbindo extends Controller
{
    //
    public function transfer($request)
    {

        $headers = [
            "Content-Type: multipart/form-data"
        ];

        $datapost = [
            'name'          =>  $request['name'],
            'file'          =>  new \CURLFile($request['file']),
            'path'          =>  $request['path']
        ];

        $data = [
            'headers'       =>  $headers,
            'type'          =>  'POST',
            'URL'           =>  $request["URL"], //'http://localhost:8001/s3/upload/transfer',
            'datapost'      =>  $datapost
        ];

        $upload = new \App\Http\Controllers\tdparty\s3\index;
        $upload = $upload->upload($data);

        return $upload;

    }



}