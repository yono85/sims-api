<?php
namespace App\Http\Controllers\config;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class index extends Controller
{

    public function arrSublevel($request)
    {
        $data = [
            '1'         =>  ['2','3','4','5','6'],
            '2'         =>  ['2','3','5','6']
        ];

        return $data[$request];
    }

    //create new id to insert data
    public function createnewid($request)
    {
        $test = (int)$request['value'];
        $length = ( (int)$request['length'] - 1);
        $sprint = sprintf('%0'.$length.'s', 0);

        $condition = [ 
            10 . $sprint =>  9,
            9 . $sprint  =>  8,
            8 . $sprint  =>  7,
            7 . $sprint  =>  6,
            6 . $sprint  =>  5,
            5 . $sprint  =>  4,
            4 . $sprint  =>  3,
            3 . $sprint  =>  2,
            2 . $sprint  =>  1
        ];

        $sprintnew = strlen($test) === (int)$request['length'] ? substr($test, 1) : $test;

        foreach($condition as $row => $val)
        {
            if( $test < $row )
            {
                $value = $val . sprintf('%0'.$length.'s', $sprintnew);;
            }
        }


        return $value;
    }

    //create new id to insert data
    public function createnewidnew($request)
    {
        $numb = (int)$request['value'];
        $numb++;

        $length = ( (int)$request['length'] - 1);
        $sprint = sprintf('%0'.$length.'s', 0);

        $condition = [ 
            10 . $sprint =>  9,
            9 . $sprint  =>  8,
            8 . $sprint  =>  7,
            7 . $sprint  =>  6,
            6 . $sprint  =>  5,
            5 . $sprint  =>  4,
            4 . $sprint  =>  3,
            3 . $sprint  =>  2,
            2 . $sprint  =>  1
        ];

        $sprintnew = strlen($numb) === (int)$request['length'] ? substr($numb, 1) : $numb;

        foreach($condition as $row => $val)
        {
            if( $numb < $row )
            {
                $value = $val . sprintf('%0'.$length.'s', $sprintnew);;
            }
        }

        return $value;
    }


    // default date for table
    public function date()
    {
        return date('Y-m-d H:i:s', time());
    }


    //number
    public function number($request)
    {
        return preg_replace('/\D/', '', $request);
    }


    //create new uniq (number and char A-Z)
    public function createuniq($q)
    {
        $length = (int)$q['length'];
        $value = (int)$q['value'];

        //
        $char = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ' . $value;
        $charlength = strlen($char);
        $rand = '';

        //
        for ($i = 0; $i < $length; $i++)
        {
            $rand .= $char[rand(0, $charlength - 1)];
        }
        return $rand;
    }


    //create uniq number
    public function createuniqnum($q)
    {
        $length = (int)$q['length'];
        $value = (int)$q['value'];

        //
        $char = '0123456789' . $value;
        $charlength = strlen($char);
        $rand = '';

        //
        for ($i = 0; $i < $length; $i++)
        {
            $rand .= $char[rand(0, $charlength - 1)];
        }
        return $rand;
    }


    //apps
    public function apps()
    {
        $data = [
            "company"       =>  [
                "name"          =>  env("APP_NAMELABEL"),
                "url_logo"      =>  env("URL_APP") . "/assets/icon/logo-bonne.png",
                "url"           =>  env("URL_APP"),
                "url_help"      =>  env("URL_APP")
                ],
            "crm"           =>  [
                // "name"          =>  "CRM Herbindo",
                "url"           =>  env("URL_APP"),
                "url_help"      =>  env("URL_APP")
                ],
            "app"      =>  [
                "url"           =>  env("URL_APP"),
                "url_help"      =>  env("URL_APP")
            ],
            "storage"        => [
                "URL"           =>  env("URL_S3"),
            ],
            "URL"           =>  [
                "WEBWASENDTEXT" =>  "https://web.whatsapp.com/send?phone={{phone}}&text={{text}}",
                "APIWASENDTEXT" =>  "https://api.whatsapp.com/send?phone={{phone}}&text={{text}}",
                "APIWASEND"     =>  "https://api.whatsapp.com/send?phone={{phone}}",
                "WAMESEND"      =>  "https://wa.me/{{phone}}",
                "S3"            =>  env("URL_S3")
            ]
        ];

        return $data;
    }


    public function rootapps($q)
    {
        $data = [
            '1'     =>  'crm',
            '2'     =>  'distributor',
            '3'     =>  'maklon',
            '4'     =>  'reseller',
            '9'     =>  'app'
        ];


        return $data[$q];
    }


    public function subURI()
    {
    	$subURI = explode("/", url()->full());
    	return $subURI;
    }


