<?php
namespace App\Http\Controllers\account;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\config\index as Config;
use App\users as tblUsers;
use App\reset_passwords as tblResetPasswords;
use App\user_configs as tblUserConfigs;
use App\user_images as tblUserImages;
use App\user_employes as tblUserEmployes;
use App\employe_levels as tblEmployeLevels;
use DB;

class index extends Controller
{
    //
    public function show($request)
    {

        $Config = new Config;
        //
        $account = $request;

        $getconfig = $this->userConfig([
            'id'   =>  $account['id'],
            'level'      =>  $account['level']
        ]);

        $getimage = tblUserImages::where([
            'user_id'       =>  $account['id'],
            'status'        =>  1
        ])->first();

        $data = [
            'id'            =>  $account['id'],
            'name'          =>  $account['name'],
            'level'         =>  $account['level'],
            'sublevel'      =>  $account['sub_level'],
            'username'      =>  $account['username'],
            'email'         =>  $account['email'],
            'gender'        =>  $account['gender'],
            'key'           =>  $account['token'],
            'image'         =>  ($getimage === null ? '/assets/images/none/user.png' : ( env("URL_API") . '/images/users/' . $getimage->token. '.jpg')),
            'config'        =>  $getconfig['config'],
            'company'       =>  $getconfig['company']
            // 'employe'       =>  $this->userEmploye($account['id'])
        ];

        return $data;
    }


    public function profile($request)
    {
        $Config = new Config;
        $account = $request;

        $data = [
            'id'            =>  $account['id'],
            'name'          =>  $account['name'],
            'email'         =>  $account['email'],
            'level'         =>  $account['level'],
            'sublevel'      =>  $account['sub_level'],
            'username'      =>  $account['username'],
            'email'         =>  $account['email'],
            'gender'        =>  $account['gender'],
            'key'           =>  $account['token'],
            // 'image'         =>  $Config->apps()['storage']['URL'] . ($getimage === null ? '/images/none/user.png' : '/images/users/' . $getimage->id. '.jpg')
        ];

        return $data;
    }


    //get page change password cekin token send email
    public function getchangepassword(Request $request)
    {   
        //
        $Config = new Config;
        //
        $key = $request->header('key');
        //
        $ceking = tblResetPasswords::where([
            'token'         =>  trim($request->header('key')),
            'status'        =>  1
        ])->first();
    
        if( $ceking == null)
        {
            return response()->json([
                'message'=>'Key/Token tidak valid atau kadaluwarsa'
            ], 404);
        }
        

        $data = [
            'token'     =>  $key
        ];

        return response()->json([
            'message'=>'',
            'response'  =>  $data
        ],200);
    }
    

    public function userConfig($request)
    {
        //
        $getdata = tblUserConfigs::from('user_configs as uc');
        if($request['level'] == '1')
        {   
            $getdata = $getdata->select(
                'uc.type', 'uc.company_id', 'uc.homepage', 'uc.aside_id',
                'c.id as company_id', 'c.name as company_name'
            )
            ->leftJoin('companies as c', function($join)
            {
                $join->on('c.id', '=', 'uc.company_id');
            });
        }
        if($request['level'] == '2')
        {
            $getdata = $getdata->select(
                'uc.type', 'uc.company_id', 'uc.homepage', 'uc.aside_id',
                'l.id as company_id', 'l.name as company_name'
            )
            ->leftJoin('lembagas as l', function($join)
            {
                $join->on('l.id', '=', 'uc.company_id');
            });
        }
        $getdata = $getdata->where([
            'uc.user_id'       =>  $request['id']
        ])
        ->first();

        //

        $data = [
            'config'        =>  [
                'type'              =>  $getdata->type,
                'aside'             =>  $getdata->aside_id,
                'homepage'          =>  $getdata->homepage
            ],
            'company'       =>  [
                'id'                =>  $getdata->company_id,
                'name'              =>  $getdata->company_name
            ]
        ];

        return $data;
    }


