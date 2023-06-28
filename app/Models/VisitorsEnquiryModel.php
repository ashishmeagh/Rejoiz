<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorsEnquiryModel extends Model
{
    protected $table    = 'visitors_enquiry';
		const UPDATED_AT = null;
    protected $fillable = ['name',
    					   					 'mobile_no',
    					             'type'
								 ];
								 
}
