<?php
namespace App\Http\Middleware;
// use Illuminate\Http\Request;
use Illuminate\Http\Response;
use DB;

use Closure;

class CekRequest
{

    public function handle($request, Closure $next)
    {

    
        if( $request->method() === 'GET' ) //ceking method GET
        {
            
            if( $this->methodget($request) != '')
            {
                $data = [
                    'message'       =>  $this->methodget($request)
                ];
                return response()->json($data, 401);
            }
        }
        else 
        {
            // ceking method POST
            if( $this->methodpost($request) != '')
            {
                $data = [
                    'message'       =>  $this->methodpost($request)
                ];
                return response()->json($data, 401);
            }
        }
        

        return $next($request);
        
    }


    //ceking method get
    public function methodget($request)
    {

        $message = $request->header('Content-Type') != 'application/json' ?  'Error! Harap gunakan Content-Type="application/json" dan row data JSON pada request anda' : '';


        return $message;

    }


    // ceking method post
    public function methodpost($request)
    {

        // $header = $request->header('Content-Type');
        // $arrtype = ['application/json', 'multipart/form-data'];
        // $cektype = array_search($header, $arrtype) === false ? 0 : 1;

        $header = $request->header('Content-Type');
        $cheader = explode(";", $header);
        $arrtype = ['application/json', 'multipart/form-data'];
        $cektype = array_search($cheader[0], $arrtype) === false ? 0 : 1;

        // not 'application/json', 'multipart/form-data'
        if( $cektype == 0 )
        {
            $message = 'Error! Harap gunakan Content-Type: "application/json" atau "multipart/form-data" ';
        }
        else
        {

            //application/json
            if( $header == 'application/json' && count($request->json() ) == 0)
            {
                
                $message = 'Error! Harap gunakan type data "Raw/JSON"';
            }
            else
            {
                // multipart/form-data
                if( $header == 'multipart/form-data' && count($request->json() ) > 0 )
                {
                    $message = 'Error! Harap gunakan type body "form-data"';
                }
                else
                {
                    $message = '';
                }
    
            }
        }
        

        return $message;
    }
}