<?php
namespace App\Http\Controllers\data;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\app_origin_provinsi as tblAppOriginProvinsis;
use App\app_origin_city as tblAppOriginCities;
use App\app_origin_kecamatan as tblAppOriginKecamatans;

class bps extends Controller
{
    //
    public function provinsi(Request $request)
    {
        $Config = new Config;

        //
        $getdata = tblAppOriginProvinsis::where([
            'status'            =>  1
        ])->get();

        foreach($getdata as $row)
        {
            $list[] = [
                'id'            =>  $row->id,
                'name'          =>  $row->name
            ];

        }
        
        $data = [
            'message'       =>  '',
            'list'          =>  $list
        ];

        return  response()->json($data,200);
    }

    //cities
    public function city(Request $request)
    {
        $Config = new Config;

        $pid = trim($request->pid);

        //
        $data = [
            'message'       =>  '',
            'list'          =>  $this->citydata($pid)
        ];
        return response()->json($data, 200);
    }

    //kecamatan
    public function kecamatan(Request $request)
    {
        $Config = new Config;

        $cid = trim($request->cid);

        $data = [
            'message'       =>  '',
            'list'          =>  $this->kecamatandata($cid)
        ];
        return response()->json($data, 200);
    }


    public function citydata($request)
    {
        $getdata = tblAppOriginCities::where([
            'provinsi_id'          =>  $request,
            'status'            =>  1
        ])->get();

        foreach($getdata as $row)
        {
            $list[] = [
                'id'            =>  $row->id,
                'name'          =>  $row->type_label . '. ' . ucwords($row->name)
            ];
        }

        return $list;
    }


    public function kecamatandata($request)
    {
        //
        $getdata = tblAppOriginKecamatans::where([
            'city_id'          =>  $request,
            'status'            =>  1
        ])->get();

        foreach($getdata as $row)
        {
            $list[] = [
                'id'            =>  $row->id,
                'name'          =>  'Kec. ' . ucwords($row->name)
            ];
        }
        
        return $list;
    }

}