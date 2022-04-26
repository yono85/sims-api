<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use DB;
use App\Http\Controllers\config\index as Config;
use App\user_logins as tblUserlogins;

class Authenticate
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        
        $Config = new Config;
        
        // return $this->auth->guard()->getToken(); //cek token
        // return $this->auth->factory()->getTTL(); //cek TTL expire



        if ($this->auth->guard($guard)->guest()) //jika token tidak valid / expire
        {
            
            if( $Config->cekURI($request) == '/api/logout')
            {
                $logout = new \App\Http\Controllers\log\access\manage;
                $logout = $logout->logout($request->token);

                //response
                $data = [
                    'message'       =>  '',
                    'response'      =>  [
                        'redirect'      =>  '/login'
                    ]
                ];

                return response()->json($data, 401);

            }


            //
            $token = explode(' ', $request->header('Authorization'))[1];

            $ceklogins = tblUserlogins::where([
                'token_jwt'     =>  $token,
                'status'        =>  1
            ])->first();


            if( $ceklogins != null )
            {

                $token_update = $this->auth->guard($guard)->refresh();

                //update table user_logins
                $uplogins = tblUserlogins::where([
                    'token_jwt'     =>  $token,
                    'status'        =>  1
                ])
                ->update([
                    'token_jwt'     =>  $token_update
                ]);

                $this->auth->setToken($token_update);
                // $request->headers->set('Authorization','Bearer '.$token_update);


                // $data = [
                //     'refresh'           =>  $token_update
                // ];

                // return response()->json($data, 200);

                return $next($request);
            }


            // token not valid or expire
            $data = [
                'message'       =>  'Token tidak valid'
            ];

            return response()->json($data, 401);


        }
        else
        {

            if( $request->header('Authorization') == null) //jika header tidak ada Authorization
            {
                $data = [
                    'message'       =>  'Harap gunakan Authorization Bearer di Header'
                ];
                return response()->json($data, 401);
            }
            else
            {

                $token = explode(' ', $request->header('Authorization'))[1];
                $authtype = explode(' ', $request->header('Authorization'))[0];

                if( $authtype != 'Bearer' )
                {
                    $data = [
                        'message'           =>  'Harap gunakan Authorization Bearer'
                    ];

                    return response()->json($data, 401);
                }

                $ceking = tblUserlogins::where([
                    'token_jwt'     =>  $token,
                    'status'        =>  0
                ])->count();
    
                if( $ceking > 0) //jika user login di tempat yg berbeda
                {
                    $this->auth->guard($guard)->logout();
                    $data = [
                        'message'       =>  'Session login telah habis'
                    ];
                    return response()->json($data, 401);
                }
            }

            
        }


        return $next($request);
    }
}
