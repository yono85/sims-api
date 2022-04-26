<?php
namespace App\Http\Controllers\export\orders;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\orders as tblOrders;
use App\Http\Controllers\config\index as Config;
use App\Http\Controllers\dashboard\report as Report;
use DB;

class index extends Controller
{
    //
    public function main(Request $request)
    {

        $Config = new Config;
        $Report = new Report;

        //
        $status = trim($request->status);
        $compid = trim($request->compid);
        $csid = trim($request->csid);

        //
        $date = $Report->changeDate($request->date);

        $getdata = DB::table('vw_export_orders as veo')
        ->select('*', 'vecc.total', 'vecc.customer_date')
        ->leftJoin('vw_eo_countcus as vecc', function($join)
        {
            $join->on('vecc.customer_id', '=', 'veo.customer_id');
        });
        if( $csid <> "-1")
        {
            $getdata = $getdata->where([
                'veo.user_id'   =>  $csid,
                'veo.status'    =>  1
            ]);
        }
        if( $status <> "-1")
        {
            if( $status == "1")
            {
                $getdata = $getdata->where([
                    'veo.checkout'      =>  1,
                    'veo.payment'       =>  0,
                    'veo.paid'          =>  0,
                ]);
            }
            elseif( $status == "2")
            {
                $getdata = $getdata->where([
                    'veo.checkout'      =>  1,
                    'veo.payment'       =>  1,
                    'veo.paid'          =>  0,
                ]);
            }
            else
            {
                $getdata = $getdata->where([
                    'veo.checkout'      =>  1,
                    'veo.payment'       =>  1,
                    'veo.paid'          =>  1,
                ]);
            }
            
        }
        $getdata = $getdata->where([
            'veo.company_id'    =>  trim($request->compid)
        ])
        ->whereBetween('veo.date', [$date['startDate'], $date['endDate']])
        ->whereIn('veo.type', [1,2,4]);
        

        $count = count($getdata->get());

        if( $count > 0 )
        {

            $datarecord[] =[
                ['text'         =>  'No'],
                ['text'         =>  'Invoice'],
                ['text'         =>  'Produk'],
                ['text'         =>  'Jumlah'],
                ['text'         =>  'Sub Total'],
                ['text'         =>  'Kurir'],
                ['text'         =>  'Berat Total'],
                ['text'         =>  'Biaya Kurir'],
                ['text'         =>  'Angka Uniq'],
                ['text'         =>  'Total Bayar'],
                ['text'         =>  'Metode pembayaran'],
                ['text'         =>  'Catatan'],
                ['text'         =>  'Nama Customer'],
                ['text'         =>  'Gender Customer'],
                ['text'         =>  'Handphone Customer'],
                ['text'         =>  'Email Customer'],
                ['text'         =>  'Alamat tujuan'],
                ['text'         =>  'Pemilik alamat'],
                ['text'         =>  'Kontak alamat'],
                ['text'         =>  'Provinsi tujuan'],
                ['text'         =>  'Kab/Kota tujuan'],
                ['text'         =>  'Kecamatan tujuan'],
                ['text'         =>  'Kodepos'],
                ['text'         =>  'Nama Marketing'],
                ['text'         =>  'Telepon Marketing'],
                ['text'         =>  'Toko'],
                ['text'         =>  'Tanggal Order'],
                ['text'         =>  'Tanggal Paid'],
                ['text'         =>  'Status']
            ];


            $getdata = $getdata;
            if( $status == "3")
            {
                $getdata = $getdata->orderBy('veo.date', 'asc');
            }
            else
            {
                $getdata = $getdata->orderBy('veo.created_at', 'asc');
            }
            $getdata = $getdata->get();
            $no = 1;
            foreach($getdata as $row)
            {
                $field = json_decode($row->field, true);
                $customer = $field['customers'];
                $product = $field['product'];
                $shiping = $field['shiping'];
                $payment = $field['payment'];
                $destination = $field['destination'];
                $sales = $field['sales'];

                $cusemail = isset($customer['email']) ? $customer['email'] : '';

                $datarecord[] =[
                    ['text'         =>  $no++],
                    ['text'         =>  $row->invoice],
                    ['text'         =>  $product['item']],
                    ['text'         =>  $product['quantity']],
                    ['text'         =>  number_format($payment['total'], 0, ',', '.') ],
                    ['text'         =>  $shiping['courier_name']],
                    ['text'         =>  $shiping['weight']],
                    ['text'         =>  number_format($shiping['courier_price'], 0, ',', '.') ],
                    ['text'         =>  '-' . $row->uniq],
                    ['text'         =>  number_format($row->bayar, 0, ',', '.')],
                    ['text'         =>  $payment['method_type'] === 'Trt' ? $payment['bank'] : $payment['method']],
                    ['text'         =>  $row->notes],
                    ['text'         =>  $customer['name']],
                    ['text'         =>  $customer['gender']],
                    ['text'         =>  $customer['phone']],
                    ['text'         =>  $cusemail],
                    ['text'         =>  $destination['address']],
                    ['text'         =>  $destination['name']],
                    ['text'         =>  $destination['phone']],
                    ['text'         =>  $destination['provinsi']],
                    ['text'         =>  $destination['city']],
                    ['text'         =>  $destination['kecamatan']],
                    ['text'         =>  $destination['kodepos']],
                    ['text'         =>  $sales['name']],
                    ['text'         =>  '0'.(int)$sales['phone']],
                    ['text'         =>  $sales['company']],
                    ['text'         =>  date('d-m-Y H:i', strtotime($row->created_at))],
                    ['text'         =>  date('d-m-Y H:i', strtotime($row->date))],
                    ['text'         =>  $row->paid === 1 ? 'Berhasil' : ( $row->paid === 0 && $row->payment === 1 ? 'Verifikasi Pembayaran' : 'Menunggu Pembayaran') ]
                ];
            }
    
            $data = [
                'message'           =>  '',
                'response'          =>  [
                    'title'             =>  'Laporan_orders-' . date('Y-m-d H:i:s', time()),
                    'data'              =>  $datarecord
                ]
            ];

            return response()->json($data, 200);
        }
        else
        {
            $data = [
                'message'       =>  'Data laporan yang Anda minta tidak ditemukan'
            ];

            return response()->json($data, 404);
            
        }

    }

