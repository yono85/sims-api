<?php
namespace App\Http\Controllers\data\orders;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\app_metode_payments as tblAppMetodePayments;
use App\app_bank_lists as tblAppBankLists;
use App\app_shiping_origins as tblAppShipingOrigins;
use App\product_prices as tblProductPrices;

class index extends Controller
{
    //
    public function BankList($request)
    {
        $company_id = trim($request['company_id']);

        //
        $getdata = tblAppMetodePayments::from('app_metode_payments as amp')
        ->select(
            'amp.id', 'amp.type', 'amp.account_name', 'amp.account_norek', 'amp.bank_id',
            'abl.name', 'abl.label'
        )
        ->leftJoin('app_bank_lists as abl', function($join)
        {
            $join->on('abl.id', '=', 'amp.bank_id');
        })
        ->where([
            'amp.company_id'        =>  $company_id,
            'amp.type'              =>  1,
            'amp.status'            =>  1
        ])
        ->get();

        if( count($getdata) > 0 )
        {
            foreach($getdata as $row)
            {


                $list[] = [
                    'id'                =>  $row->id,
                    'type'              =>  $row->type,
                    'name'              =>  $row->name,
                    'label'             =>  $row->label,
                    'account_name'      =>  $row->account_name,
                    'account_norek'     =>  $row->account_norek,
                    'bank_id'           =>  $row->bank_id
                ];
            }
        }
        else
        {
            $list = '';
        }


        return $list;

    }

    //
    public function OriginList($request)
    {
        
        $company_id = trim($request['company_id']);

        //
        $getdata = tblAppShipingOrigins::from('app_shiping_origins as aso')
        ->select(
            'aso.id', 'aso.label', 'aso.name', 'aso.address', 'aso.kodepos', 'aso.phone', 'aso.provinsi', 'aso.city', 'aso.kecamatan',
            'aop.name as provinsi_name',
            'aoc.name as city_name', 'aoc.type as city_type',
            'aok.name as kecamatan_name'
        )
        ->leftJoin('app_origin_provinsis as aop', function($join)
        {
            $join->on('aop.id', '=', 'aso.provinsi');
        })
        ->leftJoin('app_origin_cities as aoc', function($join)
        {
            $join->on('aoc.id', '=', 'aso.city');
        })
        ->leftJoin('app_origin_kecamatans as aok', function($join)
        {
            $join->on('aok.id', '=', 'aso.kecamatan');
        })
        ->where([
            'aso.company_id'        =>  $company_id,
            'aso.status'            =>  1
        ])->get();


        if( count($getdata) > 0 )
        {
            foreach($getdata as $row)
            {
                $list[] = [
                    'id'            =>  $row->id,
                    'label'         =>  $row->label,
                    'name'          =>  $row->name,
                    'address'       =>  $row->address,
                    'kodepos'       =>  $row->kodepos,
                    'phone'         =>  $row->phone,
                    'address_array' =>  $row->provinsi .','.$row->city.','.$row->kecamatan,
                    'address_label' =>  $row->provinsi_name .', ' . ($row->city_type === 'Kota' ? 'Kota. ' : 'Kab. ') . ucwords(strtolower($row->city_name)) . ', ' . 'Kec. ' . ucwords(strtolower($row->kecamatan_name)),
                    'provinsi'      =>  [
                        'id'            =>  $row->provinsi,
                        'name'          =>  $row->provinsi_name
                    ],
                    'city'          =>  [
                        'id'            =>  $row->city,
                        'name'          =>  ($row->city_type === 'Kota' ? 'Kota. ' : 'Kab. ') . ucwords(strtolower($row->city_name)),
                        // 'type'          =>  $row->city_type
                    ],
                    'kecamatan'     =>  [
                        'id'            =>  $row->kecamatan,
                        'name'          =>  'Kec. ' . ucwords(strtolower($row->kecamatan_name))
                    ]
                ];
            }
        }
        else
        {
            $list = '';
        }


        return $list;

    }


    //
    public function PriceDistributorList($request)
    {
        $company_id = trim($request['company_id']);

        $getdata = tblProductPrices::from('product_prices as pp')
        ->select(
            'pp.id', 'pp.price as uprice',
            'p.name as product_name', 'p.price_reseller as price'
        )
        ->leftJoin('products as p', function($join)
        {
            $join->on('p.id', '=', 'pp.product_id');
        })
        ->where([
            'pp.company_id'    =>  trim($company_id),
            'pp.status'         =>  1
        ])
        ->get();

        if( count($getdata) > 0 )
        {
            foreach($getdata as $row)
            {
                $list[] = [
                    'id'        =>  $row->id,
                    'uprice'    =>  $row->uprice,
                    'name'      =>  $row->product_name,
                    'price'     =>  $row->price
                ];
            }
        }
        else
        {
            $list = '';
        }
        

        return $list;
    }

}