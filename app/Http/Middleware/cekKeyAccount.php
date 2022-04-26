<?php
namespace App\Http\Middleware;
// use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\users as tblUsers;

use Closure;

class cekKeyAccount
{
    public function handle($request, Closure $next)
    {

        $cekkey = tblUsers::where([
            'token'         =>  $request->header('key'),
            'status'        =>  1
        ])->first();

        if( $cekkey == null )
        {
            $data = [
                'message'       =>  'Key tidak valid!'
            ];

            return response()->json($data, 401);
        }

        

        return $next($request);
    }
}