    public function dashboard(Request $request)
    {
        $Config = new Config;
        $Report = new Report;

        $date = $Report->changeDate($request->date);

        $getdata = DB::table('vw_export_orders as veo')
        ->select('*', 'vecc.total', 'vecc.customer_date')
        ->leftJoin('vw_eo_countcus as vecc', function($join)
        {
            $join->on('vecc.customer_id', '=', 'veo.customer_id');
        })
        ->whereBetween('veo.date', [$date['startDate'], $date['endDate']])
        ->where([
            'veo.paid'      =>  1,
            'veo.company_id'    =>  trim($request->compid),
            'veo.status'        =>  1
        ])
        ->whereIn('veo.type', [1,2,4]);

        $count = count($getdata->get());

        if( $count > 0 )
        {
            $datarecord[] =[
                ['text'         =>  'No'],
                ['text'         =>  'Invoice'],
                ['text'         =>  'Produk'],
                ['text'         =>  'Jumlah'],
                ['text'         =>  'Sub Total'],
                ['text'         =>  'Kurir'],
                ['text'         =>  'Berat Total'],
                ['text'         =>  'Biaya Kurir'],
                ['text'         =>  'Angka Uniq'],
                ['text'         =>  'Total Bayar'],
                ['text'         =>  'Metode pembayaran'],
                ['text'         =>  'Catatan'],
                ['text'         =>  'Nama Customer'],
                ['text'         =>  'Gender Customer'],
                ['text'         =>  'Handphone Customer'],
                ['text'         =>  'Email Customer'],
                ['text'         =>  'Alamat tujuan'],
                ['text'         =>  'Pemilik alamat'],
                ['text'         =>  'Kontak alamat'],
                ['text'         =>  'Provinsi tujuan'],
                ['text'         =>  'Kab/Kota tujuan'],
                ['text'         =>  'Kecamatan tujuan'],
                ['text'         =>  'Kodepos'],
                ['text'         =>  'Nama Marketing'],
                ['text'         =>  'Telepon Marketing'],
                ['text'         =>  'Toko'],
                ['text'         =>  'Info Belanja'],
                ['text'         =>  'Tanggal Add Lead'],
                ['text'         =>  'Tanggal Paid']
            ];
    
            //
            $getdata = $getdata->orderBy('veo.date', 'asc')
            ->get();
            $no = 1;
            foreach($getdata as $row)
            {
                $field = json_decode($row->field, true);
                $customer = $field['customers'];
                $product = $field['product'];
                $shiping = $field['shiping'];
                $payment = $field['payment'];
                $destination = $field['destination'];
                $sales = $field['sales'];

                $cusemail = isset($customer['email']) ? $customer['email'] : '';

                $datarecord[] =[
                    ['text'         =>  $no++],
                    ['text'         =>  $row->invoice],
                    ['text'         =>  $product['item']],
                    ['text'         =>  $product['quantity']],
                    ['text'         =>  number_format($payment['total'], 0, ',', '.') ],
                    ['text'         =>  $shiping['courier_name']],
                    ['text'         =>  $shiping['weight']],
                    ['text'         =>  number_format($shiping['courier_price'], 0, ',', '.') ],
                    ['text'         =>  '-' . $row->uniq],
                    ['text'         =>  number_format($row->bayar, 0, ',', '.')],
                    ['text'         =>  $payment['method_type'] === 'Trt' ? $payment['bank'] : $payment['method']],
                    ['text'         =>  $row->notes],
                    ['text'         =>  $customer['name']],
                    ['text'         =>  $customer['gender']],
                    ['text'         =>  $customer['phone']],
                    ['text'         =>  $cusemail],
                    ['text'         =>  $destination['address']],
                    ['text'         =>  $destination['name']],
                    ['text'         =>  $destination['phone']],
                    ['text'         =>  $destination['provinsi']],
                    ['text'         =>  $destination['city']],
                    ['text'         =>  $destination['kecamatan']],
                    ['text'         =>  $destination['kodepos']],
                    ['text'         =>  $sales['name']],
                    ['text'         =>  '0'.(int)$sales['phone']],
                    ['text'         =>  $sales['company']],
                    ['text'         =>  $row->total . 'x'],
                    ['text'         =>  date('d-m-Y H:i', strtotime($row->customer_date))],
                    ['text'         =>  date('d-m-Y H:i', strtotime($row->date))]
                ];
            }
    
            $data = [
                'title'     =>  'Report_dashboard_' . $date['startDate'] . '_' . $date['endDate'],
                'record'    =>  $count,
                'data'      =>  $datarecord
            ];
    
    
            return response()->json($data, 200);
        }
        else
        {
            $data = [
                'message'       =>  'Data export yang Anda minta tidak ditemukan'
            ];

            return response()->json($data, 404);
        }
        
    }
}