<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSettingModel extends Model
{
    protected $table ='site_settings';
    
	protected $fillable = [ 'id',
	                        'site_name',
                    		'site_address',
                            'site_contact_number',
                            'meta_desc',
                            'meta_keyword',
                            'site_email_address',
                            'site_logo',
                            'login_site_logo',
                            'fb_url',
                            'twitter_url',
                            'linkdin_url',
                            'youtube_url',
                            'rss_feed_url',
                            'instagram_url',
                            'whatsapp_url',
                            'website_url',
                            'site_status',
                            'commission',
                            'lattitude',
                            'longitude',
                            'mailchimp_api_key',
                            'mailchimp_list_id',
                            'site_short_name',
                            'site_short_description',
                            'representative_commission',
                            'salesmanager_commission' ,
                            'product_max_qty',
                            'tinymce_api_key',
                            'favicon'    
                         ];
}
