<?php
namespace App\Http\Controllers\send\mail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
use App\auto_senders as tblAutoSenders;

class index extends Controller
{
    //
    public function main($request)
    {
        $user = $request['user'];
        $temp = $request['temp'];
        $sender = $request['sender'];


        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = $sender['sender_host'];
        $mail->Port =  $sender['sender_port'];
        $mail->Username = $sender['sender_user'];
        $mail->Password = $sender['sender_password'];

        //form
        $mail->setFrom($sender['sender_email'], $sender['sender_label']);        
        $mail->Subject = $temp['subject'];
        $mail->MsgHTML($temp['content']);
        $mail->addAddress($user['email'], $user['name']); 
        $mail->send();

        
        if($mail )
        {
            $data = [
                'message'       =>  ''
            ];  
            
        }
        else
        {
            $data = [
                'message'       =>  "Mailer Error: " . $mail->ErrorInfo
            ];
        }

        return $data;
    }

    function send(Request $request)
    {
        $cekautosenders = tblAutoSenders::from('auto_senders as ae')
        ->select(
            'ae.id','ae.type','ae.sub_type', 'ae.info', 'ae.template', 'ae.template_body',
            'es.host as sender_host', 'es.tls as sender_tls', 'es.port as sender_port', 'es.email as sender_email', 'es.user as sender_user', 'es.password as sender_password', 'es.label as sender_label'
        )
        ->leftJoin('email_senders as es', function($join)
        {
            $join->on('es.id', '=', 'ae.sender_id')
            ->where('es.status',1);
        })
        ->where([
            'ae.token'              =>  trim($request->token),
            'ae.sender_type'       =>  1,
            'ae.sender_email'      =>  0,
            'ae.status'            =>  1
        ])->first();


        if( $cekautosenders == null)
        {
            $data = [
                "message"       =>  "Data sender email tidak ditemukan"
            ];

            return response()->json($data, 404);
        }

        //info users
        $user = [
            'email'         =>  json_decode($cekautosenders->info, true)['user']['email'],
            'name'          =>  json_decode($cekautosenders->info, true)['user']['name']
        ];
        

        $sender = [
            'sender_host'       =>  $cekautosenders->sender_host,
            'sender_port'       =>  $cekautosenders->sender_port,
            'sender_tls'        =>  $cekautosenders->sender_tls,
            'sender_email'      =>  $cekautosenders->sender_email,
            'sender_user'       =>  $cekautosenders->sender_user,
            'sender_password'   =>  $cekautosenders->sender_password,
            'sender_label'      =>  $cekautosenders->sender_label . ' | ' .json_decode($cekautosenders->info, true)['apps']['name']
        ];


        $data = [
            'user'          =>  $user,
            'temp'          =>  [
                'subject'       =>  json_decode($cekautosenders->template, true)['subject'],
                'content'       =>  $cekautosenders->template_body
            ],
            'sender'        =>  $sender
        ];

        //send in main
        $sendmail = $this->main( $data );


        if( $sendmail['message'] == '')
        {

            $status_email = [
                'status'        =>  'Terkirim',
                'date'          =>  date('Y-m-d H:i:s', time())
            ];

            $autosender = tblAutoSenders::where([
                'id'            =>  $cekautosenders->id,
                'sender_email'  =>  0,
            ])
            ->update([
                'sender_email'      =>  1,
                'status_email'     =>  $status_email
            ]);
        }

        return $sendmail;

    }
}