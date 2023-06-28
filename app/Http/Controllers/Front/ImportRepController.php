<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\RepresentativeModel;
use App\Models\RepAreaModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;

use DB;
use Validator;
use Sentinel;
use Flash;
use DataTable;


class ImportRepController extends Controller
{
    
	public function __construct( UserModel $UserModel,
								 RepresentativeModel $RepresentativeModel,
								 RepAreaModel $RepAreaModel,
								 RoleModel $RoleModel,
								 RoleUsersModel $RoleUsersModel


                                )
    {
     	$this->UserModel  			= $UserModel;
        $this->RepresentativeModel  = $RepresentativeModel;
        $this->RepAreaModel  		= $RepAreaModel;
        $this->RoleModel  			= $RoleModel;
        $this->UserModel  			= $UserModel;
        $this->RoleUsersModel  		= $RoleUsersModel;
	}


    public function import_data()
    {

    	 // $filename   = storage_path('app/Mid-Atlantic-Lifestyle-Rep - Sheet1.csv'); 
    	// $filename   = storage_path('app/Mid-Atlantic-Trend-Rep - Sheet1.csv'); 
    	 // $filename   = storage_path('app/Mink-Rep - Sheet1.csv'); 
    	 // $filename   = storage_path('app/Northeast - Sheet1.csv'); 
    	 // $filename   = storage_path('app/Southeast-Gift-Rep - Sheet1.csv'); 
    	 // $filename   = storage_path('app/Southeast-Glam-Rep - Sheet1.csv'); 
    	 // $filename   = storage_path('app/Southeast-Home-Rep - Sheet1.csv'); 
    	 $filename   = storage_path('app/West - Sheet1.csv'); 
    	 

      	 if (!file_exists($filename) || !is_readable($filename)){

        	return false;
      	 }

	    $delimiter = ",";
	    $header   = null;
	    $data     = array();
	    if (($handle = fopen($filename, 'r')) !== false)
	    {
	        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
	        {
	            if (!$header)
	                $header = $row;
	            else
	                
	            if (count($header) != count($row)) {
				  continue;
				}
				$data[] = array_combine($header, $row);
	        }
	        fclose($handle);
	    }
	    
	    $arr_input_data =  $data;
	    // dd($arr_input_data);
		try
		{
			if(isset($arr_input_data) && count($arr_input_data)>0)
			{   
				$user_data = $representative_data = $not_inserted_data = [];
				DB::beginTransaction();
				$count = 0;
				$inserted_record = 0;
				foreach ($arr_input_data as $key => $rep_data) 
				{
                   	$rep_data['email_address'] = trim($rep_data['email_address']);

	            	if($rep_data['email_address'] == "" || $rep_data['email_address'] == 'email_address')
	            	{
	            		array_push($not_inserted_data,$rep_data);
	            		continue;
	            	}

					if(isset($rep_data['email_address'])){

						$count = $this->UserModel->where('email',$rep_data['email_address'])
											 ->count();
					}
					
					if($count >= 1)
					{
						array_push($not_inserted_data,$rep_data);
					}

					if($count == 0)
					{
						$sales_manager_id = 0;
						$existingRepsId = $this->UserModel->where('email',$rep_data['sales_manager'])
											 ->first();

						if($existingRepsId){

							$sales_manager_id = $existingRepsId['id'];
						}

						$user_data['first_name'] = isset($rep_data['first_name'])?$rep_data['first_name']:'';
						$user_data['last_name']  = isset($rep_data['last_name'])?$rep_data['last_name']:'';
						$user_data['email']      = isset($rep_data['email_address'])?$rep_data['email_address']:'';
						$user_data['country_id']  = isset($rep_data['country'])?$rep_data['country']:'';

						$user_data['password']      = isset($rep_data['password'])?$rep_data['password']:'';
						$user_data['post_code'] = isset($rep_data['zip_code'])?$rep_data['zip_code']:'';
						$user_data['tax_id'] = isset($rep_data['tax_id'])?$rep_data['tax_id']:'';

						$user_data['contact_no'] = isset($rep_data['mobile_number'])?$rep_data['mobile_number']:'';

					    $representative_data['category_id'] = $user_data['category_id'] = isset($rep_data['category_id'])?json_encode($rep_data['category_id']):'';
					    
					    $representative_data['area_id'] = isset($rep_data['area'])?$rep_data['area']:0;

					    $representative_data['sales_manager_id'] = isset($sales_manager_id)?$sales_manager_id:0;


						$user = Sentinel::registerAndActivate([
	                	'email' => $user_data['email'] ,
	                	'password' => $user_data['password']
	            		]);


	            		if($user)
	            		{
	                		$role = Sentinel::findRoleBySlug('representative');
							$role->users()->attach($user);
	            		

	            			$user->first_name    = $user_data['first_name'];
	        				$user->last_name     = $user_data['last_name'];
		        			$user->email         = $user_data['email'];
		        			$user->contact_no    = $user_data['contact_no'];
		        			$user->country_code  = $user_data['country_id'];
		        			$user->tax_id        = $user_data['tax_id'];
		        			$user->country_id    = $user_data['country_id'];
		        			$user->post_code     = $user_data['post_code'];
		       				$user->commission    = '';
		        			$user->status        = '1';
		        			$user->is_approved   = '1';

		        			$user->save();

	        				$category = '';

	        				if(isset($representative_data['category_id']) && !empty($representative_data['category_id']))
	        				{
	            				$category_id = $representative_data['category_id'];
	            				$category = json_encode($category_id);
	            			}

	        				$representative                   =  RepresentativeModel::firstOrNew(['user_id' => $user->id]);
	        
	        				$representative->sales_manager_id = $representative_data['sales_manager_id'];
	        			
	        				$representative->area_id          = isset($representative_data['area_id'])?$representative_data['area_id']:0;
	        				$representative->category_id      = $category;
							$representative->save();
						}
				   }
				   $inserted_record++;
			}
		  		DB::commit();
          		$response['status']      = 'success';
          		$response['not_inserted_data']      = $not_inserted_data;
          		$response['inserted_records']      = 'total records are: ('.sizeof($arr_input_data).")  Inserted  records: (".$inserted_record.").";
          	}
		}
		catch(Exception $e)
      	{
	        DB::rollback();
	        $response['status']            = 'failure';
	        $response['not_inserted_data'] = $not_inserted_data;
	        $response['description']       = $e->getMessage();	
      	}
      	return response()->json($response); 
	} 
}
