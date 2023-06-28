<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSubscriptiponModel extends Model
{
    protected $table    = 'email_subscriptipon';

    protected $fillable = ['email'];
}
