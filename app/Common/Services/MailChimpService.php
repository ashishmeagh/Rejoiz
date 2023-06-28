<?php 

namespace App\Common\Services;

use App\Models\SiteSettingModel;

class MailChimpService
{
	  private $obj_mailchimp;
    private $list_id;
    private $api_key;

    public function __construct()
    {  
        $site_settings = SiteSettingModel::where('id','1')->first();

        /* Fallback Credentials */
        $this->api_key = "a43f7f7d8bf6c81432fbebf42766c94c-us20";   
        $this->list_id = "481855f4d2";

        /* SiteSettings Mailchimp Credentials */
        if($site_settings)
        {
            $this->api_key = $site_settings->mailchimp_api_key;   
            $this->list_id = $site_settings->mailchimp_list_id;
        }
    }

    public function subscribe($email)
    {
    	$api_key = $this->api_key;
      $listing_id = $this->list_id;


      
      $email_address = $email;//It should be dynamic
      $status = "subscribed";


      $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => "https://us20.api.mailchimp.com/3.0/lists/".$listing_id."/members",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "POST",
      CURLOPT_POSTFIELDS => "{\n\t\"email_address\":\"".$email_address."\",\t\n\t\"status\":\"".$status."\"\n}\n",
      CURLOPT_HTTPHEADER => array(
        "Authorization: apikey ".$api_key."-us20",
      ),
    ));

    $result = curl_exec($curl);
    curl_close($curl);

    $data = json_decode($result);
    if (isset($data->error))
    {
        return false;
    } 
    else 
    {
        return true;
    }
  }  
}