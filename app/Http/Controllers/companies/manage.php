<?php
namespace App\Http\Controllers\companies;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\app_metode_payments as tblAppMetodePayments;
use App\Http\Controllers\config\index as Config;

class manage extends Controller
{

    public function getpaymentlist(Request $request)
    {

        $data = $this->paymentlist([
            'company_id'=>$request->id,
            'type'      =>  1
        ]);

        
        return response()->json($data, 200);
    }

    //
    public function paymentlist($request)
    {
        
        $Config = new Config;

        $getdata = tblAppMetodePayments::from('app_metode_payments as amp')
        ->select(
            'amp.id', 'amp.type', 'amp.account_name', 'amp.account_norek',
            'abl.label', 'abl.image'
        )
        ->where([
            'amp.company_id'    =>  $request['company_id'],
            'amp.status'        =>  1
        ])
        ->leftJoin('app_bank_lists as abl', function($join){
            $join->on('abl.id', '=', 'amp.bank_id');
        });
        if( $request['type'] != '-1')
        {
            $getdata = $getdata->where([
                'amp.type'         =>   $request['type']
            ]);
        }
        $getdata = $getdata->get();


        foreach($getdata as $row)
        {
            $list[] = [
                'id'        =>  $row->id,
                'type'      =>  $row->type,
                'name'      =>  'Bank ' . $row->label,
                'account_name'  =>  $row->account_name,
                'account_norek' =>  $row->account_norek,
                'label'     =>  $row->account_name . ' - ' . $row->account_norek,
                'images'    =>  $Config->apps()['storage']['URL'] . '/images/bank/' . $row->image
            ];
        }


        return $list;
    }
}