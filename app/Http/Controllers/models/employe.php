<?php
namespace App\Http\Controllers\models;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\user_employes as tblUserEmployes;
use App\employe_menus as tblEmployeMenus;
use App\doc_emp_subtypes as tblDocEmpSubtypes;
use App\Http\Controllers\config\index as Config;

class employe extends Controller
{

    //user employe
    public function main($request)
    {
        $Config = new Config;

        //
        $newid = $Config->createnewidnew([
            'value'         =>  tblUserEmployes::count(),
            'length'        =>  9
        ]);

        $addnew                     =   new tblUserEmployes;
        $addnew->id                 =   $newid;
        $addnew->level              =   0;
        $addnew->sublevel           =   0;
        $addnew->groups             =   trim($request->divisi);
        $addnew->joins              =   $Config->changeFormatDate(trim($request->tgljoin));
        $addnew->leaves             =   '';
        $addnew->name               =   trim($request->name);
        $addnew->nick               =   $Config->nickName(trim($request->name));
        $addnew->gender             =   trim($request->gender);
        $addnew->birth              =   $Config->changeFormatDate(trim($request->tgllahir));
        $addnew->place_birth        =   trim($request->tempatlahir);
        $addnew->religion           =   0;
        $addnew->last_education     =   0;
        $addnew->relationship       =   0;
        $addnew->address            =   trim($request->address);
        $addnew->kodepos            =   trim($request->kodepos);
        $addnew->kecamatan          =   trim($request->kecamatan_selected);
        $addnew->city               =   trim($request->city_selected);
        $addnew->provinsi           =   trim($request->provinsi_selected);
        $addnew->phone              =   trim($request->phone);
        $addnew->email              =   trim($request->email);
        $addnew->user_id            =   trim($request->user_id);
        $addnew->status             =   1;
        $addnew->save();
    }


    public function update($request)
    {
        $Config = new Config;

        $update = tblUserEmployes::where([
            'id'            =>  $request->employe_id
        ])
        ->update([
            'groups'                =>  trim($request->divisi),
            'joins'                 =>  $Config->changeFormatDate(trim($request->tgljoin)),
            'leaves'                =>  trim($request->tglout) === '' ? '' : $Config->changeFormatDate(trim($request->tglout)),
            'name'                  =>  trim($request->name),
            'nick'                  =>  $Config->nickName(trim($request->name)),
            'gender'                =>  trim($request->gender),
            'birth'                 =>  $Config->changeFormatDate(trim($request->tgllahir)),
            'place_birth'           =>  trim($request->tempatlahir),
            'religion'              =>  0,
            'last_education'        =>  0,
            'relationship'          =>  0,
            'address'               =>  trim($request->address),
            'kodepos'               =>  trim($request->kodepos),
            'kecamatan'             =>  trim($request->kecamatan_selected),
            'city'                  =>  trim($request->city_selected),
            'provinsi'              =>  trim($request->provinsi_selected),
            'phone'                 =>  trim($request->phone),
            'email'                 =>  trim($request->email)
        ]);
    }

    //
    public function menus($request)
    {
        $Config = new Config;

        $newid = $Config->createnewidnew([
            'value'         =>  tblEmployeMenus::count(),
            'length'        =>  9
        ]);

        $addnew             =   new tblEmployeMenus;
        $addnew->id         =   $newid;
        $addnew->employe_id =   $request['id'];
        $addnew->menu       =   json_encode($request['menu']);
        $addnew->status     =   1;
        $addnew->save();
    }

    //TYPE SK
    public function types($request)
    {
        $Config = new Config;

        $newid = tblDocEmpSubtypes::count();
        $newid = ($newid + 1);

        //
        $addnew                 =   new tblDocEmpSubtypes;
        $addnew->id             =   $newid;
        $addnew->name           =   trim($request->label);
        $addnew->user_id        =   trim($request->user_id);
        $addnew->status         =   1;
        $addnew->save();
    }    

}