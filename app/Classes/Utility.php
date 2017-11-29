<?php
/**
 * Created by PhpStorm.
 * User: walkover
 * Date: 27/9/17
 * Time: 12:22 PM
 */

namespace App\Classes;


use App\Tabs;
use GuzzleHttp\Psr7\Request;
use Webpatser\Uuid\Uuid;

class Utility
{

  public static $records = "id,created_at,username, CONCAT(firstname, ' ', lastname) AS name, email,contact,purpose,industry,comment,status,follow_up_date,
                              true_client,assign_to,won_or_lost,source,city,country,utm_source,utm_campaign,reference,updated_at";

  public static function postToWebhook($data){
      $client = new \GuzzleHttp\Client();
      $flowId =  env('FLOW_ID');
      $authkey =  env('SOCKT_AUTH_KEY');
      $url = 'https://sokt.io/'.$flowId.'/contacts-test?authkey='.$authkey;
    
      $response =  $client->post(
          $url,
          array(
              'form_params' => $data
          )
      );

      $status = $response->getStatusCode();

      if($status == 200)
          return true;
      else
          return false;
  }

  # send data to webhook urls of corresponding tab
  public static function sendToWebhook($tab,$data){



      $tabUrl  =  Tabs::getTabsWebhook($tab);
      if(isset($tabUrl)){

      $client = new \GuzzleHttp\Client();
      $response =  $client->post(
          $tabUrl,
          array(
              'form_params' => $data
          )
      );

      $status = $response->getStatusCode();

      if($status == 200)
          return true;
      else
          return false;
      }
  }

    public static function getOptions($tab){

        $data = [];

        $status = ['Targeted','Final_Stage','Untargeted','Reseller','Busy','NotRes','DevAPI','Farzi','Startup','International'];

        $purpose  = ['Mix','Transactional','Promotional','SendOTP'];

        $industry = ['Agriculture','Automobiles & Transport','B2B','E-Commerce','Education','Freelancer','IT Company','Stock & Commodity','RealEstate',
            'Travel','Sports','Gym','HealthCare','Hospitality','Retail & FMCG','Wholesale Distributor','Website Developer & Hosting','Media & Ads',
            'Personal Use','Religious','ERP/CRM','Political','Job Portal','Logistics','Event Management','Others'];

        $trueClient = ['True', 'Not_True'];

        $assignTo =  Utility::getAssignedTo();

        $wonOrLost = ['Won', 'Lost_Pricing','Lost_Service','Lost_Feature','Lost_Not_Required','Lost_Others'];

        $colOptions = ['status'=>$status,'purpose'=>$purpose,'industry'=>$industry,
            'true_client'=>$trueClient,'assign_to'=>$assignTo,'won_or_lost'=>$wonOrLost];


        $possibleDropDownArr= ['status','purpose','industry','true_client','assign_to','won_or_lost'];

        if (in_array($tab, $possibleDropDownArr)) {
            return $colOptions[$tab];
        }
        return $data;
    }

    public static function getAssignedTo()
    {  
        return  ['Mona','Rahul_Verma','Jerry','Ravi','Chinmay','Umang','Ayushi','Damini'];
    }

    public static function getToken()
    {
        $id = Uuid::generate() . "";
        return $id;
    }


}