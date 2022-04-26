<?php
namespace App\Http\Controllers\data\partner;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\user_companies as tblUserCompanies;


class manage extends Controller
{
    //
    public function list(Request $request)
    {


        //account
        $getaccount = new \App\Http\Controllers\account\index;
        $getaccount = $getaccount->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);



        $getdata = tblUserCompanies::from('user_companies as uc')
        ->select(
            'uc.id', 'uc.name', 'uc.address',
            'uc.kodepos',
            'aop.name as provinsi_name',
            'aoc.name as city_name', 'aoc.type as city_type',
            'aok.name as kecamatan_name'
        )
        ->leftJoin('app_origin_provinsis as aop', function($join)
        {
            $join->on('aop.id', '=', 'uc.provinsi');
        })
        ->leftJoin('app_origin_cities as aoc', function($join)
        {
            $join->on('aoc.id', '=', 'uc.city');
        })
        ->leftJoin('app_origin_kecamatans as aok', function($join)
        {
            $join->on('aok.id', '=', 'uc.kecamatan');
        })
        ->where([
            'uc.produsen_id'    =>  $getaccount['config']['company_id'],
            'uc.type'      =>  2
        ])
        ->get();

        foreach($getdata as $row)
        {
            $list[] = [
                'id'            =>  $row->id,
                'name'          =>  $row->name,
                'address'       =>  $row->address,
                'provinsi'      =>  $row->provinsi_name,
                'city'          =>  ucwords(strtolower($row->city_type)) . '. ' . ucwords(strtolower($row->city_name)),
                'kecamatan'     =>  ucwords(strtolower($row->kecamatan_name)),
                'kodepos'       =>  $row->kodepos
            ];
        }


        //
        $data = [
            'message'           =>  '',
            'response'          =>  [
                'list'              =>  $list
            ]
        ];

        return response()->json($data, 200);
    }
}