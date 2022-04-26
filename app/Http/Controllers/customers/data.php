<?php
namespace App\Http\Controllers\customers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\customers as tblCustomers;
use App\customer_types as tblCustomerTypes;
use App\Http\Controllers\config\index as Config;

class data extends Controller
{
    //
    public function main(Request $request)
    {
        $getdata = tblCustomerTypes::where([
            'status'      =>  1
        ])->get();


        $data = [
            'message'       =>  '',
            'response'      =>  $getdata
        ];

        return response()->json($data, 200);
    }


    public function listmodal(Request $request)
    {
        $Config = new Config;

        //
        $search = '%' . trim($request->search) . '%';
        $paging = trim($request->pg);

        $getdata = tblCustomers::from('customers as c')
        ->select(
            'c.id', 'c.name',
            'ct.alias as type_label'
        )
        ->leftJoin('customer_types as ct', function($join)
        {
            $join->on('ct.id', '=', 'c.type');
        })
        ->where([
            ['c.name',    'like', $search]
        ]);

        $count = $getdata->count();

        if($count == 0)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }

        $gettable = $getdata->orderBy('c.name', 'asc')
        ->take($Config->table(['paging'=>$paging])['paging_item'])
        ->skip($Config->table(['paging'=>$paging])['paging_limit'])
        ->get();

        foreach($gettable as $row)
        {
            $list[] = [
                'id'            =>  $row->id,
                'name'          =>  $row->type_label . ' ' . $row->name
            ];
        }

        $data = [
            'message'       =>  '',
            'response'      =>  $list
        ];

        return response()->json($data, 200);

        
    }
}