    public function cekURI($request)
    {
        return $request->getRequestUri();
    }


    public function table($request)
    {
        $item = 15;
        $limit = (( (int)$request['paging'] - 1) * $item);

        $data = [
            'paging_item'       =>  $item,
            'paging_limit'      =>  $limit,
            'paging'            =>  $request['paging']
        ];

        return $data;
    }

    public function scroll($request)
    {
        $item = 55;
        $limit = (( (int)$request['paging'] - 1) * $item);

        $data = [
            'paging_item'       =>  $item,
            'paging_limit'      =>  $limit,
            'paging'            =>  $request['paging']
        ];

        // $data = [
        //     'item'      =>  $item
        // ];

        return $data;
    }

    public function uniqnum($first,$second)
    {

        return mt_rand($first, $second);

        // return mt_rand(125, 299);
    }

    // timeago
    public function timeago($ptime)
    {

        $gettime = strtotime($ptime);

        $estimate_time = time() - $gettime;
        if( $estimate_time < 1 )
        {
            return '1d lalu';
        }

        $condition = [ 
            12 * 30 * 24 * 60 * 60  =>  'thn',
            30 * 24 * 60 * 60       =>  'bln',
            24 * 60 * 60            =>  'hari',
            60 * 60                 =>  'j',
            60                      =>  'm',
            1                       =>  'd'
        ];

        foreach( $condition as $secs => $str )
        {
            $d = $estimate_time / $secs;

            $r = round($d);

            if( $d >= 1 )
            {
                    // $r = round( $d );
                // return ' ' . $r . $str;
                
                if( $str == 'm' || $str == 'd')
                {   
                    return $r . $str . ' lalu';
                }
                elseif( $str == 'j' )
                {
                    if( $r < 4 )
                    {
                        return $r . $str . ' lalu';
                    }
                    else
                    {
                        return date('H.i', $gettime);
                    }
                }
                elseif( $str == 'hari' && $r < 7)
                {
                    return $this->namahari($ptime) . ', ' . date('H:i', $gettime);
                    
                }
                else
                {
                    return date('d/m/Y', $gettime);

                }

            }
        }

    } 

    // end timeago

    function randString($length,$val) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ12345689'.$val;
        $my_string = '';
        for ($i = 0; $i < $length; $i++) {
        $pos = mt_rand(0, strlen($chars) -1);
        $my_string .= substr($chars, $pos, 1);
        }
        return $my_string .'==';
    }

    function namahari($date)
    {
        $info=date('w', strtotime($date));

        switch($info){
            case '0': return "Minggu"; break;
            case '1': return "Senin"; break;
            case '2': return "Selasa"; break;
            case '3': return "Rabu"; break;
            case '4': return "Kamis"; break;
            case '5': return "Jumat"; break;
            case '6': return "Sabtu"; break;
        };
    }


    // BULAN ARRAY SIMPLE
    function bulanArr($request)
    {
        $q = (int)$request;
        $q = ($q - 1);
        $bln = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nop','Des'];

        return $bln[$q];
    }


    function bulanFull($request)
    {
        $q = (int)$request;
        $q = ($q - 1);
        $bln = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','Nopember','Desember'];

        return $bln[$q];
    }


    // end nama hari


    public function calcWgSAP($request)
    {
        $weight = $request;
        $bagi = 1000;
        $wg = ($weight / $bagi);
        $up = 0.5;

        // jika berat di bawah bgi maka masukan nilai 1 utuk 1 kilo
        $wg =  $weight < $bagi ? 1 : ($weight / $bagi);

        // cek jika wg mengandung desimal
        $num = is_float($wg) ? 1 : 0;

        if( $num == 1)
        {
            $dec = 0 . '.' . substr($wg, 2);

            if( $dec >= $up )
            {
                $wgt = floor($wg) + 1;
            }
            else
            {
                $wgt = floor($wg);
            }

        }
        else
        {
            $wgt = $wg;
        }

        return $wgt;
    }


    //CHANGE FORMAT DATE YYYY-MM-DD
    public function changeFormatDate($request)
    {
        $date = explode("/", $request);
        return ($date[2] . '-' . $date[1] . '-' . $date[0]);
    }

    //ROLEBACK FORMT DATE DD/MM/YYYY
    public function roleFormatDate($request)
    {
        $date = explode("-", $request);
        return ($date[2] . '/' . $date[1] . '/' . $date[0]);
    }

    //NICK NAME
    public function nickName($request)
    {
        $name = explode(" ", $request);
        return $name[0];
    }

    //create number null 
    public function numberFZero($request)
    {
        $num = $request[0];
        $num++;
        $length = $request[1];

        $sprint = sprintf('%0'.$length.'s',$num);

        return $sprint;
    }
}