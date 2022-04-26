<?php
namespace App\Http\Controllers\customers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\customers as tblCustomers;
use DB;

class table extends Controller
{

    public function main(Request $request)
    {
        $Config = new Config;

        //
        $src = '%' . str_replace(';', '', trim($request->search) ) . '%';
        $paging = trim($request->paging);
        $sort = trim($request->sort_name);

        $getdata = tblCustomers::from('customers as c')
        ->select(
            'c.id', 'c.name', 'c.token', 'c.address', 'c.kodepos', 'c.owner', 'c.phone', 'c.email', 'c.created_at as date',
            'ct.alias as type_name',
            'aop.name as provinsi_name',
            'aoc.name as city_name', 'aoc.type_label as city_label',
            'aok.name as kecamatan_name',
            'u.name as admin',
            DB::raw('IFNULL(ud.url, "") as file')
        )
        ->leftJoin('customer_types as ct', function($join)
        {
            $join->on('ct.id', '=', 'c.type');
        })
        ->leftJoin('app_origin_provinsis as aop', function($join)
        {
            $join->on('aop.id', '=', 'c.provinsi');
        })
        ->leftJoin('app_origin_cities as aoc', function($join)
        {
            $join->on('aoc.id', '=', 'c.city');
        })
        ->leftJoin('app_origin_kecamatans as aok', function($join)
        {
            $join->on('aok.id', '=', 'c.kecamatan');
        })
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'c.user_id');
        })
        ->leftJoin('upload_documents as ud', function($join)
        {
            $join->on('ud.link_id', '=', 'c.id')
            ->where([
                'ud.type'       =>  2,
                'ud.status'     =>  1
            ]);
        })
        ->where([
            ['c.search', 'like', $src],
            ['c.status', '=', 1]
        ]);

        $count = $getdata->count();

        if( $count == 0)
        {
            $data = [
                'message'       =>  'Data tidak ditemukan'
            ];

            return response()->json($data, 404);
        }


        $gettable = $getdata->orderBy('c.name', $sort)
        ->take($Config->table(['paging'=>$paging])['paging_item'])
        ->skip($Config->table(['paging'=>$paging])['paging_limit'])
        ->get();

        foreach($gettable as $row)
        {
            $list[] = [
                'id'            =>  $row->id,
                'token'         =>  $row->token,
                'name'          =>  $row->type_name . ' ' . $row->name,
                'type'          =>  $row->type_name,
                'owner'         =>  $row->owner,
                'phone'         =>  $row->phone,
                'email'         =>  $row->email,
                'address'       =>  $row->address,
                'address2'          =>  ucwords($row->kecamatan_name) . ' - ' . $row->city_label . '. ' . ucwords($row->city_name),
                'address3'          =>  'Prov. ' . $row->provinsi_name . ' - ' . $row->kodepos,
                'date'          =>  $Config->timeago($row->date),
                'admin'         =>  $Config->nickName($row->admin),
                'npwp'          =>  $row->file
            ];
        }

        $data = [
            'message'       =>  '',
            'response'      =>  [
                'list'          =>  $list,
                'paging'        =>  $paging,
                'total'         =>  $count,
                'countpage'     =>  ceil($count / $Config->table(['paging'=>$paging])['paging_item'] )
            ]
        ];

        return response()->json($data, 200);
        

    }

    //
    public function back_main(Request $request)
    {
        //default config
        $Config = new Config;

        //ceking refresh
        // $Refresh = new Refresh;
        // $Refresh = $Refresh->refresh();

        $account = new \App\Http\Controllers\account\index;
        $account = $account->viewtype([
            'type'      =>  'key',
            'token'     =>  $request->header('key')
        ]);

        //request
        $paging = trim($request->pg);
        $search = '%' . trim($request->src) . '%';
        $sort_name = trim($request->sort);

        $taging = trim($request->taging);
        $register = trim($request->reg);
        $progress = trim($request->progress);
        $source = trim($request->source);
        $companyid = trim($request->companyid);
        $csid = trim($request->csid);


        if( $register != '')
        {
            $register = explode(";", $register);
            $start_date = $register[0];
            $end_date = $register[1];

            $end_date = date('Y-m-d', strtotime($end_date . '+1 day') );
        }
        else
        {
            $register = '';
            $start_date = '';
            $end_date = '';
        }

        $taging = explode(',', $taging);

        //cekin table
        // $cektable = DB::table('customers');
        // foreach($taging as $tag)
        // {
        //     $cektable->orWhere('taging', 'like', '%' . $tag . '%');
        //     if( $progress != '-1')
        //     {
        //         $cektable->where(['progress'=>$progress]);
        //     }
        //     if( $source != '-1')
        //     {
        //         $cektable->where(['source'=>$source]);
        //     }
        //     if( $companyid != '-1')
        //     {
        //         $cektable->where(['company_id'=>$companyid]);
        //     }
        //     if( $csid != '-1')
        //     {
        //         $cektable->where(['user_id'=>$csid]);
        //     }
        //     if( $register != '')
        //     {
        //         $cektable->whereBetween('created_at', [$start_date, $end_date]);
        //     }
        //     $cektable->where('search','like',$search);

        // }
        // $cektable = $cektable->count();
        
        $getdata = tblData::from('customers as c')
        ->select(
            'c.id', 'c.token', 'c.name', 'c.phone', 'c.email', 'c.gender', 'c.taging', 'c.created_at as date',
            'cp.id as progress_id', 'cp.name as progress_name', 'cp.color as progress_color',
            'c.user_id as admin_id', 'u.name as admin_name',
            'cs.name as source',
            'uc.name as company_name'
        )
        ->leftJoin('customer_progresses as cp', function($join)
        {
            $join->on('cp.id', '=', 'c.progress')
            ->where('cp.status', '=', 1);
        })
        ->leftJoin('users as u', function($join)
        {
            $join->on('u.id', '=', 'c.user_id');
        })
        ->leftJoin('customer_sources as cs', function($join)
        {
            $join->on('cs.id', '=', 'c.source');
        })
        ->leftJoin('user_companies as uc', function($join)
        {
            $join->on('uc.id', '=', 'c.company_id');
        });
        foreach($taging as $tag)
        {
            $getdata->orWhere('c.taging', 'like', '%' . $tag . '%');
            if( $progress != '-1')
            {
                $getdata->where(['c.progress'=>$progress]);
            }
            if( $source != '-1')
            {
                $getdata->where(['c.source'=>$source]);
            }
            if( $companyid != '-1')
            {
                $getdata->where(['c.company_id'=>$companyid]);
            }
            if( $csid != '-1')
            {
                $getdata->where(['c.user_id'=>$csid]);
            }
            if( $register != '')
            {
                $getdata->whereBetween('c.created_at', [$start_date, $end_date]);
            }
            $getdata->where('c.search', 'like', $search);
        }

        $cektable = $getdata->count();


        if( $cektable > 0)
        {
            $status = 200;


            // //
            // $getlist = tblData::from('customers as c')
            // ->select(
            //     'c.id', 'c.token', 'c.name', 'c.phone', 'c.email', 'c.gender', 'c.taging', 'c.created_at as date',
            //     'cp.id as progress_id', 'cp.name as progress_name', 'cp.color as progress_color',
            //     'c.user_id as admin_id', 'u.name as admin_name',
            //     'cs.name as source',
            //     'uc.name as company_name'
            // )
            // ->leftJoin('customer_progresses as cp', function($join)
            // {
            //     $join->on('cp.id', '=', 'c.progress')
            //     ->where('cp.status', '=', 1);
            // })
            // ->leftJoin('users as u', function($join)
            // {
            //     $join->on('u.id', '=', 'c.user_id');
            // })
            // ->leftJoin('customer_sources as cs', function($join)
            // {
            //     $join->on('cs.id', '=', 'c.source');
            // })
            // ->leftJoin('user_companies as uc', function($join)
            // {
            //     $join->on('uc.id', '=', 'c.company_id');
            // });
            // foreach($taging as $tag)
            // {
            //     $getlist->orWhere('c.taging', 'like', '%' . $tag . '%');
            //     if( $progress != '-1')
            //     {
            //         $getlist->where(['c.progress'=>$progress]);
            //     }
            //     if( $source != '-1')
            //     {
            //         $getlist->where(['c.source'=>$source]);
            //     }
            //     if( $companyid != '-1')
            //     {
            //         $getlist->where(['c.company_id'=>$companyid]);
            //     }
            //     if( $csid != '-1')
            //     {
            //         $getlist->where(['c.user_id'=>$csid]);
            //     }
            //     if( $register != '')
            //     {
            //         $getlist->whereBetween('c.created_at', [$start_date, $end_date]);
            //     }
            //     $getlist->where('c.search', 'like', $search);
            // }
            
            $getlist = $getdata->orderBy('c.id', 'desc')
            ->orderBy('c.name', $sort_name)
            ->take($Config->table(['paging'=>$paging])['paging_item'])
            ->skip($Config->table(['paging'=>$paging])['paging_limit'])
            ->get();


            foreach($getlist as $row)
            {

                $gettag = $row->taging === '' ? '' : DB::table('customer_tags')->whereIn('id', json_decode($row->taging) )->get();


                $admin = explode(' ', $row->admin_name);

                $getnote = DB::table('customer_notes')
                ->where([
                    'customer_id'            =>  $row->id
                ])->orderBy('id', 'desc')->first();

                $getod = tblOrders::where([
                    'customer_id'           =>  $row->id,
                    'paid'                  =>  1,
                    'status'                =>  1   
                ])->count();

                $list[] = [
                    'id'                    =>  $row->id,
                    'url'                   =>  $row->token,
                    'customer_name'         =>  $row->name,
                    'customer_gender'       =>  $row->gender === 1 ? 'male' : 'female',
                    'customer_phone'        =>  $row->phone,
                    'customer_email'        =>  $row->email,
                    'progress_id'           =>  $row->progress_id,
                    'progress_name'         =>  $row->progress_name,
                    'progress_color'        =>  $row->progress_color,
                    'taging'                =>  $gettag,
                    'source'                =>  $row->source,
                    'note'                  =>  $getnote === null ? '' : $getnote->text,
                    'admin_name'            =>  $admin[0],
                    'admin_id'              =>  $row->admin_id,
                    'date'                  =>  $Config->timeago($row->date),
                    'orders'                =>  $getod,
                    'company_name'          =>  $row->company_name
                ];
            }

            $message = '';
            $response = [
                'list'          =>  $list,
                'paging'        =>  $paging,
                'total'         =>  $cektable,
                'countpage'     =>  ceil($cektable / $Config->table(['paging'=>$paging])['paging_item'] )
            ];

        }
        else
        {
            $status = 404;
            $message = 'Data tidak ditemukan';
        }

        //
        $data = [
            // 'refresh'           =>  $Refresh,
            'message'           =>  $message,
            'response'          =>  $status === 200 ? $response : '',
            'cektable'          =>  $cektable
        ];

        return response()->json($data, $status);
    }
}