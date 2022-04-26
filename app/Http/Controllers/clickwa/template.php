<?php
namespace App\Http\Controllers\clickwa;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\app_whatsapp_templates as tblAppWhatsappTemplates;

class template extends Controller
{
    //
    public function main($request)
    {
        $gettemplate = tblAppWhatsappTemplates::where([
            'type'      =>  $request['type'],
            'subtype'   =>  $request['subtype'],
            'status'    =>  1
        ])->first();

        return $gettemplate;
    }
}