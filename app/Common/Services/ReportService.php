<?php

namespace App\Common\Services;

use DB;
use Excel;
use Illuminate\Http\Request;
use App\Models\UserModel;
use App\Models\MakerModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\CategoryModel;
use App\Models\CategoryTranslationModel;
use App\Models\RetailerModel;
use App\Models\ShopSettings;


 
class ReportService 
{
 
   
 public function __construct(
                                RoleModel $RoleModel,
                                RoleUsersModel $RoleUsersModel,
                                MakerModel $MakerModel,
                                RoleUsersModel $RoleUserModel, 
                                UserModel $UserModel,
                                CategoryModel $CategoryModel,
                                CategoryTranslationModel $CategoryTranslationModel,
                                RetailerModel $RetailerModel,
                                ShopSettings $ShopSettings
                            )
    {
        $this->RoleUserModel      = $RoleUserModel;
        $this->RetailerModel	  = $RetailerModel;
        $this->UserModel          = $UserModel;
        $this->MakerModel         = $MakerModel;
        $this->RoleModel          = $RoleModel;
        $this->RoleUsersModel     = $RoleUsersModel;
        $this->ShopSettings       = $ShopSettings;
        $this->CategoryModel      = $CategoryModel;
        $this->CategoryTranslationModel = $CategoryTranslationModel;
        $this->role = 'maker';
       
    }

  
    public function downloadExcel($type)
    {
      $user_table        =  $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;

      $maker_table        =  $this->MakerModel->getTable();
      $prefix_maker_table = DB::getTablePrefix().$maker_table;

      $category_table        =  $this->CategoryModel->getTable();
      $prefix_category_table = DB::getTablePrefix().$category_table;


      $category_trans_tbl_name      = $this->CategoryTranslationModel->getTable();        
      $prefixed_category_trans_tbl  = DB::getTablePrefix().$this->CategoryTranslationModel->getTable();

      $role_table        =  $this->RoleModel->getTable();
      $prefix_role_table = DB::getTablePrefix().$role_table;

      $role_user_table =  $this->RoleUsersModel->getTable();
      $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

      $shop_setting_table =  $this->ShopSettings->getTable();
      $prefix_shop_setting_table = DB::getTablePrefix().$shop_setting_table;

      $obj_user = DB::table($user_table)
                          ->select(DB::raw(
                            $prefix_maker_table.".user_id as '#', ".
                            $prefix_user_table.".tax_id as 'Tax Id',".
                            $prefix_maker_table.".company_name as 'Company Name',".
                            
                             "CONCAT(".$prefix_user_table.".first_name,' ',"
                                     .$prefix_user_table.".last_name) as 'Vendor Name', ".

                            $prefix_user_table.".email as Email, ".
                            $prefix_user_table.".contact_no as 'Contact No', ".
                            $prefix_user_table.".address as 'Address', ".
                            $prefix_user_table.".is_approved as 'Approval Status', ".
                            $prefix_user_table.".country_id as 'Country', ".
                             $prefix_maker_table.".brand_name as 'Brand Name', ".
                            
                            $category_trans_tbl_name.".category_name as 'Primary Category', ".
                           
                            // $prefix_maker_table.".no_of_stores as 'Total Stores', ".

                            $prefix_shop_setting_table.".first_order_minimum as 'First Order minimum', ".

                            // $prefix_shop_setting_table.".re_order_minimum as 'Re-Order minimum', ".

                            // $prefix_maker_table.".description as 'Description', ".

                            $prefix_user_table.".Status"                          
                          ))

                          ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')

                          ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')


                          ->leftJoin($maker_table,$maker_table.'.user_id','=',$user_table.'.id')

                          ->leftJoin($shop_setting_table,$shop_setting_table.'.maker_id','=',$maker_table.'.user_id')

                          ->leftjoin($category_trans_tbl_name,$category_trans_tbl_name.'.category_id','=',$maker_table.'.primary_category_id')

                          ->where($role_table.'.slug','=',$this->role)
                          ->whereNull($user_table.'.deleted_at')
                          ->where($user_table.'.id','!=',1)
                          ->orderBy($user_table.'.created_at','DESC')->get()->toArray();


      $data = [];
      foreach($obj_user as $key => $value)
      { 
        $array = (array)$value;
        $array['#'] = $key+1;

        if($array['Tax Id']=="")
        {
            $array['Tax Id'] = 'NA';
        }
        if($array['Company Name']=="")
        {
            $array['Company Name'] = 'NA';
        }
        if($array['Vendor Name']=="")
        {
            $array['Vendor Name'] = 'NA';
        }
        if($array['Email']=="")
        {
            $array['Email'] = 'NA';
        }
        if($array['Contact No']=="")
        {
            $array['Contact No'] = 'NA';
        }
        if($array['Address']=="")
        {
            $array['Address'] = 'NA';
        }
        if($array['Country']=="")
        {
            $array['Country'] = 'NA';
        }else{
          $array['Country'] = get_country($array['Country']);
        }
        if($array['Approval Status']==1)
        {
          $array['Approval Status'] = "Active";
        }
        else
        {
          $array['Approval Status'] = "Inactive";
        }
        if($array['Brand Name']=="")
        {
            $array['Brand Name'] = 'NA';
        }
        if($array['Primary Category']=="")
        {
            $array['Primary Category'] = 'NA';
        }
        // if($array['Total Stores']=="")
        // {
        //     $array['Total Stores'] = 'NA';
        // }
        if($array['First Order minimum']=="")
        {
            $array['First Order minimum'] = 'NA';
        }
        // if($array['Re-Order minimum']=="")
        // {
        //     $array['Re-Order minimum'] = 'NA';
        // }
        // if($array['Description']=="")
        // {
        //     $array['Description'] = 'NA';
        // }
        if($array['Status']==1)
        {
          $array['Status']="Active";
        }
        else
        {
          $array['Status']="Inactive";
        }

        array_push($data,$array);      
      }
      
      return Excel::create('Vendor Report', function($excel) use ($data) {
        $excel->sheet('Vendors', function($sheet) use ($data)
        {
          $sheet->fromArray($data);
          $sheet->freezeFirstRow();  

            $sheet->cells("A1:M1", function($cells) {            
              $cells->setFont(array(              
                'bold'       =>  true
              ));
            });
        });

      })->download($type);
    }

