<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tabs extends Model {

    protected $table = 'tabs';
    protected $fillable = ['tab_name', 'user_id', 'query', 'webhook',
        'created_at', 'updated_at', 'table_id', 'condition'
    ];

    public static function allTabs() {
        $data = Tabs::pluck('tab_name')->toArray();
        return $data;
    }

    public static function tabsWithWebhookUrls() {
        $data = Tabs::where('webhook', '<>', '')->pluck('tab_name')->toArray();
        return $data;
    }

    # return tab query

    public static function getTabQuery($tab) {
        $data = Tabs::where('tab_name', $tab)->first(['query']);
        return $data->query;
    }

    public static function getTabsWebhook($tab) {
        $data = Tabs::where('tab_name', $tab)->first(['webhook']);
        return $data->webhook;
    }
 
    public static function getTabsByTableId($tableId) {
        $data = \DB::table('tabs')
                ->select('tab_name')
                ->where('table_id', $tableId)
                ->get();
        return $data;
    }
    
    public static function deleteById($id) {
        Tabs::destroy($id);
    }
    
    public static function getTabsListByTableId($tableId) {
        $data = \DB::table('tabs')
                ->select(array('tab_name','table_id','id'))
                ->where('table_id', $tableId)
                ->get();
        return $data;
    }

}