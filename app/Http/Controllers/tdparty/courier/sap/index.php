<?php
namespace App\Http\Controllers\tdparty\courier\sap;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\order_shipings as tblOrderShipings;
use DB;

class index extends Controller
{
    //
    public function single($request)
    {

        $header = [
            'Content-Type:application/json',
            'api_key:global'
        ];

        $field = [
            'origin'            =>  $request['courier']['origin'],
            'destination'       =>  $request['courier']['destination'],
            'weight'            =>  $request['weight']
        ];

        $data = [
            'host'      =>  $request['host'],
            'method'    =>  'POST',
            'header'    =>  $header,
            'field'     =>  json_encode($field)
        ];

        $send = new \App\Http\Controllers\tdparty\courier\index;
        $send = $send->main($data);

        //
        if( $send['message'] !== '')
        {

            return response()->json($send, $send['status']);
        }
        else
        {

            $response = json_decode($send['response'], true);

            $getdata = $response['price_detail']['UDRREG'];

            $data = [
                'id'            =>  $request['courier_id'],
                'price'         =>  $getdata['price'],
                'etd'           =>  $getdata['sla'],
                'service'  =>  'SAP (' . $getdata['service_type_name'] . ')'
            ];

            return response()->json($data, 200);

        }


    }

    public function cost($request)
    {

        $header = [
            'Content-Type:application/json',
            'api_key:global'
        ];

        $field = [
            'origin'            =>  $request['courier']['origin'],
            'destination'       =>  $request['courier']['destination'],
            'weight'            =>  $request['weight'],
            'cost_type'         =>  'NEW'
        ];

        $data = [
            'host'      =>  $request['host'],
            'method'    =>  'POST',
            'header'    =>  $header,
            'field'     =>  json_encode($field)
        ];

        $send = new \App\Http\Controllers\tdparty\courier\index;
        $send = $send->main($data);

        //
        if( $send['message'] !== '')
        {

            return response()->json($send, $send['status']);
        }
        else
        {


            
            $response = json_decode($send['response'], true);

            foreach($response['price_detail'] as $row => $value)
            {
                
                if( $value['service_type_code'] != 'DRGREG' )
                {

                    $etd = str_replace(' - ', '-', $value['sla']);

                    $list[] = [
                        'courier_id'    =>  $request['courier_id'],
                        'price'         =>  $value['price'],
                        'etd'           =>  $etd, //$value['sla'],
                        'name'          =>  'SAP',
                        'service'       =>  ucwords(strtolower($value['service_type_name'])),
                        'service_code'  =>  $value['service_type_code'],
                        'cod'           =>  $response['coverage_cod']
                    ];
                }

            }

            return response()->json($list, 200);

            // return response()->json($response, 200);

        }


    }

