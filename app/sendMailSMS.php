<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\TableStructure;

class sendMailSMS extends Model
{
    protected $table = 'send_mail_details';
    
    public static function insertMailDetials($insertData){

        $response = \DB::table('send_mail_details')
                    ->insert($insertData);
        return $response;
        
    }
    public static function insertMessageDetials($insertData){

        $response = \DB::table('send_sms_details')
                    ->insert($insertData);
        return $response;
        
    }
}
