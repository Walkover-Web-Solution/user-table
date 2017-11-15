<?php

namespace App;

use App\Classes\Utility;
use Illuminate\Database\Eloquent\Model;

class Tabs extends Model
{
    protected $table = 'tabs';
    protected $fillable = [ 'tab_name', 'user_id', 'query','webhook',
                            'created_at','updated_at','table_id'
                           ];


     public static function allTabs(){
         $data=Tabs::pluck('tab_name')->toArray();
        return $data;
     }

    public static function tabsWithWebhookUrls(){
        $data = Tabs::where('webhook','<>','')->pluck('tab_name')->toArray();
        return $data;
    }

     public static function tabsData($tab){
        $assignedTo = Utility::getAssignedTo();
         if($tab == "All")
         {
             $data =  Users::selectRaw(Utility::$records)
                 ->orderBy('created_at', 'desc')->get();
         }
         else{
             $tabSql = Tabs::where('tab_name', $tab)->first(['query']);
             $req  =  (array)json_decode($tabSql->query);
             $data =  Users::getFilteredData($req);
         }
        return $data;
     }

     # return tab query
    public static function getTabQuery($tab){
        $data = Tabs::where('tab_name', $tab)->first(['query']);
        return $data->query;
    }

    # return all the users in the tab
    public static function getListOfUsers($tab){
       $q = "SELECT group_concat(id) as users_list FROM user_data";

       if( $tab != "All") {
           $req =  Tabs::getTabQuery($tab);
           $req = (array)json_decode($req);
           $q  = Users::getFilteredUsersDetails($q,$req);
       }

       $data = \DB::select($q);
      return $data[0];
    }

    public static function getTabsWebhook($tab){
        $data = Tabs::where('tab_name', $tab)->first(['webhook']);
        return $data->webhook;
    }

    # to delete tab
    public static function deleteTab($tabName){
        # delete tab from tabs table
        $data=Tabs::where(['tab_name' => $tabName])
            ->delete();

        # delete tab from tabs_users table (used by crone job)
        $d=TabUsers::where(['tab_name' => $tabName])
            ->delete();

        return $data;
    }
    
//    public static function getTabsByTableId($tableId){
//        $data = Tabs::where('table_id', $tableId)->get();
//        return $data;
//    }
    
    public static function getTabsByTableId($tableId){
        $data =  \DB::table('tabs')
                  ->select('tab_name')
                  ->where('table_id',$tableId)
                   ->get();
        return $data;
    }
    
    

}