    public function downloadExcelRetailer($type)
    {
      $user_table =  $this->UserModel->getTable();
  		$prefix_user_table = DB::getTablePrefix().$user_table;

  		$role_table =  $this->RoleModel->getTable();
  		$prefix_role_table = DB::getTablePrefix().$role_table;

  		$role_user_table =  $this->RoleUsersModel->getTable();
  		$prefix_role_user_table = DB::getTablePrefix().$role_user_table;

  		$retailer_table = $this->RetailerModel->getTable();
  		$prefix_retailer_table = DB::getTablePrefix().$retailer_table;

  		$obj_user = DB::table($user_table)
						->select(DB::raw($prefix_user_table.".id as '#',".
                          "CONCAT(".$prefix_user_table.".first_name,' ',"
                              .$prefix_user_table.".last_name) as 'Retailer Name',".
                            $prefix_user_table.".email as Email, ".
                            $prefix_user_table.".contact_no as 'Contact No', ".
                            $prefix_retailer_table.".store_name as 'Retailer Company Name', ".
                            // $prefix_retailer_table.".store_description as 'Company Description', ".
                            // $prefix_retailer_table.".store_website as ' Company Website', ".

                            $prefix_user_table.".is_approved as 'Approval Status', ".
                            $prefix_user_table.".status_net_30 as 'Net 30 Status', ".
										   
                       
                        
                        //$prefix_user_table.".contact_no as contact_no, ".
									       "CONCAT(".$prefix_retailer_table.".billing_suit_apt,', ',".$prefix_retailer_table.".billing_address,', ',"
                                             .$prefix_retailer_table.".billing_city,', ',".$prefix_retailer_table.".billing_state,', ',".$prefix_retailer_table.".billing_zip_postal_code) as 'Billing Address', ".

                        "CONCAT(".$prefix_retailer_table.".shipping_suit_apt,', ',".$prefix_retailer_table.".shipping_addr,', ',"
                                             .$prefix_retailer_table.".shipping_city,', ',".$prefix_retailer_table.".shipping_state,', ',".$prefix_retailer_table.".shipping_zip_postal_code) as 'Shipping Address', ". 

                        $prefix_user_table.".status as Status"
 
                      ))
						->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
						->leftJoin($retailer_table,$retailer_table.'.user_id','=',$user_table.'.id')
						->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')
						->where($role_table.'.slug','=',"retailer")
                        ->where($user_table.'.id','!=',1)
						->whereNull($user_table.'.deleted_at')
                        ->orderBy($user_table.'.created_at','DESC')->get();

                       
        		$data = [];
 						foreach($obj_user as $key => $value)
            {
              //$data['#']=$value['#'];
              //$data['Retailer Company Name'] = isset($value['Retailer Company Name'])?$value['Retailer Company Name']:'-';
 							$array = (array)$value;
              //dd($array);

 							$array['#'] = $key+1;
              
              if($array['Customer Company Name']=="")
              {
                $array['Customer Company Name'] = "NA"; 
              }
              // if($array['Company Description']=="")
              // {
              //   $array['Company Description'] = "NA"; 
              // }
              // if($array['Company Website']=="")
              // {
              //   $array['Company Website'] = "NA"; 
              // }  
               if($array['Approval Status']==1)
              {
                $array['Approval Status'] = "Active";
              }
              else
              {
                $array['Approval Status'] = "Inactive";
              }
              if($array['Net 30 Status']==1)
              {
                $array['Net 30 Status'] = "Active";
              }
              else
              {
                $array['Net 30 Status'] = "Inactive";
              }
              if($array['Customer Name']=="")
              {
                $array['Customer Name'] = "NA"; 
              }
              if($array['Email']=="")
              {
                $array['Email'] = "NA"; 
              }
              if($array['Contact No']=="")
              {
                $array['Contact No'] = "NA"; 
              }
              if($array['Billing Address']=="")
              {
                $array['Billing Address'] = "NA"; 
              }
              if($array['Shipping Address']=="")
              {
                $array['Shipping Address'] = "NA"; 
              }
              
              if($array['Status']==1)
 							{
 								$array['Status']="Active";
 							}
 							else
 							{
 								$array['Status']="Inactive";
 							}

 							array_push($data,$array);
 						}
             
 		
        return Excel::create('Customer Report', function($excel) use ($data) {
            $excel->sheet('Customers', function($sheet) use ($data)
            {
              $sheet->fromArray($data);
              $sheet->freezeFirstRow();
              
              $sheet->cells("A1:J1", function($cells) {
          
                $cells->setFont(array(
            
                  'bold'       =>  true
                ));
              });

            });
        })->download($type);
    }    

