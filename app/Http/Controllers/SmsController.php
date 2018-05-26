<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Http\Controllers;

use App\CronTab;
use App\Tabs;
use App\team_table_mapping;
use App\TableStructure;
use App\Tables;
use App\SMS;

/**
 * Description of SmsController
 *
 * @author sapnabhayal
 */
class SmsController extends Controller {

    public function SendSMSAuto($type = 'email') {
        if ($type === false)
            return false;
        $cron_status = ($type == 'sms') ? 1 : 0;
        $cron_tab = CronTab::where('type', $cron_status)->get();
        foreach ($cron_tab as $cron) {
            $tab = Tabs::where('id', $cron->tab_id)->first();
            if (empty($tab->query))
                continue;

            $tableNames = team_table_mapping::getUserTablesNameById($cron->table_id);
            if (empty($tableNames['table_id'])) {
                return array();
            }

            $tableId = $cron->table_id;
            $tabId = $cron->tab_id;
            $coltypes = TableStructure::getTableColumnTypesArray($tableNames['table_id']);
            $TabCount = Tables::getCountOfTabsData($tableNames['table_id'], $tab->tab_name, $coltypes);
            $tabDataJson = Tables::TabDataBySavedFilter($tableNames['table_id'], $tab->tab_name, $TabCount, $tab->condition);
            $tabPaginateData = json_decode(json_encode($tabDataJson), true);
            $results = $tabPaginateData['data'];
            
            if ($type == 'email') {
                $formData = array('from_email' => $cron->from_email, 'from_name' => $cron->from_name, 'subject' => $cron->subject, 'mailContent' => $cron->message, 'email_column' => $cron->tab_column_type, 'testsmsno' => false, 'send_auto' => false);
                $newresult = array();
                foreach ($results as $data) {
                    $sms = \DB::table('send_mail_details')->where('to_email', $data[$cron->tab_column_type])
                                    ->where('tableId', $cron->table_id)->where('tab_id', $cron->tab_id)
                                    ->where('status', 1)->first();
                    if (!empty($sms))
                        continue;

                    $newresult[] = $data;
                }
                if(count($newresult)>0)
                    $response = SMS::sendMail($formData, $newresult, $tableId, $tableNames['email_api_key'], $tabId);
                else
                    $response = response(json_encode(array('status' => 'error', 'message' => 'No new email to send.')), 200)->header('Content-Type', 'application/json');
            }
            if ($type == 'sms') {
                $formData = array('sender' => $cron->subject, 'route' => $cron->route, 'message' => $cron->message, 'mobile_columnn' => $cron->tab_column_type, 'testsmsno' => false, 'send_auto' => false);
                $newresult = array();
                foreach ($results as $data) {
                    $sms = \DB::table('send_sms_details')->where('number', $data[$cron->tab_column_type])
                                    ->where('tableId', $cron->table_id)->where('tab_id', $cron->tab_id)
                                    ->where('status', 1)->first();
                    if (!empty($sms))
                        continue;

                    $newresult[] = $data;
                }
                if(count($newresult)>0)
                    $response = SMS::sendSMS($formData, $newresult, $tableId, $tableNames['sms_api_key'], $tabId);
                else
                    $response = response(json_encode(array('status' => 'error', 'message' => 'No new number to send Sms.')), 200)->header('Content-Type', 'application/json');
            }
            echo $response;
        }
    }

}
