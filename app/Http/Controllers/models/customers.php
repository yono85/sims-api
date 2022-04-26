<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\customers as tblCustomers;
use App\customer_notes as tblCustomerNotes;


class customers extends Controller
{

    //create
    public function main($request)
    {
        $Config = new Config;

        $newid = $Config->createnewidnew([
            'value'         =>  tblCustomers::count(),
            'length'        =>  9
        ]);

        $token = md5($newid);

        $addnew                 =   new tblCustomers;
        $addnew->id             =   $newid;
        $addnew->token          =   $token;
        $addnew->type           =   trim($request->customer_type);
        $addnew->name           =   trim($request->name);
        $addnew->owner          =   trim($request->owner);
        $addnew->phone          =   trim($request->phone);
        $addnew->email          =   trim($request->email);
        $addnew->search         =   trim($request->name) . ';' . trim($request->phone);
        $addnew->address        =   trim($request->address);
        $addnew->provinsi       =   trim($request->provinsi_selected);
        $addnew->city           =   trim($request->city_selected);
        $addnew->kecamatan      =   trim($request->kecamatan_selected);
        $addnew->kodepos        =   trim($request->kodepos);
        $addnew->user_id        =   trim($request->user_id);
        $addnew->status         =   1;
        $addnew->save();

        $data = [
            'token'         =>  $token,
            'id'            =>  $newid
        ];


        return $data;

    }


    //edit
    public function update($request)
    {
        $update = tblCustomers::where([
            'id'        =>  trim($request->customer_id)
        ])
        ->update([
            'type'          =>  trim($request->customer_type),
            'name'          =>  trim($request->name),
            'owner'         =>  trim($request->owner),
            'phone'         =>  trim($request->phone),
            'email'         =>  trim($request->email),
            'search'        =>  trim($request->name) . ';' . trim($request->phone),
            'address'       =>  trim($request->address),
            'provinsi'      =>  trim($request->provinsi_selected),
            'city'          =>  trim($request->city_selected),
            'kecamatan'     =>  trim($request->kecamatan_selected),
            'kodepos'       =>  trim($request->kodepos)

        ]);
    }

    //
    public function new($request)
    {
        $Config = new Config;

        //
        //
        $newcustomerid = tblCustomers::count();
        $newcustomerid++;
        $newcustomerid = $newcustomerid++;

        
        // //create new id
        $newcustomerid = $Config->createnewid([
            'value'         =>  $newcustomerid,
            'length'        =>  14
        ]);


        $newaddcustomers                = new tblCustomers;
        $newaddcustomers->id            =   $newcustomerid;
        $newaddcustomers->token         =   md5($newcustomerid);
        $newaddcustomers->type          =   $request['type'];
        $newaddcustomers->name          =   $request['name'];
        $newaddcustomers->gender        =   $request['gender'];
        $newaddcustomers->phone         =   '0' . (int)$request['phone'];
        $newaddcustomers->phone_code    =   $request['phone_code'];
        $newaddcustomers->email         =   $request['email'];
        $newaddcustomers->progress      =   $request['progress'];
        $newaddcustomers->taging        =   $request['taging'];
        $newaddcustomers->source        =   $request['source'];
        $newaddcustomers->search         =   $request['search'];
        $newaddcustomers->user_id       =   $request['user_id'];
        $newaddcustomers->company_id    =   $request['company_id'];
        $newaddcustomers->status        =   1;
        $newaddcustomers->save();


        //log customers
        $datalog = [
            'customer_id'      =>  $newcustomerid,
            'user_id'           =>  $request['user_id']
        ];

        $addLogs = new \App\Http\Controllers\log\customers\manage;
        $addLogs = $addLogs->Add($datalog);
    }


    public function addnote($request)
    {
        $Config = new Config;

        //
        //
        $newid = tblCustomerNotes::count();
        $newid++;
        $newid = $newid++;

        
        //create new id
        $newid = $Config->createnewid([
            'value'         =>  $newid,
            'length'        =>  14
        ]);
        
        $newaddnote                 = new tblCustomerNotes;
        $newaddnote->id             =   $newid;
        $newaddnote->text           =   $request['note'];
        $newaddnote->customer_id    =   $request['customer_id'];
        $newaddnote->user_id        =   $request['user_id'];
        $newaddnote->companies_id   =   $request['company_id'];
        $newaddnote->status         =   1;
        $newaddnote->save();

        if( $newaddnote )
        {
            return 200;
        }
        
        return 500;

    }

}