    public function downloadExcelCustomer($type)
    {
      $user_table =  $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;

      $role_table =  $this->RoleModel->getTable();
      $prefix_role_table = DB::getTablePrefix().$role_table;

      $role_user_table =  $this->RoleUsersModel->getTable();
      $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

      // $retailer_table = $this->RetailerModel->getTable();
      // $prefix_retailer_table = DB::getTablePrefix().$retailer_table;

      $obj_user = DB::table($user_table)
            ->select(DB::raw($prefix_user_table.".id as '#',".
                            // $prefix_retailer_table.".store_name as 'Retailer Company Name', ".
                            // $prefix_retailer_table.".store_description as 'Company Description', ".
                            // $prefix_retailer_table.".store_website as ' Company Website', ".
                        "CONCAT(".$prefix_user_table.".first_name,' ',"
                              .$prefix_user_table.".last_name) as 'Customer Name',".
                        $prefix_user_table.".email as Email, ".
                        $prefix_user_table.".contact_no as 'Contact No', ".
                        $prefix_user_table.".country_id ,".
                        //$prefix_user_table.".contact_no as contact_no, ".
                         // "CONCAT(".$prefix_retailer_table.".billing_suit_apt,', ',".$prefix_retailer_table.".billing_address,', ',"
                         //                     .$prefix_retailer_table.".billing_city,', ',".$prefix_retailer_table.".billing_state,', ',".$prefix_retailer_table.".billing_zip_postal_code) as 'Billing Address', ".

                        // "CONCAT(".$prefix_retailer_table.".shipping_suit_apt,', ',".$prefix_retailer_table.".shipping_addr,', ',"
                        //                      .$prefix_retailer_table.".shipping_city,', ',".$prefix_retailer_table.".shipping_state,', ',".$prefix_retailer_table.".shipping_zip_postal_code) as 'Shipping Address', ". 

                        $prefix_user_table.".status as Status"
 
                      ))
            ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
            // ->leftJoin($retailer_table,$retailer_table.'.user_id','=',$user_table.'.id')
            ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')
            ->where($role_table.'.slug','=',"customer")
                        ->where($user_table.'.id','!=',1)
            ->whereNull($user_table.'.deleted_at')
                        ->orderBy($user_table.'.created_at','DESC')->get();

                       
            $data = [];
            foreach($obj_user as $key => $value)
            {
              //$data['#']=$value['#'];
              //$data['Retailer Company Name'] = isset($value['Retailer Company Name'])?$value['Retailer Company Name']:'-';
              $array = (array)$value;

              $array['#'] = $key+1;
              if($array['Customer Name']=="")
              {
                  $array['Customer Name'] = 'NA';
              }
              if($array['Email']=="")
              {
                  $array['Email'] = 'NA';
              }
              if($array['Contact No']=="")
              {
                  $array['Contact No'] = 'NA';
              }
              if($array['country_id']!="")
              {
                $array['Country'] = get_country($array['country_id']);
              }
              else
              {
                $array['Country'] = 'NA';
              }  
              
              if($array['Status']==1)
              {
                $array['Status']="Active";
              }
              else
              {
                $array['Status']="Inactive";
              }
              unset($array['country_id']);
              array_push($data,$array);
            }

            //array_except($array,$array['country_id']);
            //dd($data);
    
        return Excel::create('Customer Report', function($excel) use ($data) {
            $excel->sheet('Customers', function($sheet) use ($data)
            {
              $sheet->fromArray($data);
              $sheet->freezeFirstRow();
              
              $sheet->cells("A1:J1", function($cells) {
          
                $cells->setFont(array(
            
                  'bold'       =>  true
                ));
              });

            });
        })->download($type);
    }

