<?php
namespace App\Http\Controllers\export\customers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\access\manage as Refresh;
use App\Http\Controllers\config\index as Config;
use App\customers as tblCustomers;
use DB;

class index extends Controller
{
    //
    public function main(Request $request)
    {
        //default config
        $Config = new Config;

        //ceking refresh
        $Refresh = new Refresh;
        $Refresh = $Refresh->refresh();

        $account = $Refresh['refresh']['account'];

        $cekrecord = tblCustomers::where([
            'status'            =>  1
        ]);
        if($account['level'] == '1')
        {
            if( $account['sublevel'] == '3')
            {
                $cekrecord = $cekrecord->where([
                    'user_id'       =>  $account['id'],
                    'company_id'    =>  $account['config']['company_id']
                ]);
            }   
        }
        $cekrecord = $cekrecord->count();

        if( $cekrecord > 0 )
        {
            $datarecord[] =[
                ['text'         =>  'No'],
                ['text'         =>  'Nama'],
                ['text'         =>  'Whatsapp'],
                ['text'         =>  'Email'],
                ['text'         =>  'Progress'],
                ['text'         =>  'Tag'],
                ['text'         =>  'Tanggal']
            ];

            // get record
            $gatrecord = tblCustomers::select(
                'customers.id', 'customers.name', 'customers.phone', 'customers.email', 'customers.created_at', 'customers.taging',
                'cp.name as progress_name'
            )
            ->leftJoin('customer_progresses as cp', function($join) 
            {
                $join->on('cp.id','=','customers.progress');
            })
            ->where([
                'customers.status'        =>  1
            ]);
            if($account['level'] == '1')
            {
                if( $account['sublevel'] == '3')
                {
                    $gatrecord = $gatrecord->where([
                        'customers.user_id'       =>  $account['id'],
                        'customers.company_id'    =>  $account['config']['company_id']
                    ]);
                }   
            }
            $gatrecord = $gatrecord->get();

            
            $no = 1;
            foreach($gatrecord as $row)
            {

                    if( $row->taging != '')
                    {
                        $gettag = DB::table('customer_tags')->select('name')->whereIn('id', json_decode($row->taging) )->get();
                        
                        $listtag = [];
                        foreach($gettag as $rowx)
                        {
                            $listtag[] = $rowx->name;
                        }
                        
                        $listtag = implode(",", $listtag);

                    }
                    else
                    {
                        $listtag = '';
                    }
    
                    
                $datarecord[] = [
                    ['text'             =>  $no++],
                    ['text'             =>  $row->name],
                    ['text'             =>  $row->phone],
                    ['text'             =>  $row->email],
                    ['text'             =>  $row->progress_name],
                    ['text'             =>  $listtag],
                    ['text'             =>  date('d-m-Y', strtotime($row->created_at))]
                ];
            }
    
            $response = [
                'record'        =>  0,
                'title'         =>  'export_customers-' . date('Y-m-d H:i:s', time()),
                'data'          =>  $datarecord
            ];
        }
        

        $data = [
            'message'       =>  $cekrecord > 0 ? '' : 'Data tidak tersedia',
            'response'      =>  $cekrecord > 0 ? $response : '',
            'refresh'       =>  $Refresh,
            'account'       =>  $account,
            'count'         =>  $cekrecord
        ];


        return response()->json($data, 200);
    }
}