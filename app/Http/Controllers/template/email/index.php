<?php
namespace App\Http\Controllers\template\email;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\email_templates as tblEmailTemplates;


class index extends Controller
{
    //
    public function main($request)
    {
        $getdata = tblEmailTemplates::where([
            'id'        =>  $request['id']
        ])
        ->first();

        $data = [
            'title'         =>  $getdata->title,
            'subject'       =>  $getdata->subject,
            'content'       =>  $getdata->content
        ];

        return $data;
    }
}