    public function pickup($request)
    {
        $getdata = $this->dataPickup($request);

        $header = [
            'Content-Type:application/json',
            // 'api_key:DEV_m4rK3tPlac3#_2019'
            'api_key:H3rV1nd0_S@p_2021'
        ];

        $field = [
            'customer_code'         =>  (int)$getdata['payment']->type === 1 ? 'SUB027819' : 'SUB027818', //*
            'awb_no'                =>  $getdata['shiping']->code, //*
            'reference_no'          =>  $getdata['shiping']->code, //*
            'pickup_name'           =>  $getdata['pickup']['name'], //*
            'pickup_address'        =>  $getdata['pickup']['address'], //* 
            'pickup_phone'          =>  $getdata['pickup']['phone'], //*
            'pickup_place'          =>  '2', //1 marketplace, 2 wherehouse
            'pickup_email'          =>  '',
            'pickup_postal_code'    =>  '',
            'pickup_contact'        =>  '',
            'pickup_latitude'       =>  '',
            'pickup_district_code'  =>  $getdata['pickup']['origin_code'], //*
            'service_type_code'     =>  $getdata['pickup']['stc'], //*
            'quantity'              =>  $getdata['product']->quantity, //*
            'total_item'            =>  '', 
            'weight'                =>  $getdata['pickup']['weight'], //*
            'volumetric'            =>  '1x1x1', //*
            'shipment_type_code'    =>  'SHTPC', //* package
            'shipment_content_code' =>  '',
            'shipment_label_flag'   =>  '',
            'description_item'      =>  'Herbal',
            'packing_type_code'     =>  'ACH03',
            'item_value'            =>  '',
            'insurance_flag'        =>  '1', //* 1 Non Insurance, 2 Insurance
            'insurance_type_code'   =>  '',
            'insurance_value'       =>  '',
            'cod_flag'              =>  (int)$getdata['payment']->type, //* 1 Non COD, 2 COD
            'cod_value'             =>  $getdata['total_cod'],
            'shipper_name'          =>  $getdata['sales']->name, //*
            'shipper_address'       =>  $getdata['sales']->company_city . ' - ' . $getdata['sales']->company_provinsi, //*
            'shipper_phone'         =>  $getdata['sales']->phone, //*
            'shipper_email'         =>  '',
            'shipper_postal_code'   =>  '',
            'shipper_contact'       =>  '',
            'destination_district_code' =>  $getdata['receiver']['desti_code'], //*
            'receiver_name'         =>  $getdata['receiver']['name'], //*
            'receiver_address'      =>  $getdata['receiver']['address'], //*
            'receiver_phone'        =>  $getdata['receiver']['phone'], //*
            'receiver_email'        =>  '',
            'receiver_postal_code'  =>  '',
            'receiver_contact'      =>  '',
            'special_instruction'   =>  ''
        ];

        
        $data = [
            'host'      =>  'https://api.coresyssap.com/shipment/pickup/single_push',
            'method'    =>  'POST',
            'header'    =>  $header,
            'field'     =>  json_encode($field)
        ];

        $send = new \App\Http\Controllers\tdparty\courier\index;
        $send = $send->sendpickup($data);

        return $send;
    }

    public function dataPickup($request)
    {

        $Config = new Config;

        //
        $getdata = DB::table('orders as o')
        ->select(
            'o.id', 'o.invoice', 'o.field'
        )
        ->where([
            'o.id'     =>  $request
        ])->first();


        $field = json_decode($getdata->field);

        //
        $desticode = explode(',', $field->destination->array);

        //get code district on SAP
        $getdesticode = DB::table('app_origin_saps')
        ->where([
            'origin_kecamatan'      =>  $desticode[2]
        ])->first();

        //get wherehouse
        $getwherehouse = DB::table('app_shiping_origins as aso')
        ->select(
            'aso.name', 'aso.address', 'aso.kodepos', 'aso.phone', 'aso.kecamatan',
            'aop.name as provinsi',
            'aoc.name as city_name', 'aoc.type_label as city_label',
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
            'aso.id'        =>  $field->shiping->origin_id
        ])
        ->first();

        $getDistrictOrigin = DB::table('app_origin_saps')
        ->where([
            'origin_kecamatan'      =>  $getwherehouse->kecamatan
        ])->first();

        

        $calcWeight = $Config->calcWgSAP(2500);

        $pickup = [
            'name'      =>  $getwherehouse->name,
            'address'   =>  $getwherehouse->address . ', Kec.' . ucwords($getwherehouse->kecamatan_name) .', ' . $getwherehouse->city_label . '.'. ucwords($getwherehouse->city_name) . ', ' . $getwherehouse->provinsi . '-' . $getwherehouse->kodepos,
            'phone'     =>  $getwherehouse->phone,
            'origin_code' =>  $getDistrictOrigin->district_code,
            'stc'           =>  ( $field->shiping->courier_service === 'Reguler' ? 'UDRREG' : ( $field->shiping->courier_service === 'One Day Service' ? 'UDRONS' : 'UDRSDS' ) ), //service type code,
            'weight'        =>  $calcWeight
        ];


        $destination = $field->destination;
        $destiCity = str_replace('Kabupaten. ', 'Kab.', $destination->city);

        $receiver = [
            'name'              =>  $destination->name,
            'phone'             =>  $destination->phone,
            'address'           =>  $destination->address . ', Kec.' . ucwords($destination->kecamatan) . ', ' . ucwords($destiCity) . ', ' . $destination->provinsi . '-' . $destination->kodepos,
            'desti_code'      =>  $getdesticode->district_code,
        ];
        
