<?php
namespace App\Http\Controllers\tdparty\courier\rajaongkir;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\app_courier_configs as tblCourierConfigs;

class index extends Controller
{
    //
    public function cost($request)
    {

        $key = $request['config']['key'];
        $host = $request['config']['host'] . json_decode($request['config']['sub_host'])->cost;

        $header = [
            'Content-Type:application/x-www-form-urlencoded',
            'key:' . $key
        ];

        $field = 'origin=' . $request['courier']['origin'];
        $field .= '&originType=' . ( $request['courier']['origin_type'] === 'origin_kecamatan' ? 'subdistrict' : 'city');
        $field .= '&destination='  . $request['courier']['destination'];
        $field .= '&destinationType=' . ( $request['courier']['destination_type'] === 'origin_kecamatan' ? 'subdistrict' : 'city');
        $field .= '&weight=' . ($request['weight']);
        $field .= '&courier=' . strtolower($request['config']['name']);

        // $field = 'origin=501;
        // $field .= '&originType=city';
        // $field .= '&destination=574';
        // $field .= '&destinationType=subdistrict';
        // $field .= '&weight=1700';
        // $field .= '&courier=jne';


        // return response()->json($field, 200);

        $data = [
            'field'         =>  $field,
            'method'        =>  'POST',
            'header'        =>  $header,
            'host'          =>  $host
        ];

        $send = new \App\Http\Controllers\tdparty\courier\index;
        $send = $send->main($data);


        if( $send['message'] !== '')
        {
            return response()->json($send['message'], 400);
        }
        else
        {
            $response = json_decode($send['response'], true);
            $code = $response['rajaongkir']['status']['code'];

            if($code != 200 )
            {
                $data = [
                    'message'       =>  $response['rajaongkir']['status']['description']
                ];
            }
            else
            {
                // $data = $response;
                foreach( $response as $row )
				{

					//get data costs
					$costs = $row['results'][0]['costs'];
					foreach($costs as $row )
					{
						
						$service = $row['service'];
						$price = $row['cost'][0]['value'];
						$etd = strtolower($row['cost'][0]['etd']);
                        $etd = str_replace('hari', '', $etd);
                        
						$view[] = [
                            'courier_id'    =>  $request['courier_id'],
							'service'       =>  $service,
							'price'         =>  $price,
                            'etd'           =>  trim($etd) . ' Hari',
                            'name'          =>  $request['config']['name'],
                            'cod'           =>  false
						];
					}
				}

            }

            return response()->json($view, $code);
        }

        
    }
}