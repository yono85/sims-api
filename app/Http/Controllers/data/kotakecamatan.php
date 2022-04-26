<?php
namespace App\Http\Controllers\data;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\app_origin_kecamatan as tblKecamatans;
use App\app_origin_city as tblCity;
use DB;

class kotakecamatan extends Controller
{
    //
    public function list(Request $request)
    {

        //
        $Config = new Config;


        //
        $q = '%' . trim($request->q) . '%';
        $paging = trim($request->p);

        $cekkecamatan = DB::table('vw_src_kecamatans')
        ->where([
            ['name', 'LIKE', $q]
        ]);

        //kab
        $cekcity = DB::table('vw_src_city')
        ->where([
            ['name', 'LIKE', $q]
        ])
        ->union($cekkecamatan)
        ->count();

        
        if( $cekcity > 0 )
        {
            // $item = $Config->scroll()['item'];
            // $limit = (($paging - 1) * $item);
            // $countpage = ceil( $cekcity /$item);

            $item = $Config->scroll(['paging'=>$paging])['paging_item'];
            $limit = $Config->scroll(['paging'=>$paging])['paging_limit'];
            $countpage = ceil( $cekcity /$item);

            

            //kec
            $kecamatan = DB::table('vw_src_kecamatans')
            ->where([
                ['name', 'LIKE', $q]
            ]);

            //kab
            $city = DB::table('vw_src_city')
            ->where([
                ['name', 'LIKE', $q]
            ])
            ->union($kecamatan)
            ->skip($limit)
            ->take($item)
            ->get();
            

            foreach( $city as $row )
            {
        
                if( $row->type === "Kec" )
                {
                    $second = $row->sub_type . ". " . ucwords(strtolower($row->sub));
                    $last = $row->type . ". " . ucwords(strtolower(trim($row->name)));
                }
                else
                {
                    $second = $row->type . ". " . ucwords(strtolower(trim($row->name)));
                    $last = $row->sub_type . ". " . ucwords(strtolower($row->sub));
                }


                $list[] = [
                    'id'        =>  $row->prov_id . "," . $row->kab_id . "," . $row->kec_id,
                    'label'     => $last. ", " . $second . ", " . $row->provinsi
                ];

            }

            $data = [
                'list'  =>  $list,
                'lastpaging'        =>  $countpage
            ];

            $status = 200;
        }
        else
        {
            $status = 404;
        }
        // //
        $data = [
            'message'           =>  '',
            'response'          =>  $status === 200 ? $data : '',
            'cek'               =>  $cekcity
        ];


        return response()->json($data, $status);
    }

    public function listprovinsi(Request $request)
    {

        //
        $Config = new Config;


        //
        $q = '%' . trim($request->q) . '%';
        $paging = trim($request->p);
        $provinsi = trim($request->provinsi);

        $cekkecamatan = DB::table('vw_src_kecamatans')
        ->where([
            ['name', 'LIKE', $q],
            ['prov_id', '=', $provinsi]
        ]);

        //kab
        $cekcity = DB::table('vw_src_city')
        ->where([
            ['name', 'LIKE', $q],
            ['prov_id', '=', $provinsi]
        ])
        ->union($cekkecamatan);

        $count = $cekcity->count();

        // ->count();

        
        if( $count > 0 )
        {
            // $item = $Config->scroll()['item'];
            // $limit = (($paging - 1) * $item);
            // $countpage = ceil( $cekcity /$item);

            $item = $Config->scroll(['paging'=>$paging])['paging_item'];
            $limit = $Config->scroll(['paging'=>$paging])['paging_limit'];
            $countpage = ceil( $count /$item);

            

            //kec
            // $kecamatan = DB::table('vw_src_kecamatans')
            // ->where([
            //     ['name', 'LIKE', $q],
            //     ['prov_id', '=', $provinsi]
            // ]);

            // //kab
            // $city = DB::table('vw_src_city')
            // ->where([
            //     ['name', 'LIKE', $q],
            //     ['prov_id', '=', $provinsi]
            // ])
            // ->union($kecamatan)
            $gettable = $cekcity->skip($limit)
            ->take($item)
            ->get();
            

            foreach( $gettable as $row )
            {
        
                if( $row->type === "Kec" )
                {
                    $second = $row->sub_type . ". " . ucwords(strtolower($row->sub));
                    $last = $row->type . ". " . ucwords(strtolower(trim($row->name)));
                }
                else
                {
                    $second = $row->type . ". " . ucwords(strtolower(trim($row->name)));
                    $last = $row->sub_type . ". " . ucwords(strtolower($row->sub));
                }


                $list[] = [
                    'id'        =>  $row->prov_id . "," . $row->kab_id . "," . $row->kec_id,
                    'label'     => $last. ", " . $second . ", " . $row->provinsi
                ];

            }

            $data = [
                'list'  =>  $list,
                'lastpaging'        =>  $countpage
            ];

            $status = 200;
        }
        else
        {
            $status = 404;
        }
        // //
        $data = [
            'message'           =>  '',
            'response'          =>  $status === 200 ? $data : '',
            'cek'               =>  $count
        ];


        return response()->json($data, $status);
    }
}