    public function userEmploye($request)
    {

        $getdata = tblUserEmployes::from('user_employes as ue')
        ->select(
            'ue.id','ue.name','ue.nick','ue.gender','ue.birth','ue.level','ue.sublevel','ue.groups',
            'el.name as level_name',
            DB::raw('IFNULL(es.name, "") as sublevel_name'),
            DB::raw('IFNULL(es.alias, "") as sublevel_alias'),
            DB::raw('IFNULL(eg.name, "") as divisi_name'),
        )
        ->leftJoin('employe_levels as el', function($join)
        {
            $join->on('el.id', '=', 'ue.level');
        })
        ->leftJoin('employe_sublevels as es', function($join)
        {
            $join->on('es.id', '=', 'ue.sublevel');
        })
        ->leftJoin('employe_groups as eg', function($join)
        {
            $join->on('eg.id', '=', 'ue.groups');
        })
        ->where([
            'ue.user_id'       =>  $request
        ])
        ->first();

        if( $getdata == null)
        {
            return "";
        }

        $data = [
            'id'        =>  $getdata->id,
            'name'      =>  $getdata->name,
            'nick'      =>  $getdata->nick,
            'gender'    =>  $getdata->gender,
            'birth'     =>  $getdata->birth,
            'level'         =>  [
                'id'        =>  $getdata->level,
                'name'      =>  $getdata->level_name,
                'position'  =>  $getdata->level === 10005 ? 'false' : 'true'
            ],
            'sublevel'      =>  [
                'id'        =>  $getdata->sublevel,
                'name'      =>  $getdata->sublevel_name,
                'alias'     =>  $getdata->sublevel_alias
            ],
            'divisi'        =>  [
                'id'        =>  $getdata->divisi,
                'name'      =>  $getdata->divisi_name
            ]
        ];

        return $data;
    }


    public function viewtype($request)
    {
        $Config = new Config;
        //type
        $type = $request['type'];

        //get data
        $getdata = tblUsers::from('users as u')
        ->select(
            'u.id', 'u.email', 'u.token', 'u.name', 'u.level', 'u.sub_level', 'u.gender', 'u.username',
            DB::raw('IFNULL(ui.token, "") as images')
        )
        ->leftJoin('user_images as ui', function($join)
        {
            $join->on('ui.user_id', '=', 'u.id')
            ->where([
                'ui.status' =>  1
            ]);
        })
        ->where([
            'u.status'            =>  1
        ]);
        if( $type == 'key' )
        {
            $getdata = $getdata->where([
                'u.token'         =>  $request['token']
            ]);
        }
        elseif( $type == 'id')
        {
            $getdata = $getdata->where([
                'u.id'         =>  $request['id']
            ]);
        }
        else
        {
            $getdata = $getdata->where([
                'u.email'         =>  $request['email']
            ]);
        }
        $getdata = $getdata->first();

        $getconfig = $this->userConfig(['user_id'=>$getdata->id]);
        $data = [
            'id'            =>  $getdata->id,
            'name'          =>  $getdata->name,
            'level'         =>  $getdata->level,
            'sublevel'      =>  $getdata->sub_level,
            'username'      =>  $getdata->username,
            'email'         =>  $getdata->email,
            'email'         =>  $getdata->email,
            'gender'        =>  $getdata->gender,
            'key'           =>  $getdata->token,
            'image'         =>  $Config->apps()['storage']['URL'] . ($getdata->images === "" ? '/images/none/user.png' : '/images/users/' . $getdata->images. '.jpg'),
            'config'        =>  $getconfig,
            'employe'       =>  $this->userEmploye($getdata->id)
        ];


        return $data;
    }


    public function viewprofile(Request $request)
    {
        $view = $this->viewtype([
            'type'          =>  'key',
            'token'         =>  $request->header('key')
        ]);
        
        $data = [
            'message'       =>  $view
        ];

        return response()->json($data, 200);
    }

}