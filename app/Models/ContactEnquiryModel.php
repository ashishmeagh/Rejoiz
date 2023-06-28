<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactEnquiryModel extends Model
{
    protected $table    = 'contact_us_enquiry';

    protected $fillable = ['name',
    					   'email',
    					   'subject',
    					   'message'
    					   ];
}
