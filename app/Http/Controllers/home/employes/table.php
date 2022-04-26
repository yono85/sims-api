<?php
namespace App\Http\Controllers\home\employes;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\user_employes as tblUserEmployes;
use App\document_employes as tblDocumentEmployes;
use App\Http\Controllers\config\index as Config;
use DB;

class table extends Controller
{
    //
    public function main(Request $request)
    {
        $Config = new Config;

        //request
        $src = '%' . trim($request->search) . '%';
        $groups = trim($request->selected_group);
        $status = trim($request->selected_status);
        $paging = trim($request->paging);
        $sortname = trim($request->sort_name);


        $getdata = tblUserEmployes::from('user_employes as ue')
        ->select(
            'ue.id','ue.nick','ue.name','ue.phone','ue.email','ue.joins','ue.gender','ue.address', 'ue.status', 'ue.user_id', 'ue.kodepos',
            DB::raw('IFNULL(eg.name, "null") as group_name'),
            DB::raw('IFNULL(u.registers, 0) as register'),
            'aop.name as provinsi_name',
            'aoc.name as city_name', 'aoc.type_label as city_label',
            'aok.name as kecamatan_name'
        )
        ->leftJoin('employe_groups as eg', function($join)
        {
            $join->on('eg.id', '=', 'ue.groups');
        })
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'ue.user_id');
        })
        ->leftJoin('app_origin_provinsis as aop', function($join)
        {
            $join->on('aop.id', '=', 'ue.provinsi');
        })
        ->leftJoin('app_origin_cities as aoc', function($join)
        {
            $join->on('aoc.id', '=', 'ue.city');
        })
        ->leftJoin('app_origin_kecamatans as aok', function($join)
        {
            $join->on('aok.id', '=', 'ue.kecamatan');
        })
        ->where([
            ['ue.name','like', $src]
        ]);
        if($groups != '-1')
        {
            $getdata = $getdata->where([
                'ue.groups'     =>  $groups
            ]);
        }
        if( $status != '-1')
        {
            //
            if( $status == '1')
            {
                $getdata = $getdata->where([
                    ['ue.user_id', '!=', 0],
                    ['u.registers', '=', 1]
                ]);
            }
            elseif( $status == '2')
            {
                $getdata = $getdata->where([
                    'u.registers'   =>  0
                ]);
            }
            elseif( $status == '3')
            {
                $getdata = $getdata->where([
                    'ue.user_id'        =>  0
                ]);
            }
            else
            {
                $getdata = $getdata->where([
                    'ue.status'        =>  0
                ]);
            }
        }


        //
        $count = $getdata->count();
        if( $count == 0)
        {
            $data = [
                'message'       =>  'Data Tidak ditemukan',
                'response'      =>  ''
            ];

            return response()->json($data, 404);
        }

        //
        $gettable = $getdata->orderBy('ue.name', $sortname)
        ->take($Config->table(['paging'=>$paging])['paging_item'])
        ->skip($Config->table(['paging'=>$paging])['paging_limit'])
        ->get();

        foreach($gettable as $row)
        {

            //status account
            if( $row->status == 0 )
            {
                $status = 4; //non active
            }
            elseif( $row->user_id != '0' && $row->register == '1')
            {
                $status = 1; //active
            }
            elseif( $row->user_id != '0' && $row->register == '0') //no account
            {
                $status = 2;
            }
            else{
                $status = 3;
            }

            //getdcument
            $getdocument = tblDocumentEmployes::from('document_employes as de')
            ->select(
                'de.id', 'de.url',
                'det.name as type_name',
                DB::raw('IFNULL(des.name, "") as subtype_name')
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
                'de.employe_id' =>  $row->id,
                'de.status'     =>  1
            ])
            ->get();

            $list[] = [
                'id'                =>  $row->id,
                'nick'              =>  $row->nick,
                'name'              =>  $row->name,
                'gender'            =>  $row->gender,
                'join'              =>  date('d/m/Y', strtotime($row->joins)),
                'phone'             =>  $row->phone,
                'email'             =>  $row->email,
                'group'             =>  $row->group_name,
                'address'           =>  $row->address,
                'register'          =>  $row->register,
                'address2'          =>  ucwords($row->kecamatan_name) . ' - ' . $row->city_label . '. ' . ucwords($row->city_name),
                'address3'          =>  'Prov. ' . $row->provinsi_name . ' - ' . $row->kodepos,
                'status'            =>  $status,
                'document'          =>  count($getdocument) === 0 ? '' : $getdocument
            ];
        }


        //
        $data = [
            'message'       =>  '',
            'response'      =>  [
                'list'          =>  $list,
                'paging'        =>  $paging,
                'total'         =>  $count,
                'countpage'     =>  ceil($count / $Config->table(['paging'=>$paging])['paging_item'] )
            ]
        ];

        return response()->json($data,200);
    }
}