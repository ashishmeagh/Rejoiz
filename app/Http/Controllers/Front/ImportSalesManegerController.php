<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UserModel;
use App\Models\CountryModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\SalesManagerModel;
use App\Models\RepAreaModel;

use Sentinel;
use DB;

class ImportSalesManegerController extends Controller
{

		public function __construct(UserModel  $UserModel,
									RoleModel  $RoleModel,
									RoleUsersModel $RoleUsersModel,
									SalesManagerModel $SalesManagerModel,
	                                CountryModel  $CountryModel,
	                                RepAreaModel $RepAreaModel)
		{
			$this->UserModel         = $UserModel;
			$this->BaseModel         = $UserModel;
			$this->SalesManagerModel = $SalesManagerModel;
	        $this->CountryModel      = $CountryModel;
			$this->RoleModel         = $RoleModel;
			$this->RoleUsersModel    = $RoleUsersModel;
			$this->RepAreaModel      = $RepAreaModel;
			$this->role              = 'sales manager';
		}


      public function index()
      {

      	 $filename   = storage_path('Mid-Atlantic-Lifestyle-Sales - Sheet1.csv'); 
    	 /*$filename   = storage_path('Mid-Atlantic-Trend-Sales - Sheet1.csv'); 
    	 $filename   = storage_path('Northeast-Sales - Sheet1.csv'); 
    	 $filename   = storage_path('Southeast-Gift-Sales - Sheet1.csv'); 
    	 $filename   = storage_path('Southeast-Glam-Sales - Sheet1.csv'); 
    	 $filename   = storage_path('SouthEast-home-sales - Sheet1.csv'); 
    	 $filename   = storage_path('West-Division-Sales - Sheet1.csv'); 
    	 */

	 
      	if (!file_exists($filename) || !is_readable($filename))
      	{ 	
          return false;
      	}

	    $delimiter = ",";
	    $header    = null;
	    $data      = array();
	    if (($handle = fopen($filename, 'r')) !== false)
	    {
	        while (($row = fgetcsv($handle, 1000, $delimiter)) !== false)
	        {
	           if (!$header)
	            {
	               $header = $row;
	            }
	            elseif(count($header) !=count($row))
	            {
	            	continue;
	            }
	            else
	            {
	              $data[] = array_combine($header, $row);
	            }
	        }
	        fclose($handle);
	    }

	    $arr_input_data   = $data;
	    $records_inserted = 0;

	    
/*	    $arr_input_data =  [
			[
				'first_name' => 'monika',
				'last_name'  => 'Desoza',
				'email'      => 'monika.rwaltzsoftware@gmail.com',
				'contact_no' => '9089567890',
				'password'   => 'EfJVRSv7k8GuyUws',
				'post_code'  => '32201',
				'country_id' => '2',
				'area'       => ['1','19','27']
			],
			[
				'first_name' => 'sonali',
				'last_name'  => 'k',
				'email'      => 'sonali.rwaltzsoftware@gmail.com',
				'contact_no' => '9056789034',
				'password'   => '2jwvC2ENjjaLyFQh',
				'post_code'  => '65789',
				'country_id' => '2',
				'area'       => ['4','25','6']
			],
			[
				'first_name' => 'hardik',
				'last_name'  => 'Franklin',
				'email'      => 'hardik.rwaltzsoftware@gmail.com',
				'contact_no' => '9870456789',
				'password'   => '2jwvC2ENjjaLyFQh',
				'post_code'  => '3456789',
				'country_id' => '2',
				'area'       => ['1','4','19']
			],
		];*/

     try
     {

     	DB::beginTransaction();

      if(isset($arr_input_data) && count($arr_input_data)>0)
      {
      	 $inser_salesmanager = ""; 
      	 $not_inserted = [];

      	 foreach ($arr_input_data as $input)
      	 {
      	 	$input['email_address'] = trim($input['email_address']);
	            	
        	if($input['email_address'] == "")
        	{  
        		array_push($not_inserted,$input);
        		continue;
        	}

            $is_duplicate = $this->UserModel->where('email',$input['email_address'])->count();
            
            if($is_duplicate==0)
            {	

		             $user = Sentinel::registerAndActivate([
		                                                  'email'    => $input['email_address'],
		                                                  'password' => $input['Password']
		                                                ]);

				     if($user)
				     {
					        $role = Sentinel::findRoleBySlug('sales_manager');
									    $role->users()->attach($user);


						  $user->first_name    = isset($input['first_name'])?$input['first_name']:'';
				          $user->last_name     = isset($input['last_name'])?$input['last_name']:'';
				          $user->email         = isset($input['email_address'])?$input['email_address']:'';
				          $user->contact_no    = isset($input['mobile_number'])?$input['mobile_number']:'';
				          $user->post_code     = isset($input['zip_code'])?$input['zip_code']:'';
				          $user->country_id    = isset($input['country'])?$input['country']:'';
				          $user->status        = '1';
				          $user->is_approved   = '1';


				          $is_saved = $user->save();

                          $area_id              = "";
				        
				          $user->save();
     
						  $area_arr['user_id']  = $user->id;
				          $area_arr['area_id']  = 20;

				          $inser_salesmanager   = $this->SalesManagerModel->create($area_arr);
			      	 }


			    $records_inserted++;
  	 
	        }	 

/*	        else 
            {
            	 $user_id  = $area_id  = "";
            	 if(isset($input['email_address']) && $input['email_address']!="")
            	 {
            	 	$user_details      = $this->UserModel->where('email',$input['email_address'])->first()->toArray();
            	 	if(isset($user_details) && count($user_details)>0)
            	 	{	
            	 	 $user_id           = $user_details['id'];
            	    }
            	 }  
            	 
				 $area_arr['user_id']  = $user_id;
		         $area_arr['area_id']  = 20;
		         $inser_salesmanager   = $this->SalesManagerModel->create($area_arr);
		         dd($inser_salesmanager);
            }*/

      	}
      	$records_inserted++;

      	 if($inser_salesmanager)
      	 {
      	

      	 // if($inser_salesmanager)
      	 // {
      	 	$response['status']      = 'success';
	        $response['description'] = 'SalesManager has been inserted, Records Detected ('.sizeof($arr_input_data).'), Records Inserted ('.$records_inserted.')';
	            $response['not_inserted'] = $not_inserted;
	            return response()->json($response);
      	 }
      	 else
      	 {  
      	    $response['status']      = 'error';
      	 	$response['description'] = 'Error occurred while importing data.';
      	 } 
      	 // }
      	 // else
      	 // {  
      	 //    $response['status']      = 'error';
      	 // 	$response['description'] = 'Problem occured while importing data!';
      	 // } 

      	 
      	 DB::commit();
      }
	    else
	    {  
	      	$response['status']      = 'error';
	      	$response['description'] = 'Please provide data to import.';
	    }
    }
    catch(Exception $e)
	{
	        DB::rollback();
	        $response['status']      = 'error';
      	 	$response['description'] = 'Error occurred while importing data.';
	}

	return $response;

   }
}
