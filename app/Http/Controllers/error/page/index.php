<?php
namespace App\Http\Controllers\error\page;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class index extends Controller
{

    public function notfound()
    {
        $data = [
            'message'       =>  '404 | Page not found!'
        ];

        return response()->json($data, 404);
    }

    //
    public function get()
    {

        $data = [
            'message'       =>  'Page not found!'
        ];

        return response()->json($data, 404);
    }


    //
    public function post()
    {

        $data = [
            'message'       =>  'Page not found!'
        ];

        return response()->json($data, 404);
    }


}