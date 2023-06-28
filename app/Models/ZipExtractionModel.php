<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ZipExtractionModel extends Model
{
    protected $table = 'zip_extraction';

    protected $fillable = [
    						'user_id',
    						'zip_name',
    					   	'zip_status',
    					   	'zip_extraction_log',
                 'created_at',
                 'delete_date',
                 'is_deleted',
                 'updated_at'
						  ];
                          
                      
}
