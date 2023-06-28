<?php

namespace App\Common\Services;

use \Sentinel;

class CommonAccountService 
{
	function update_password($current_password='',$new_password=''){

		$return_result = false;
		$user = Sentinel::check();

        $credentials = [];
        $credentials['password'] = $current_password;

        if(Sentinel::validateCredentials($user,$credentials)) 
        { 
            $new_credentials = [];
            $new_credentials['password'] = $new_password;

            if(Sentinel::update($user,$new_credentials))
            {
            	$return_result = 'SUCCESS';
            }
            else
            {
            	$return_result = 'ERROR';
            }
        } 
        else
        {
        	$return_result = 'INCCORECT_OLD_PASS';
        }

        return $return_result; 
	}
}