<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class NotificationsModel extends Model
{
    protected $table    = 'notifications';

    protected $fillable = ['type','title','from_user_id','to_user_id','is_read','status','description','notification_url'];
}
