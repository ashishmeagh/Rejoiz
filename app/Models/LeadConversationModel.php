<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadConversationModel extends Model
{
    protected $table = 'leads_conversations';

    protected $fillable = ['lead_id','sender_id','receiver_id','message','attachment','is_viewed','is_admin_viewed','is_maker_viewed','is_representative_viewed'];

    public function sender_details()
    {
      return $this->belongsTo('App\Models\UserModel','sender_id','id');
    }

    public function receiver_details()
    {
      return $this->belongsTo('App\Models\UserModel','receiver_id','id');
    }
}