        //
        $orders = [
            'id'        =>  $getdata->id,
            'invoice'   =>  $getdata->invoice
        ];

        $biaya_cod = isset($field->payment->biaya_cod) ? $field->payment->biaya_cod : 0;

        $data = [
            'orders'             =>  $orders,
            'customers'         =>  $field->customers,
            'product'           =>  $field->product,
            'receiver'          =>  $receiver,
            'payment'           =>  $field->payment,
            'shiping'           =>  $field->shiping,
            'sales'             =>  $field->sales,
            'pickup'            =>  $pickup,
            'total_cod'             =>  $field->payment->type === '1' ? 0 : ($field->payment->total + $field->shiping->courier_price + $biaya_cod),
            'biaya_cod'         =>  $biaya_cod
            // 'field'             =>  $field,
        ];

        return $data;

    }

    public function TestPickup($request)
    {
        $getdata = $this->dataPickup($request);

        $header = [
            'Content-Type:application/json',
            'api_key:DEV_m4rK3tPlac3#_2019'
        ];

        $field = [
            'customer_code'         =>  (int)$getdata['payment']->type === 1 ? 'DEV000' : 'DEV001', //*
            'awb_no'                =>  $getdata['shiping']->code, //*
            'reference_no'          =>  $getdata['shiping']->code, //*
            'pickup_name'           =>  $getdata['pickup']['name'], //*
            'pickup_address'        =>  $getdata['pickup']['address'], //* 
            'pickup_phone'          =>  $getdata['pickup']['phone'], //*
            'pickup_place'          =>  '2', //1 marketplace, 2 wherehouse
            'pickup_email'          =>  '',
            'pickup_postal_code'    =>  '',
            'pickup_contact'        =>  '',
            'pickup_latitude'       =>  '',
            'pickup_district_code'  =>  $getdata['pickup']['origin_code'], //*
            'service_type_code'     =>  $getdata['pickup']['stc'], //*
            'quantity'              =>  $getdata['product']->quantity, //*
            'total_item'            =>  '', 
            'weight'                =>  $getdata['pickup']['weight'], //*
            'volumetric'            =>  '1x1x1', //*
            'shipment_type_code'    =>  'SHTPC', //* package
            'shipment_content_code' =>  '',
            'shipment_label_flag'   =>  '',
            'description_item'      =>  'Herbal',
            'packing_type_code'     =>  'ACH03',
            'item_value'            =>  '',
            'insurance_flag'        =>  '1', //* 1 Non Insurance, 2 Insurance
            'insurance_type_code'   =>  '',
            'insurance_value'       =>  '',
            'cod_flag'              =>  (int)$getdata['payment']->type, //* 1 Non COD, 2 COD
            'cod_value'             =>  $getdata['total_cod'],
            'shipper_name'          =>  $getdata['sales']->name, //*
            'shipper_address'       =>  $getdata['sales']->company_city . ' - ' . $getdata['sales']->company_provinsi, //*
            'shipper_phone'         =>  $getdata['sales']->phone, //*
            'shipper_email'         =>  '',
            'shipper_postal_code'   =>  '',
            'shipper_contact'       =>  '',
            'destination_district_code' =>  $getdata['receiver']['desti_code'], //*
            'receiver_name'         =>  $getdata['receiver']['name'], //*
            'receiver_address'      =>  $getdata['receiver']['address'], //*
            'receiver_phone'        =>  $getdata['receiver']['phone'], //*
            'receiver_email'        =>  '',
            'receiver_postal_code'  =>  '',
            'receiver_contact'      =>  '',
            'special_instruction'   =>  ''
        ];

        
        $data = [
            'host'      =>  'http://apisanbox.coresyssap.com/shipment/pickup/single_push',
            'method'    =>  'POST',
            'header'    =>  $header,
            'field'     =>  json_encode($field)
        ];

        $send = new \App\Http\Controllers\tdparty\courier\index;
        $send = $send->sendpickup($data);

        return $send;
    }

}