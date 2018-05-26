<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CronTab extends Model
{
    protected $table = 'cron_tab';
    protected $fillable = ['table_id', 'tab_id', 'type', 'from_email', 'from_name', 'subject', 'message', 'route', 'tab_column_type', 'created_at', 'updated_at'];
}
