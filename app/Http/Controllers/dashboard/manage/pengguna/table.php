<?php
namespace App\Http\Controllers\dashboard\manage\pengguna;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use DB;

class table extends Controller
{
    //
    public function main(Request $request)
    {
        $Config = new Config;

        //
        $sublevel = trim($request->sublevel);
        $search = trim($request->search);
        $paging = trim($request->paging);
        $sort = trim($request->sort);
    }
}