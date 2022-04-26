<?php
namespace App\Http\Controllers\clickwa\orders;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\orders as tblOrders;
use App\Http\Controllers\clickwa\template as TemplateWA;
use App\Http\Controllers\config\index as Config;

class manage extends Controller
{
    //
    public function invoice(Request $request)
    {

        $Config = new Config;

        //
        $token = $request->token;
        $cektoken = tblOrders::where([
            'token'         =>  $token,
            'status'        =>  1
        ])
        ->first();

        

        if( $cektoken == null )
        {
            return response()->json(['message'=>'Token tidak ditemukan'], 404);
        }

        $fields = json_decode($cektoken->field);
        $customers = $fields->customers;
        $sales = $fields->sales;
        $phonecus = "62".(int)$customers->phone;
        
        // linkinvoice = store/invoice?token
        $WEBWASENDTEXT = $Config->apps()["URL"]["WEBWASENDTEXT"];
        $WEBWASENDTEXT = str_replace("{{phone}}", $phonecus, $WEBWASENDTEXT);

        $linkinvoice = $Config->apps()["URL"]["STORE"] . "/invoice/v1?token=" . $cektoken->token;
        //
        $WAMESEND = $Config->apps()["URL"]["WAMESEND"];
        $linkcs = str_replace("{{phone}}", ("62" . (int)$sales->phone), $WAMESEND);

        //getcontentwa
        $datatemplatewa = [
            'type'      =>  1,
            'subtype'   =>  1
        ];

        $gettemplatewa = new TemplateWA;
        $gettemplatewa = $gettemplatewa->main($datatemplatewa);
        $content = str_replace("{{name}}", $customers->name, $gettemplatewa["content"]);
        $content = str_replace("{{linkinvoice}}", $linkinvoice, $content);
        $content = str_replace("{{linkcs}}", $linkcs, $content);
        // $content = str_replace("{{namecs}}", $sales->name, $content);
        // $content = str_replace("{{phonecs}}", "0".(int)$sales->phone, $content);
        

        $linksend = str_replace("{{text}}", rawurlencode($content), $WEBWASENDTEXT);
        
 
        $data = [
            'message'       =>  '',
            'template'      =>  rawurlencode($content),
            'phonecus'      =>  $phonecus,
            "linksend"      =>  $linksend
        ];


        return response()->json($data, 200);
    }

    public function shiping(Request $request)
    {

        $Config = new Config;

        //
        $token = $request->token;
        $cektoken = tblOrders::from('orders as o')
        ->select(
            'o.token', 'o.field'
        )
        ->leftJoin('order_shipings as os', function($join)
        {
            $join->on('os.order_id', '=', 'o.id')
            ->where([
                'os.status'    =>  1
            ]);
        })
        ->where([
            ['o.token', '=', $token],
            ['o.status', '=', 1],
            ['os.noresi', '<>', '']
        ])
        ->first();

        if( $cektoken == null )
        {
            return response()->json(['message'=>'Token tidak ditemukan'], 404);
        }

        $fields = json_decode($cektoken->field);
        $customers = $fields->customers;
        $shiping = $fields->shiping;
        $sales = $fields->sales;
        $phonecus = "62".(int)$customers->phone;
        
        // linkinvoice = store/invoice?token
        $WEBWASENDTEXT = $Config->apps()["URL"]["WEBWASENDTEXT"];
        $WEBWASENDTEXT = str_replace("{{phone}}", $phonecus, $WEBWASENDTEXT);

        $linkinvoice = $Config->apps()["URL"]["STORE"] . "/invoice/v1?token=" . $cektoken->token;
        //
        $WAMESEND = $Config->apps()["URL"]["WAMESEND"];
        $linkcs = str_replace("{{phone}}", ("62" . (int)$sales->phone), $WAMESEND);

        //getcontentwa
        $datatemplatewa = [
            'type'      =>  1,
            'subtype'   =>  2
        ];

        $gettemplatewa = new TemplateWA;
        $gettemplatewa = $gettemplatewa->main($datatemplatewa);
        $content = str_replace("{{name}}", $customers->name, $gettemplatewa["content"]);
        $content = str_replace("{{kurir}}", $shiping->courier_name, $content);
        $content = str_replace("{{noresi}}", $shiping->noresi, $content);
        $content = str_replace("{{linkcs}}", $linkcs, $content);
        // $content = str_replace("{{namecs}}", $sales->name, $content);
        // $content = str_replace("{{phonecs}}", "0".(int)$sales->phone, $content);
        

        $linksend = str_replace("{{text}}", rawurlencode($content), $WEBWASENDTEXT);
        
 
        $data = [
            'message'       =>  'shiping',
            'template'      =>  rawurlencode($content),
            'phonecus'      =>  $phonecus,
            "linksend"      =>  $linksend
        ];


        return response()->json($data, 200);
    }
}