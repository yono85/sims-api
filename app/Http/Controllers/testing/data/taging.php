<?php
namespace App\Http\Controllers\testing\data;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
class taging extends Controller
{
    //
    public function main()
    {



        $key = '1,3';
        $key = explode(',', $key);

        $p = '3';

        // foreach ($key as $k) 
        // {
        // //    $gettable->where('taging', 'like', '%' . $k. '%');
        //     $kay[] = $k;
        // }

        $gettable = DB::table('customers')
        ->select('name');
        
        foreach($key as $k)
        {
            $gettable->orWhere('taging', 'like', '%' . $k . '%');
            if( $p != '-1')
            {
                $gettable->where(['progress'=>$p]);
            }
        }

        $gettable = $gettable->get();
        



        return json_encode($gettable);
        // $gettable = $gettable->get();

        

        // $gettable = DB::table('customers')
        // ->select('name')->get(array('taging'))->toArray();

        // return response()->json($gettable);

    }
    
}