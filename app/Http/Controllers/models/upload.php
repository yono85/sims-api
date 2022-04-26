<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\upload_documents as tblUploadDocuments;
use App\document_employes as tblDocumentEmployes;
use App\document_companies as tblDocumentCompanies;
use App\Http\Controllers\config\index as Config;

class upload extends Controller
{
    //
    public function main($request)
    {
        $Config = new Config;


        //change 
        $check = tblUploadDocuments::where([
            'type'      =>  $request['type'],
            'subtype'   =>  $request['subtype'],
            'link_id'   =>  $request['link_id'],
            'status'    =>  1
        ])
        ->count();

        if( $check > 0 )
        {
            $check = tblUploadDocuments::where([
                'type'      =>  $request['type'],
                'subtype'   =>  $request['subtype'],
                'link_id'   =>  $request['link_id'],
                'status'    =>  1
            ])
            ->update([
                'status'        =>  0
            ]);
        }
        
        $file = $request['file'];

        $newid = $Config->createnewidnew([
            'value'     =>  tblUploadDocuments::count(),
            'length'    =>  15
        ]);

        $token = md5($newid);

        $namefile = $token . '.' . $file->getClientOriginalExtension();

        //
        $addnew             =   new tblUploadDocuments;
        $addnew->id         =   $newid;
        $addnew->token      =   $token;
        $addnew->type       =   $request['type']; //1 = po, 2 = customers, 3 = employe, 4 = orders, 5 = company, 6 = tools, 7 consumable
        $addnew->subtype    =   $request['subtype'];
        $addnew->url        =   $Config->apps()['URL']['S3'] . $request['url'] . $namefile;
        $addnew->file       =   $file->getClientOriginalName();
        $addnew->path       =   $request['path'] . $namefile;
        $addnew->link_id    =   $request['link_id'];
        $addnew->user_id    =   $request['user_id'];
        $addnew->status     =   1;
        $addnew->save();


        //upload file
        $dataupload = [
            'name'          =>  $namefile,
            'file'          =>  new \CURLFile($file),
            'path'          =>  $request['path']
        ];

        $upload = new \App\Http\Controllers\tdparty\s3\index;
        $upload = $upload->upload([
            'data'  =>  $dataupload
        ]);


    }


    //UPOAD DOKUMEN 
    public function employe($request)
    {
        $Config = new Config;

        $file = $request['file'];

        $newid = $Config->createnewidnew([
            'value'     =>  tblDocumentEmployes::count(),
            'length'    =>  15
        ]);

        $token = md5($newid);

        $namefile = $token . '.' . $file->getClientOriginalExtension();

        //
        $addnew                     =   new tblDocumentEmployes;
        $addnew->id                 =   $newid;
        $addnew->token              =   $token;
        $addnew->type               =   $request['type'];
        $addnew->subtype            =   $request['subtype'] === '' ? 0 : $request['subtype'];
        $addnew->employe_id         =   $request['employe_id'];
        $addnew->file               =   $file->getClientOriginalName();
        $addnew->url                =   $Config->apps()['URL']['S3'] . $request['url'] . $namefile;
        $addnew->path               =   $request['path'] . $namefile;
        $addnew->expired_date       =   $request['expired'] === '' ? '' : $Config->changeFormatDate($request['expired']);
        $addnew->reminder_duration  =   $request['reminder'];
        $addnew->user_id            =   $request['user_id'];
        $addnew->status             =   1;
        $addnew->save();
        //

        $dataupload = [
            'name'          =>  $namefile,
            'file'          =>  new \CURLFile($file),
            'path'          =>  $request['path']
        ];

        $upload = new \App\Http\Controllers\tdparty\s3\index;
        $upload = $upload->upload([
            'data'  =>  $dataupload
        ]);
        
    }


    //UPLOAD DOCUMENT COMPANIES
    public function companies($request)
    {
        $Config = new Config;

        $file = $request['file'];

        $newid = $Config->createnewidnew([
            'value'     =>  tblDocumentCompanies::count(),
            'length'    =>  15
        ]);

        $token = md5($newid);

        $namefile = $token . '.' . $file->getClientOriginalExtension();
        
        //
        $addnew                 =   new tblDocumentCompanies;
        $addnew->id             =   $newid;
        $addnew->token          =   $token;
        $addnew->type           =   $request['type'];
        $addnew->name           =   $request['name'];
        $addnew->file           =   $file->getClientOriginalName();
        $addnew->url            =   $Config->apps()['URL']['S3'] . $request['url'] . $namefile;
        $addnew->path           =   $request['path'] . $namefile;
        $addnew->expired_date   =   $Config->changeFormatDate(trim($request['expired']));
        $addnew->reminder_duration  =   trim($request['duration']);
        $addnew->reminder_id        =   0;
        $addnew->user_id            =   trim($request['user_id']);
        $addnew->status             =   1;
        $addnew->save();

        $dataupload = [
            'name'          =>  $namefile,
            'file'          =>  new \CURLFile($file),
            'path'          =>  $request['path']
        ];

        $upload = new \App\Http\Controllers\tdparty\s3\index;
        $upload = $upload->upload([
            'data'  =>  $dataupload
        ]);

        $data = [
            'id'        =>  $newid,
            'token'     =>  $token
        ];

        return $data;
    }
}