    public function downloadExcelInfluencer($type)
    {
      $user_table =  $this->UserModel->getTable();
      $prefix_user_table = DB::getTablePrefix().$user_table;

      $role_table =  $this->RoleModel->getTable();
      $prefix_role_table = DB::getTablePrefix().$role_table;

      $role_user_table =  $this->RoleUsersModel->getTable();
      $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

      $obj_user = DB::table($user_table)
            ->select(DB::raw($prefix_user_table.".id as '#',".
                        "CONCAT(".$prefix_user_table.".first_name,' ',"
                              .$prefix_user_table.".last_name) as 'Influencer Name',".
                        $prefix_user_table.".email as Email, ".
                        $prefix_user_table.".contact_no as 'Contact No', ".
                        $prefix_user_table.".country_id ,".        
                        $prefix_user_table.".status as Status"
 
                      ))
            ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
            ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')
            ->where($role_table.'.slug','=',"influencer")
                        ->where($user_table.'.id','!=',1)
            ->whereNull($user_table.'.deleted_at')
                        ->orderBy($user_table.'.created_at','DESC')->get();

                       
            $data = [];
            foreach($obj_user as $key => $value)
            {
              //$data['#']=$value['#'];
              //$data['Retailer Company Name'] = isset($value['Retailer Company Name'])?$value['Retailer Company Name']:'-';
              $array = (array)$value;

              $array['#'] = $key+1;
              if($array['Influencer Name']=="")
              {
                  $array['Influencer Name'] = 'NA';
              }
              if($array['Email']=="")
              {
                  $array['Email'] = 'NA';
              }
              if($array['Contact No']=="")
              {
                  $array['Contact No'] = 'NA';
              }
              if($array['country_id']!="")
              {
                $array['Country'] = get_country($array['country_id']);
              }
              else
              {
                $array['Country'] = 'NA';
              }  
              
              if($array['Status']==1)
              {
                $array['Status']="Active";
              }
              else
              {
                $array['Status']="Inactive";
              }
              unset($array['country_id']);
              array_push($data,$array);
            }

            //array_except($array,$array['country_id']);
            //dd($data);
    
        return Excel::create('Influencer Report', function($excel) use ($data) {
            $excel->sheet('Influencers', function($sheet) use ($data)
            {
              $sheet->fromArray($data);
              $sheet->freezeFirstRow();
              
              $sheet->cells("A1:F1", function($cells) {
          
                $cells->setFont(array(
            
                  'bold'       =>  true
                ));
              });

            });
        })->download($type);
    }

