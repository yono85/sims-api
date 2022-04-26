<?php
namespace App\Http\Controllers\export\bulkings;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;

class index extends Controller
{
    //
    public function main(Request $request)
    {
        $Config = new Config;

        //
        $bulking_id = trim($request->id);

        
        
    }
}