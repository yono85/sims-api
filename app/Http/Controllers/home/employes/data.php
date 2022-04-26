<?php
namespace App\Http\Controllers\home\employes;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\employe_groups as tblEmployeGroups;
use App\user_employes as tblUserEmployes;
use App\document_employes as tblDocumentEmployes;
use App\doc_emp_types as tblDocEmpTypes;
use App\doc_emp_subtypes as tblDocEmpSubtypes;
use App\Http\Controllers\config\index as Config;
use DB;

class data extends Controller
{
    //
    public function listgroup(Request $request)
    {
        $getdata = tblEmployeGroups::where([
            'status'        =>  1
        ])
        ->get();

        $data = [
            'message'       =>  '',
            'response'      =>  $getdata
        ];
        
        return response()->json($data,200);
    }


    public function groups(Request $request)
    {
        $getdata = tblEmployeGroups::where([
            'status'        =>  1
        ])
        ->get();

        $data = [
            'message'       =>  '',
            'response'      =>  $getdata
        ];
        
        return response()->json($data,200);
    }


    public function list(Request $request)
    {
        $type = trim($request->type);
        $id = json_decode($request->id);
        $field = trim($request->field);
        $uid = trim($request->uid);

        //
        $getdata = tblUserEmployes::where([
            'status'        =>  1
        ]);
        if( $uid == "true" )
        {
            $getdata = $getdata->where([
                ['user_id', '!=', 0]
            ]);
        }
        if( $type == 'select')
        {
            $getdata = $getdata->whereIn($field, $id);
        }
        $getdata = $getdata->get();
        
        $data = [
            'message'       =>  '',
            'response'      =>  $getdata
        ];

        return $data;
    }

    //list modal
    public function listmodal(Request $request)
    {
        $Config = new Config;

        //
        $search = '%' . trim($request->search) . '%';
        $paging = trim($request->pg);

        $getdata = tblUserEmployes::from('user_employes as ue')
        ->select(
            'ue.id', 'ue.name', 'ue.phone',
            'eg.name as group_name'
        )
        ->leftJoin('employe_groups as eg', function($join)
        {
            $join->on('eg.id', '=', 'ue.groups');
        })
        ->where([
            ['ue.name',    'like', $search],
            ['ue.project_id', '=', 0]
        ]);

        $count = $getdata->count();

        if($count == 0)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }

        $gettable = $getdata->orderBy('ue.name', 'asc')
        ->take($Config->table(['paging'=>$paging])['paging_item'])
        ->skip($Config->table(['paging'=>$paging])['paging_limit'])
        ->get();

        foreach($gettable as $row)
        {
            
            $doc = $this->documentemployelist($row);

            $list[] = [
                'id'            =>  $row->id,
                'name'          =>  $row->name,
                'group'         =>  $row->group_name,
                'phone'         =>  $row->phone,
                'status'        =>  'true',
                'error'         =>  'Error',
                'doc'           =>  $doc
            ];
        }

        $data = [
            'message'       =>  '',
            'response'      =>  $list
        ];

        return response()->json($data, 200);

        
    }

    //DOCUMENT EMPLOYE LIST
    public function documentemployelist($request)
    {
        $days = strtotime(date('Y-m-d', time()));
        //
        $getdocument = tblDocumentEmployes::from('document_employes as de')
        ->select(
            'de.id', 'de.expired_date',
            'det.name as name',
            DB::raw('IFNULL(des.name, "") as subtype')
        )
        ->leftJoin('doc_emp_types as det', function($join)
        {
            $join->on('det.id', '=', 'de.type');
        })
        ->leftJoin('doc_emp_subtypes as des', function($join)
        {
            $join->on('des.id', '=', 'de.subtype');
        })
        ->where([
            'de.employe_id' =>  $request->id,
            'de.status'    => 1
        ]);
        $count = $getdocument->count();
        
        if($count == 0 )
        {
            return "";
        }

        $gettable = $getdocument->get();
        foreach($gettable as $row)
        {
            $date = strtotime($row->expired_date);
            $list[] = [
                'id'        =>  $row->id,
                'name'      =>  ($row->subtype !== '' ? $row->subtype : $row->name),
                'expired'   =>  $row->expired_date,
                'status'    =>  ($days >= $date ? 'expired' : '')
            ];
        }

        return $list;

    }

    //DOCUMENT
    public function document(Request $request)
    {
        $Config = new Config;

        //
        $getdata = tblUserEmployes::from('user_employes as e')
        ->select(
            'e.id', 'e.name', 'e.phone',
            'eg.name as divisi'
        )
        ->leftJoin('employe_groups as eg', function($join)
        {
            $join->on('eg.id', '=', 'e.groups');
        })
        ->where([
            'e.id'            =>  trim($request->id)
        ])
        ->first();
        
        $list = $this->datalistmodaldoc($getdata->id);

        $data = [
            'message'       =>  '',
            'response'      =>  [
                'id'            =>  $getdata->id,
                'name'          =>  $getdata->name,
                'phone'         =>  $getdata->phone,
                'divisi'        =>  $getdata->divisi,
                'list'          =>  $list
            ]
        ];

        return response()->json($data, 200);
    }


    //LIST MODAL DOCUMENT
    public function listmodaldoc(Request $request)
    {
        $list = $this->datalistmodaldoc($request->id);

        if( $list == '')
        {
            $data = [
                'message'   =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }

        $data = [
            'message'       =>  '',
            'response'      =>  $list
        ];

        return response()->json($data,200);
    }


    //DATA LIST DOCUMENT MODAL
    public function datalistmodaldoc($request)
    {
        //
        $Config = new Config;
        $id = trim($request);

        //
        $getdata = tblDocumentEmployes::from('document_employes as de')
        ->select(
            'de.id', 'de.type', 'de.token', 'de.subtype', 'de.expired_date',
            'det.name as type_name',
            'des.name as subtype_name'
        )
        ->leftJoin('doc_emp_types as det', function($join)
        {
            $join->on('det.id', '=', 'de.type');
        })
        ->leftJoin('doc_emp_subtypes as des', function($join)
        {
            $join->on('des.id', '=', 'de.subtype');
        })
        ->where([
            'de.employe_id'        =>  $id,
            'de.status'            =>  1
        ]);


        if( $getdata->count() == 0 )
        {
            $list = '';
        }
        else
        {
            foreach($getdata->get() as $row)
            {
                $list[] = [
                    'id'        =>  $row->id,
                    'token'     =>  $row->token,
                    'type'      =>  [
                        'id'        =>  $row->type,
                        'name'      =>  $row->type_name
                    ],
                    'subtype'   =>  [
                        'id'        =>  $row->subtype,
                        'name'      =>  $row->subtype_name
                    ],
                    'expired'   =>  $Config->roleFormatDate($row->expired_date)
                ];
            }
        }

        return $list;
    }

    // DOCUMENT AREA ===========================>

    // TYPE DOCUMENT
    public function typedocumentlist(Request $request)
    {
        $getdata = tblDocEmpTypes::where([
            'status'          =>  1
        ])->get();

        $data = [
            'message'       =>  '',
            'response'      =>  $getdata
        ];

        return response()->json($data, 200);
    }


    //SUBTYPE DOCUMENT 
    public function subtypedocumentlist(Request $request)
    {
        $getdata = tblDocEmpSubtypes::where([
            'status'          =>  1
        ])->get();

        $data = [
            'message'       =>  '',
            'response'      =>  $getdata
        ];

        return response()->json($data, 200);
    }

}