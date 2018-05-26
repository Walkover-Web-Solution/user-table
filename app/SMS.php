<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App;
use GuzzleHttp;
use Illuminate\Database\Eloquent\Model;

/**
 * Description of SMS
 *
 * @author sapnabhayal
 */
class SMS extends Model {
    public static function sendSMS($formData, $data, $tableId, $sms_api_key = false, $tabId) {
        if (empty($sms_api_key))
            return false;

        $senderId = $formData['sender'];
        $route = $formData['route'];
        $mobile_column = $formData['mobile_columnn'];
        $message = $formData['message'];
        $testSmsno = isset($formData['testsmsno']) && !empty($formData['testsmsno']) ? $formData['testsmsno'] : false;
        $sendAuto = isset($formData['send_auto']) && $formData['send_auto'] == 'yes' ? true : false;
        if ($sendAuto) {
            $matchThese = array('table_id' => $tableId, 'type' => 1, 'subject' => $senderId, 'message' => $message, 'tab_column_type' => $mobile_column, 'route' => $route);
            CronTab::updateOrCreate($matchThese, ['tab_id' => $tabId]);
        }
        preg_match_all("~\##(.*?)\##~", $message, $replaceKey);
        
        $chunks = array_chunk($data, 500);
        foreach ($chunks as $data) {
            $insertParamArr = array();
            $smsArrayJSON = array();
            $findArr = array();
            foreach ($data as $key => $value) {
                if (!isset($value[$mobile_column])) {
                    continue;
                }
                if (!empty($value)) {
                    $valArr = array();
                    foreach ($replaceKey[1] as $index => $strName) {
                        if (isset($value[$strName])) {
                            $valArr[$index] = $value[$strName];
                            $findArr[$index] = "##$strName##";
                        }
                    }
                    $actualMsg = str_replace($findArr, $valArr, $message);
                    $insertParamArr[] = array('senderId' => $senderId, 'message' => $actualMsg, 'number' => $value[$mobile_column], 'authkey' => $sms_api_key, 'route' => $route, 'status' => 1, 'tableId' => $tableId, 'tab_id' => $tabId);
                    $smsArrayJSON[] = array("message" => $actualMsg, "to" => !empty($testSmsno) ? array($testSmsno) : array($value[$mobile_column]));
                }
                if (!empty($testSmsno))
                    break;
            }
            if (empty($testSmsno))
                $response = \App\sendMailSMS::insertMessageDetials($insertParamArr);
            else
                $response = true;

            $smscontent = json_encode(array("sender" => $senderId, "route" => $route, "country" => 91, "sms" => $smsArrayJSON));
            $response = SMS::postsms($smscontent, $sms_api_key);
        }

        if ($response) {
            return response(json_encode(array('status' => 'success', 'message' => 'SMS Sent')), 200)->header('Content-Type', 'application/json');
        } else {
            return response(json_encode(array('status' => 'error', 'message' => 'Error in sending, Please contact to support')), 200)->header('Content-Type', 'application/json');
        }
    }

    public static function postsms($sms, $smsApiKey) {
        try {
            $client = new GuzzleHttp\Client();
            $url = "http://api.msg91.com/api/v2/sendsms";
            $response = $client->post($url, ['body' => $sms, 'headers' => ['authkey' => $smsApiKey, 'Content-type' => 'application/json']]);
            return $response;
        } catch (\Guzzle\Http\Exception\ConnectException $e) {
            $response = json_encode((string) $e->getResponse()->getBody());
            return $response;
        }
    }
    
    public static function sendMail($formData, $data, $tableId, $email_api_key = false, $tabId) {
        if (empty($email_api_key))
            return false;

        $from_email = $formData['from_email'];
        $from_name = $formData['from_name'];
        $email_column = $formData['email_column'];
        $subject = $formData['subject'];
        $mailContent = $formData['mailContent'];
        $testEmailid = isset($formData['testemailid']) && !empty($formData['testemailid']) ? $formData['testemailid'] : false;
        $sendAuto = isset($formData['send_auto']) && $formData['send_auto'] == 'yes' ? true : false;
        if($sendAuto)
        {
            $matchThese = array('table_id' => $tableId, 'type' => 0, 'from_email' => $from_email, 'from_name' => $from_name, 'subject' => $subject, 'message' => $mailContent, 'tab_column_type' => $email_column);
            CronTab::updateOrCreate($matchThese,['tab_id' => $tabId]);
        }
        
        preg_match_all("~\##(.*?)\##~", $mailContent, $replaceKey);
        $chunks = array_chunk($data, 500);
        foreach ($chunks as $data) {
            $insertParamArr = array();
            $findArr = array();
            foreach ($data as $key => $value) {
                if (!isset($value[$email_column])) {
                    continue;
                }
                if (!empty($value)) {
                    $name = $value['name'];
                    $valArr = array();
                    foreach ($replaceKey[1] as $index => $strName) {
                        if (isset($value[$strName])) {
                            $valArr[$index] = $value[$strName];
                            $findArr[$index] = "##$strName##";
                        }
                    }
                    $actualMailContent = str_replace($findArr, $valArr, $mailContent);
                    $insertParamArr[] = array('to_email' => $value[$email_column], 'from_email' => $from_email, 'from_name' => $from_name, 'subject' => $subject, 'content' => $actualMailContent, 'status' => 1, 'mailKey' => $email_api_key, 'tableId' => $tableId, 'tab_id' => $tabId);
                    if(!empty($testEmailid))
                        break;
                }
            }
            $emailresponse = SMS::postemail(!empty($testEmailid) ? $testEmailid : $value[$email_column], $from_email, $subject, $actualMailContent, $email_api_key);
            if(empty($testEmailid))
                $response = \App\sendMailSMS::insertMailDetials($insertParamArr);
        }
        if ($emailresponse) {
            return response(json_encode(array('status' => 'success', 'message' => 'Email Sent')), 200)->header('Content-Type', 'application/json');
        } else {
            return response(json_encode(array('status' => 'error', 'message' => 'Error in sending, Please contact to support')), 200)->header('Content-Type', 'application/json');
        }
    }

    public static function postemail($to, $from, $subject, $email, $emailApiKey) {
        $url = 'http://control.msg91.com/api/sendmail.php?to=' . $to . '&from=' . $from . '&subject=' . $subject . '&body=' . $email . '&authkey=' . $emailApiKey;
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return false;
        } else {
            $response = json_decode($response);
            if ($response->type == 'error')
                return false;
            else
                return true;
        }
    }
}