  public function downloadExcelRepresentative($type)
  {
    	$user_table =  $this->UserModel->getTable();
		$prefix_user_table = DB::getTablePrefix().$user_table;

		$role_table =  $this->RoleModel->getTable();
		$prefix_role_table = DB::getTablePrefix().$role_table;

		$role_user_table =  $this->RoleUsersModel->getTable();
		$prefix_role_user_table = DB::getTablePrefix().$role_user_table;

		$obj_user = DB::table($user_table)
						->select(DB::raw($prefix_user_table.".id as '#',".
										 "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as 'Reps Name',".
                                              
                                      $prefix_user_table.".contact_no as 'Contact No', ".
									  $prefix_user_table.".email as 'Email', ".
                    $prefix_user_table.".commission as 'Commission (%)',".
                    $prefix_user_table.".is_approved as 'Approval Status', ".
                                     $prefix_user_table.".status as Status"
                                    
                                     ))
						->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
						->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')
						->where($role_table.'.slug','=',"representative")
						->whereNull($user_table.'.deleted_at')
                        ->where($user_table.'.id','!=',1)
                        ->orderBy($user_table.'.created_at','DESC')->get()->toArray();

                       
        				$data = [];
      
 						foreach ($obj_user as $key => $value)
 						{
 							$array = (array)$value;
              $array['#'] = $key+1;
 							if($array['Reps Name']=="")
              {
                $array['Reps Name'] = 'NA'; 
              }
              if($array['Contact No']=="")
              {
                $array['Contact No'] = 'NA'; 
              }
              if($array['Email']=="")
              {
                $array['Email'] = 'NA'; 
              }
              if($array['Commission (%)']=="")
              {
                $array['Commission (%)'] = 'NA'; 
              }
              if($array['Approval Status']==1)
              {
                $array['Approval Status'] = "Active";
              }
              else
              {
                $array['Approval Status'] = "Inactive";
              }
              if($array['Status']==1)
 							{
 								$array['Status'] = "Active";
 							}
 							else
 							{
 								$array['Status'] = "Inactive";
 							}
 							array_push($data,$array);
 						}

        return Excel::create('Representative Report', function($excel) use ($data) {
          //$lastrow= $excel->getActiveSheet()->getHighestRow();    
          $excel->sheet('Representative', function($sheet) use ($data)
          {
            $sheet->fromArray($data);
            $sheet->freezeFirstRow();
               
            
            $sheet->cells("A1:E1", function($cells) {
            
              $cells->setFont(array(
            
                'bold'       =>  true
              ));
            });
          });
        })->download($type);
  }

