<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotesConversationModel extends Model
{
    protected $table = 'quotes_conversations';

    protected $fillable = [ 'quote_id',
    						'sender_id',
    						'receiver_id',
    						'message',
    						'attachment',
    						'is_viewed',
                            'is_admin_viewed',
                            'is_maker_viewed',
                            'is_retailer_viewed',
                            'is_customer_viewed'
						  ];

	public function sender_details()
    {
      return $this->belongsTo('App\Models\UserModel','sender_id','id');
    }

    public function receiver_details()
    {
      return $this->belongsTo('App\Models\UserModel','receiver_id','id');
    }
}
