<?php
namespace App\Http\Controllers\manage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\asset_types as tblAssetTypes;
use App\doc_emp_subtypes as tblDocEmpSubtypes;
use App\doc_comp_types as tblDocCompTypes;
use App\document_companies as tblDocumentCompanies;

class data extends Controller
{
    //TOOLS LIST
    public function tools(Request $request)
    {
        //
        $Config = new Config;

        $data = [
            'message'       =>  '',
            'list'          =>  $this->datatools()
        ];

        return response()->json($data,200);
    }

    // DATA TOOLS
    public function datatools()
    {

        //
        $getdata = tblAssetTypes::where([
            'status'        =>  1
        ]);
        
        $count = $getdata->count();

        if( $count == 0)
        {
            return "";
        }

        $gettable = $getdata->get();


        return $gettable;
    }


    //SK LIST
    public function sk(Request $request)
    {
        //
        $Config = new Config;

        $data = [
            'message'       =>  '',
            'list'          =>  $this->datask()
        ];

        return response()->json($data,200);
    }

    //DATA SK
    public function datask()
    {
        //
        $getdata = tblDocEmpSubtypes::where([
            'status'        =>  1
        ]);
        
        $count = $getdata->count();
        
        if( $count == 0 )
        {
            return "";
        }
        
        $gettable = $getdata->get();
        return $gettable;

        
    }

    //DOCUMENT LIST
    public function document(Request $request)
    {
        $data = [
            'message'      =>  '',
            'list'         =>   $this->datadocument()
        ];

        return response()->json($data, 200);
    }

    //DATA DOCUMENT
    public function datadocument()
    {
        $Config = new Config;

        //
        $getdata = tblDocumentCompanies::from('document_companies as dc')
        ->select(
            'dc.id', 'dc.name', 'dc.expired_date',
            'dct.name as type_name'
        )
        ->leftJoin('doc_comp_types as dct', function($join)
        {
            $join->on('dct.id', '=', 'dc.type');
        })
        ->where([
            'dc.status'        =>  1
        ]);
        
        $count = $getdata->count();
        
        if( $count == 0)
        {
            return "";
        }

        $gettable = $getdata->get();
        foreach($gettable as $row)
        {
            $list[] = [
                'id'        =>  $row->id,
                'type'      =>  $row->type_name,
                'name'      =>  $row->name,
                'expired_date'   =>  $Config->roleFormatDate($row->expired_date)
            ];
        }

        return $list;
    }

    //DOCUMENT COMPANIES TYPES
    public function doccomptype(Request $request)
    {
        $data = [
            'message'       =>  '',
            'list'          =>  $this->datadoccomptypes()
        ];

        return response()->json($data,200);
    }

    //DATA DOCUMENT COMPANIES TYPE
    public function datadoccomptypes()
    {
        $getdata = tblDocCompTypes::where([
            'status'        =>  1
        ]);
        
        $count = $getdata->count();
        
        if( $count == 0)
        {
            return "";
        }

        $gettable = $getdata->get();

        return $gettable;
    }
}