  public function downloadExcelSalesManager($type)
  {
    $user_table =  $this->UserModel->getTable();
    $prefix_user_table = DB::getTablePrefix().$user_table;

    $role_table =  $this->RoleModel->getTable();
    $prefix_role_table = DB::getTablePrefix().$role_table;

    $role_user_table =  $this->RoleUsersModel->getTable();
    $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

    $obj_user = DB::table($user_table)
            ->select(DB::raw($prefix_user_table.".id as '#',".
                     "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as 'Sales Manager Name',".
                                      $prefix_user_table.".contact_no as 'Contact No', ".
                    $prefix_user_table.".email as 'Email', ".
                    $prefix_user_table.".commission as 'Commission (%)',".
                    $prefix_user_table.".is_approved as 'Approval Status', ".
                                     $prefix_user_table.".status as Status"
                                    
                                     ))
            ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
            ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')
            ->where($role_table.'.slug','=',"sales_manager")
            ->whereNull($user_table.'.deleted_at')
                        ->where($user_table.'.id','!=',1)
                        ->orderBy($user_table.'.created_at','DESC')->get()->toArray();

                       
                $data = [];
            foreach ($obj_user as $key => $value)
            {
              $array = (array)$value;
              $array['#'] = $key+1;
              
              if($array['Sales Manager Name']=="")
              {
                $array['Sales Manager Name'] = 'NA'; 
              }
              if($array['Contact No']=="")
              {
                $array['Contact No'] = 'NA'; 
              }
              if($array['Email']=="")
              {
                $array['Email'] = 'NA'; 
              }
              if($array['Commission (%)']=="")
              {
                $array['Commission (%)'] = 'NA'; 
              }
              if($array['Approval Status']==1)
              {
                $array['Approval Status'] = "Active";
              }
              else
              {
                $array['Approval Status'] = "Inactive";
              }
              if($array['Status']==1)
              {
                $array['Status'] = "Active";
              }
              else
              {
                $array['Status'] = "Inactive";
              }
              array_push($data,$array);
            }

            
    
        return Excel::create('Sales Manager Report', function($excel) use ($data) {
          //$lastrow= $excel->getActiveSheet()->getHighestRow();    
          $excel->sheet('Sales Manager', function($sheet) use ($data)
          {
            $sheet->fromArray($data);
            $sheet->freezeFirstRow();
               
            
            $sheet->cells("A1:G1", function($cells) {
            
              $cells->setFont(array(
            
                'bold'       =>  true
              ));
            });
          });
        })->download($type);
  }


  public function order_report($data)
  {

   
      $type = "xlsx";
          
      return  Excel::create('Order Report', function($excel) use ($data) {
        $excel->sheet('Order Report', function($sheet) use ($data)
        {
            $sheet->fromArray($data);
            $sheet->freezeFirstRow();
            $sheet->cells("A1:I1", function($cells) {
            
            $cells->setFont(array(
          
              'bold'       =>  true
            ));
            });
           

        });
      })->download($type);
   
  }

  public function notification_report($notification)
  {
              
      $type = "xlsx";
          
      return  Excel::create('Notification Report', function($excel) use ($notification) {
        $excel->sheet('Notification Report', function($sheet) use ($notification)
        {
            $sheet->fromArray($notification);
            $sheet->freezeFirstRow();
            $sheet->cells("A1:I1", function($cells) {
            
            $cells->setFont(array(
          
              'bold'       =>  true
            ));
            });
           

        });
      })->download($type);
  }

  
}