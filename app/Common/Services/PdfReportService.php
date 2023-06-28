<?php
namespace App\Common\Services;

use DB;
use PDF;
use Illuminate\Http\Request;
use App\Models\UserModel;
use App\Models\MakerModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\RetailerModel;


 
class PdfReportService 
{
 
   
 public function __construct(
                                RoleModel $RoleModel,
                                RoleUsersModel $RoleUsersModel,
                                MakerModel $MakerModel,
                                RoleUsersModel $RoleUserModel, 
                                UserModel $UserModel,
                                RetailerModel $RetailerModel
                            )
    {
        $this->RoleUserModel      = $RoleUserModel;
        $this->RetailerModel	  = $RetailerModel;
        $this->UserModel          = $UserModel;
        $this->MakerModel         = $MakerModel;
        $this->RoleModel          = $RoleModel;
        $this->RoleUsersModel     = $RoleUsersModel;
        $this->role = 'maker';
       
    }




    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function importExport()
    {
      
       return view('importExport');
    }
 
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function download_pdf($type)
    {
        $data = UserModel::select('first_name','last_name','email','contact_no')->get()->toArray();
            
        


        $user_table        =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $maker_table        =  $this->MakerModel->getTable();
        $prefix_maker_table = DB::getTablePrefix().$maker_table;

        $role_table        =  $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $role_user_table =  $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

     



$obj_user = DB::table($user_table)
                        ->select(DB::raw(
                                          $prefix_maker_table.".user_id, ".
                                         "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                 .$prefix_user_table.".last_name) as user_name, ".

                                        $prefix_user_table.".tax_id ,".       
                                        $prefix_user_table.".email as email, ".
                                       
                                        $prefix_user_table.".contact_no as phone, ".

                                        $prefix_maker_table.".brand_name, ".
                                       
                                        $prefix_maker_table.".company_name,".
                                        $prefix_user_table.".status "

                                       
                                      ))

                        ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')

                        ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')

                        ->leftJoin($maker_table,$maker_table.'.user_id','=',$user_table.'.id')

                        ->where($role_table.'.slug','=',$this->role)
                        ->whereNull($user_table.'.deleted_at')
                        ->where($user_table.'.id','!=',1)
                        ->orderBy($user_table.'.created_at','DESC')->get()->toArray();




              $data = [];
               $count = 1;
               foreach ($obj_user as $key => $value) {
               $array = (array)$value;
               
              $array = (array)$value;
              if($array['user_name']=="")
              {
                $array['user_name'] = 'NA'; 
              }
              if($array['tax_id']=="")
              {
                $array['tax_id'] = 'NA'; 
              }
              if($array['email']=="")
              {
                $array['email'] = 'NA'; 
              }
              if($array['phone']=="")
              {
                $array['phone'] = 'NA'; 
              }
              if($array['brand_name']=="")
              {
                $array['brand_name'] = 'NA'; 
              }
              if($array['company_name']=="")
              {
                $array['company_name'] = 'NA'; 
              }  
 							if($array['status']==1)
 							{
 								$array['status']="Active";
                $array['user_id'] = $count;
 							}
 							else
 							{
 								$array['status']="Inactive";
                $array['user_id'] = $count;
 							}

 array_push($data,$array);
 $count++;
 	
 }

  
 $inventory_pdf = $pdf = PDF::loadView('admin/reports_view/vendor_report',compact('data'));
 return $inventory_pdf;


}

public function downloadPdfRetailer($type)
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
                            $prefix_retailer_table.".store_name as 'Retailer Company Name', ".
                            $prefix_retailer_table.".store_description as 'Company Description', ".
                            $prefix_retailer_table.".store_website as ' Company Website', ".
                            $prefix_retailer_table.".store_description as 'Company Description', ".
                        "CONCAT(".$prefix_user_table.".first_name,' ',"
                              .$prefix_user_table.".last_name) as 'Retailer Name',".

                        $prefix_user_table.".email as Email, ".
                        $prefix_user_table.".contact_no as 'Contact No', ".
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
              $array = (array)$value;
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
            // dd($data);
    
       $inventory_pdf = $pdf = PDF::loadView('admin/reports_view/retailer_report',compact('data'));
 return $inventory_pdf;
    }

public function downloadPdfCustomer($type)
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
                                             // .$prefix_retailer_table.".billing_city,', ',".$prefix_retailer_table.".billing_state,', ',".$prefix_retailer_table.".billing_zip_postal_code) as 'Billing Address', ".

                        // "CONCAT(".$prefix_retailer_table.".shipping_suit_apt,', ',".$prefix_retailer_table.".shipping_addr,', ',"
                                             // .$prefix_retailer_table.".shipping_city,', ',".$prefix_retailer_table.".shipping_state,', ',".$prefix_retailer_table.".shipping_zip_postal_code) as 'Shipping Address', ". 

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
            // dd($data);
    
       $inventory_pdf = $pdf = PDF::loadView('admin/reports_view/customer_report',compact('data'));
      return $inventory_pdf;
    }
  public function downloadPdfInfluencer($type)
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
          // dd($data);
  
     $inventory_pdf = $pdf = PDF::loadView('admin/reports_view/influencer_report',compact('data'));
    return $inventory_pdf;
  }

public function downloadPdfRepresentative($type)
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
    
       $inventory_pdf = $pdf = PDF::loadView('admin/reports_view/representative_report',compact('data'));
 return $inventory_pdf;
    }

    public function downloadPdfSalesManager($type)
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
              if($array['Status']==1)
              {
                $array['Status'] = "Active";
              }
              else
              {
                $array['Status'] = "Inactive";
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
              array_push($data,$array);
            }
    
       $inventory_pdf = $pdf = PDF::loadView('admin/reports_view/sales_manager_report',compact('data'));
 return $inventory_pdf;
    }
}