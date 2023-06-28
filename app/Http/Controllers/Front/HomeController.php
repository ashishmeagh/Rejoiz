<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\StaticPageModel;
use App\Models\CategoryModel;
use App\Models\ProductsModel;
use App\Models\BannerImageModel;
use App\Models\PromotionsModel;
use App\Models\PromotionsTypeModel;
use App\Models\EmailSubscriptiponModel;
use App\Models\VendorRepresentativeMappingModel;
use App\Models\RepresentativeModel;
use App\Models\SalesManagerModel;
use App\Models\RepAreaModel;
use App\Models\UserModel;
use App\Models\StateModel;
use App\Models\RoleUsersModel;
use App\Models\StripeAccountDetailsModel;
use App\Models\RoleModel;
use App\Models\MakerModel;
use App\Models\ShopImagesModel;
use App\Models\CategoryTranslationModel;
use App\Models\CategoryDivisionModel;
use App\Models\SiteSettingModel;
use App\Models\RetailerModel;
use App\Common\Services\EmailService;
use App\Common\Services\StripePaymentService;
use App\Common\Services\ElasticSearchService;
use App\Common\Services\HelperService;
use Stripe;
use DB;
use Mail;
use Validator;
use Session;
use Paginate;
use Flash;
use Sentinel;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
// use App\Models\PropertyModel;

class HomeController extends Controller
{
    public function __construct(StaticPageModel $StaticPageModel,
                                CategoryModel   $CategoryModel,
                                ProductsModel   $ProductsModel,
                                BannerImageModel $BannerImageModel,
                                EmailSubscriptiponModel $EmailSubscriptiponModel,
                                EmailService    $EmailService,
                                RepresentativeModel $RepresentativeModel,
                                SalesManagerModel $SalesManagerModel,
                                PromotionsModel $PromotionsModel,
                                StripePaymentService $StripePaymentService,
                                UserModel $UserModel,
                                StateModel $StateModel,
                                RoleModel $RoleModel,
                                RoleUsersModel $RoleUsersModel,
                                CategoryTranslationModel $CategoryTranslationModel,
                                PromotionsTypeModel $PromotionsTypeModel,
                                VendorRepresentativeMappingModel $VendorRepresentativeMappingModel,
                                RepAreaModel $RepAreaModel,
                                StripeAccountDetailsModel $StripeAccountDetailsModel,
                                MakerModel $MakerModel,
                                ShopImagesModel $ShopImagesModel,
                                CategoryDivisionModel $CategoryDivisionModel,
                                SiteSettingModel $SiteSettingModel,
                                RetailerModel  $RetailerModel,
                                ElasticSearchService $ElasticSearchService,
                                HelperService $HelperService

                            )
    { 
        // $this->middleware('auth')->except('front.index');       
        $this->StaticPageModel        = $StaticPageModel;
        $this->CategoryModel          = $CategoryModel;
        $this->ProductsModel          = $ProductsModel;
        $this->BannerImageModel       = $BannerImageModel;
        $this->EmailSubscriptiponModel= $EmailSubscriptiponModel;
        $this->EmailService           = $EmailService;
        $this->RepresentativeModel    = $RepresentativeModel;
        $this->SalesManagerModel      = $SalesManagerModel;
        $this->CategoryTranslationModel = $CategoryTranslationModel;
        $this->UserModel              = $UserModel;
        $this->StateModel             = $StateModel;
        $this->PromotionsModel        = $PromotionsModel;
        $this->StripePaymentService   = $StripePaymentService;
        $this->VendorRepresentativeMappingModel = $VendorRepresentativeMappingModel;
        $this->PromotionsTypeModel    = $PromotionsTypeModel;
        $this->RepAreaModel           = $RepAreaModel;
        $this->RoleModel              = $RoleModel;
        $this->RoleUsersModel         = $RoleUsersModel;
        $this->StripeAccountDetailsModel=$StripeAccountDetailsModel;
        $this->MakerModel             = $MakerModel;
        $this->ShopImagesModel        = $ShopImagesModel;
        $this->CategoryDivisionModel  = $CategoryDivisionModel;
        $this->SiteSettingModel       = $SiteSettingModel;
        $this->RetailerModel          = $RetailerModel;
        $this->ElasticSearchService   = $ElasticSearchService;
        $this->HelperService          = $HelperService;

        $this->GlobalPropertiesModel  = false;
        $this->PropertyModel          = false;
        $this->locale                 = \App::getLocale();
        $this->module_view_folder     = 'front';  
        $this->arr_view_data          = [];
        $this->module_url_path      = url('/');
    }

    public function index(Request $request)
    {   
        /*$toUserData['company_name']  = 'Company name';
        $toUserData['user_name']  = 'Vendor name';
        $toUserData['address']  = 'Altai Republic, Russia, 649789 ';

        $invoiceData['invoice_no'] = '4654648';
        $invoiceData['invoice_date'] = '10/12/2021';

        return view('front.admin_commission_paid_invoice',compact('toUserData','invoiceData'));*/


        $request_data  = $request->all();
        
        $response_code = isset($request_data['code'])?$request_data['code']:false;
        $state         = isset($request_data['state'])?$request_data['state']:false;
        
        $divideState = explode('/', $state);

        $user_id    = isset($divideState[0])?$divideState[0]:false;
        $vendor_id  = isset($divideState[1])?$divideState[1]:false;
        
        $isRoleMaker = $isRoleAdmin = false;

        if($user_id != false)
        {
            
          $isRoleMaker = Sentinel::findUserById($user_id)->inRole('maker');
          
          $isRoleAdmin = Sentinel::findUserById($user_id)->inRole('admin');

        }

        /*get admin id*/
        $adminUserId = get_admin_id();

        $adminStripKeyId = $vendorStripKeyId = false;
            
    
        if($isRoleMaker || $isRoleAdmin)
        {
            if($user_id && $vendor_id)
            {
                $stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($vendor_id);
                $vendorStripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';                
            }
            else
            {
                $stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($user_id);
                $vendorStripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:''; 
            }

        }

        $stripeApiKeyData = $this->StripePaymentService->get_active_stripe_key($adminUserId);
        $adminStripKeyId = isset($stripeApiKeyData['id'])?$stripeApiKeyData['id']:'';
        
        if($response_code && $user_id)
        {   
            $obj_stripe_account_details = $this->StripeAccountDetailsModel
                                                          ->where('user_id',$user_id)
                                                          ->where('vendor_id',$vendor_id)
                                                          ->where('vendor_stripe_key_id',$vendorStripKeyId)
                                                          ->where('admin_stripe_key_id',$adminStripKeyId)
                                                          ->count();
        
            if($obj_stripe_account_details > 0)
            {
                \Flash::error('You have already connection with '.get_site_settings(['site_name'])['site_name'].' stripe account.');
                return redirect('/');
            }

            /* get login user details */
            $user_data = \Sentinel::findById($user_id);

            /* get user role */

            if($user_data)
            {   
                $customer_data['email'] = $user_data->email;
                $customer_data['id']    = $user_data->id;
               
                if($vendor_id != false)
                {
                  $customer_data['vendor_id'] = $vendor_id;
                }

                /* create_stripe_account */
                $acc_response = $this->StripePaymentService->create_stripe_account($response_code,$customer_data,$adminStripKeyId,$vendorStripKeyId);
                
                if(isset($acc_response['status']) && $acc_response['status'] == 'Error')
                {
                    \Flash::error(isset($acc_response['description'])?$acc_response['description']:'Something went wrong, please try again.');
                }
                else
                {
                    //\Flash::success('We have successfully associated with stripe account with us.');
                    \Flash::success('We have successfully associated your stripe account with us. Please initiate payment again.');
                }
                
                return redirect('/');

            }
        }
        
         $categories_arr = $this->CategoryModel->where('is_active','1')
                                                ->orderBy('priority','DESC')
                                                ->take(12)
                                                ->get()
                                                ->toArray();
                                                

         /* Sort by Alpha */ 
        usort($categories_arr, function($sort_base, $sort_compare) {
            return $sort_base['category_name'] <=> $sort_compare['category_name'];
        });   



        $cms_page_arr = $this->StaticPageModel->where('is_active','1')
                                              ->orderBy('id','DESC')
                                              ->get()
                                              ->toArray();

        // $product_arr = $this->ProductsModel->where('is_active','1')
        //                                     ->where('is_deleted','0')
        //                                     ->where('user_id','722')
        //                                     ->where('product_complete_status',4)    
        //                                     ->orderBy('updated_at','DESC')
        //                                     ->take(8)
        //                                     ->get()
        //                                     ->toArray();

       

        $request->request->add(['category'=>'new_arrivals']);
        //dd($request->all());
        $temp_product_arr = $this->ElasticSearchService->search($request);
        $product_arr = array();

        // dd($product_arr);
        if(isset($temp_product_arr['arr_data']) && count($temp_product_arr['arr_data']>0))
          {
              foreach ($temp_product_arr['arr_data'] as $key => $value) {
                if($key <= 7){
                  $temp_product_data = $value['_source'];
                  if(isset($temp_product_data))
                  {
                    array_push($product_arr,$temp_product_data);
                  }
                }
              }
          }

        $banner_img1_arr = $this->BannerImageModel->where('type',1)->get()->toArray();
        $banner_img2_arr = $this->BannerImageModel->where('type',2)->get()->toArray();
        $banner_img_arr  = $this->BannerImageModel->where('type',3)->get()->toArray();

        /* get login user details */
        $is_login          = "";
        $obj_data          = '';
        $obj_data          = Sentinel::getUser();
        if($obj_data)
        {
           $user_id  = $obj_data->id;
           $is_login = $obj_data->is_login;
        }

        $arr_meta_details['meta_title']    = 'Home';
        $arr_meta_details['meta_large_image_content']  = 'home_large_image';

        $this->arr_view_data['is_search_box_visible']     =  'yes';
        $this->arr_view_data['banner_img_arr']            =  $banner_img_arr;
        $this->arr_view_data['banner_img1_arr']           =  $banner_img1_arr;
        $this->arr_view_data['banner_img2_arr']           =  $banner_img2_arr;
        $this->arr_view_data['categories_arr']            =  $categories_arr;
        $this->arr_view_data['cms_page_arr']              =  $cms_page_arr;
        $this->arr_view_data['product_arr']               =  $product_arr;
        $this->arr_view_data['page_title']                =  'Home';
        $this->arr_view_data['meta_details']              =  $arr_meta_details;         
        $this->arr_view_data['is_login']                  =  $is_login;  

        $this->arr_view_data['login_user'] = Sentinel::check();       
        //dd($this->arr_view_data);       
        $this->arr_view_data['module_url_path']           = $this->module_url_path;
        if(Session::has('products.name')!="true")
        {
            session()->put('products.name', []);
        }

        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function cms_page($page_slug = false)
    {
      

        $cms_page_arr = [];

        $cms_page_obj = $this->StaticPageModel->whereTranslation('locale',$this->locale)
                                              ->whereTranslation('page_slug',$page_slug)
                                              ->first();
        /*dd($cms_page_obj);*/
        if($cms_page_obj)
        {
            $cms_page_arr = $cms_page_obj->toArray();
        }
        else
        {
            return redirect()->back();
        }

        $this->arr_view_data['page_title']       = $page_slug;
        $this->arr_view_data['cms_page_arr']     = $cms_page_arr;
        $this->arr_view_data['meta_desc']        = $cms_page_arr['meta_desc'] or '';
        $this->arr_view_data['meta_keywords']    = $cms_page_arr['meta_keyword'] or '';

        return view($this->module_view_folder.'.cms_pages',$this->arr_view_data);
        
    }

    public function blockchain_platform(){
        $this->arr_view_data['page_title'] = "Blockchain Platform";

        return view($this->module_view_folder.'.blockchain_platform',$this->arr_view_data);
    }

     ##email#

    public function subscribe(Request $request)
    {
       
        $form_data = $request->all();
       
        $arr_rules = [
                       'email'=>'required|email'
                    ];
       
        $validator = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
           $response['status']      = "warning";
           $response['description'] = "Provided email should not be blank or invalid.";
           return response()->json($response);
        }
       
        /* Check for email duplication */
        $is_duplicate =  $this->EmailSubscriptiponModel
                              ->where('email',$request->input('email'))
                              ->count();  
        
        if($is_duplicate > 0)
        {
           $response['status']      = "warning";
           $response['description'] = "You have already subscribed to our newsletter.";
           return response()->json($response);
        }                 
   
        $arr_user_data['email'] = $request->input('email');
        
        $status = $this->EmailSubscriptiponModel->create($arr_user_data);  
        
         
        /*Get site setting data from helper*/
        $arr_site_setting = get_site_settings(['site_name','website_url']);


        $credentials = ['email' => $arr_user_data['email']];
      
        $arr_user = get_user_by_credentials($credentials);

        if($arr_user == false){
            $arr_user['email'] = $request->input('email');
        }

        if($status)
        {
          
            $subscribed_email  = $arr_user_data['email'];
           
            $arr_built_content = [
                                    'email'          => $subscribed_email,
                                    'SITE_URL'       => $arr_site_setting['website_url'],
                                    'PROJECT_NAME'   => $arr_site_setting['site_name'],
                                ];
   

            $arr_mail_data                      = [];
            $arr_mail_data['email_template_id'] = '35';
            $arr_mail_data['arr_built_content'] = $arr_built_content;
            $arr_mail_data['arr_user']          = $arr_user;
               
          
            try
            {                
                //$is_mail_sent = $this->EmailService->send_subscribe_email($arr_mail_data);
                $is_mail_sent = $this->EmailService->send_mail($arr_mail_data);

                //send mail to admin
                 
                $admin_mail  = get_admin_email();

                $credentials = ['email' => $admin_mail];
      
                $arr_user    = get_user_by_credentials($credentials);


                $arr_built_content = [
                                       'email'          => $subscribed_email,
                                       'PROJECT_NAME'   => $arr_site_setting['site_name']
                                    ];
   

                $arr_mail_data                      = [];
                $arr_mail_data['email_template_id'] = '82';
                $arr_mail_data['arr_built_content'] = $arr_built_content;
                $arr_mail_data['arr_user']          = $arr_user;


                $is_mail_sent = $this->EmailService->send_mail($arr_mail_data);

             
            }
            catch(\Exception $e)
            { 
                $response['status']       = 'error';
                $response['description']  = $e->getMessage();

                return response()->json($response);
            }

                

            $response['description'] = "You have subscribed.";
            $response['status']      = "success";
            $response['url']         = url("/");

            return response()->json($response);
      
        }
    
       
    }    

    public function about_us()
    {
        $this->arr_view_data['page_title']  =  'About';
        return view('cms_pages.about_us',$this->arr_view_data); 
    }


    public function get_promotions($area_id=false,$category_id=false)
    { 
        $area_id     = isset($area_id)?base64_decode($area_id):false;
        $category_id = isset($category_id)?base64_decode($category_id):false;

        /*get all ppromotions from area */
        $promotions_arr = $representative_details = $vendor_arr = [];

        $current_month = date('Y-m-d');

    
        if($area_id == false && $category_id == false)    
        { 
            $promotions_details = $this->PromotionsModel->with(['get_promotions_offer_details','get_maker_details.shop_store_images','get_promo_code_details','get_user_details'])          
                                    ->whereHas('get_user_details',function($q) 
                                    {  
                                        $q->where('status',1);
                                        $q->where('is_approved',1);
                                    })
                                   ->where('is_active','1')                                   
                                   ->orderBy('created_at','DESC')                                   
                                   ->where('to_date','>=',$current_month)                    
                                   ->get()
                                   ->toArray();

            //dd( $promotions_details->get()->toArray());

            $promotions_arr = $promotions_details;

        }
        else
        {
           
       
            $representative_details = $this->RepresentativeModel->with(['get_rep_vendor.get_user_details'               
                                                                        ,'get_rep_vendor.get_promotions.get_maker_details.shop_store_images'
                                                                        ,'get_rep_vendor.get_promotions.get_promotions_offer_details'
                                                                        ,'get_rep_vendor.get_promotions.get_promo_code_details'
                                                                      ])


                                                        ->with(['get_rep_vendor.get_promotions'=>function($q) use($current_month)
                                                        {  
                                                            return $q->where('to_date','>',$current_month);
                                                        }])
                                                        
                                                        ->where('area_id',$area_id);

                                                        if(isset($category_id) && $category_id!='')
                                                        {
                                                          $representative_details = $representative_details->where('category_id','LIKE','%'.$category_id.'%');
                                                        }

            $representative_details =   $representative_details->get()
                                                               ->toArray();

            if(isset($representative_details) && count($representative_details)>0) 
            {
                foreach($representative_details as $key => $representative)
                {  
                    if(isset($representative['get_rep_vendor']) && count($representative['get_rep_vendor'])>0)
                    {  
                        foreach ($representative['get_rep_vendor'] as $key => $vendor)
                        {
                           
                           $vendor_arr[] = $vendor;
                        }
                        
                    }
                }
            }


            if(isset($vendor_arr) && count($vendor_arr)>0)
            {
                foreach($vendor_arr as $key => $vendor)
                {
                    if(isset($vendor['get_promotions']) && count($vendor['get_promotions'])>0)
                    {
                       $promotions_arr = $vendor['get_promotions'];

                       //$promotions_arr = $promotions_arr->orderBy('created_at','DESC');
                    }
                }
            }                                            
        }

         /*get area name from area id*/
          
          if($area_id == '' && $category_id == '')
          {
             $area_name = "All Offers";
          }
          else
          {
            $area_name     = get_area_name($area_id);
            //$category_name = get_catrgory_name($category_id);
            $category_name = $this->HelperService->get_cat_division($category_id);
          }

        $this->arr_view_data['promotion_arr']     = $promotions_arr; 
        $this->arr_view_data['area_name']         = isset($area_name)?$area_name:''; 
        $this->arr_view_data['category_name']     = isset($category_name)?$category_name:''; 
        $this->arr_view_data['page_title']        = 'Offers'; 

        return view('front.promotion',$this->arr_view_data);

    }

    public function find_rep($area_id=false,$category_id=false ,Request $request)
    {

        $area_id     = base64_decode($area_id);
        $category_id = base64_decode($category_id);
        $state_details_arr = $state_id_arr = [];

       
     
        $area_table                  =  $this->RepAreaModel->getTable();
       
        $prefix_area_table           = DB::getTablePrefix().$area_table;

        $representative_table        = $this->RepresentativeModel->getTable();

        $prefix_representative_table = DB::getTablePrefix().$representative_table;

        $user_table                  = $this->UserModel->getTable();

        $prefix_user_table           = DB::getTablePrefix().$user_table;

        $state_table                 = $this->StateModel->getTable();

        $prefix_state_table          = DB::getTablePrefix().$state_table;
    
        $role_table                  = $this->RoleModel->getTable();

        $prefix_role_table           = DB::getTablePrefix().$role_table;

        $role_users_table            = $this->RoleUsersModel->getTable();

        $prefix_role_users_table     = DB::getTablePrefix().$role_users_table;

        $sales_manager_table         = $this->SalesManagerModel->getTable();
        $prefix_sales_manager_table  = DB::getTablePrefix().$sales_manager_table;

       

       /* $category_division           = $this->CategoryDivisionModel->getTable();
        $prefix_category_division    = DB::getTablePrefix().$category_division;
*/
       
        $rep_details = DB::table($representative_table)
                                ->select(DB::raw($prefix_representative_table.".*,".  
                                                
                                                 $prefix_state_table.".name,".
                                                 $prefix_role_table.".name,".
                                                 $prefix_user_table.".profile_image as profile_image, ".
                                                 "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                          .$prefix_user_table.".last_name) as user_name"
                                                )
                                               )                                
                                ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_representative_table.'.user_id')

                                ->leftjoin($prefix_role_users_table,$prefix_role_users_table.'.user_id','=',$prefix_representative_table.'.user_id')

                                ->leftjoin($prefix_role_table,$prefix_role_table.'.id','=',$prefix_role_users_table.'.role_id')

                                ->leftjoin($prefix_area_table,$prefix_area_table.'.id','=',$prefix_representative_table.'.area_id')

                                ->leftjoin($prefix_state_table,$prefix_state_table.'.id','=',$prefix_area_table.'.state_id')

                                ->where($prefix_user_table.'.status',1)
                                ->where($prefix_user_table.'.is_approved',1);


                                // if($area_id!="" && $category_id!="")
                                if($area_id!="")   
                                {
                                    if(isset($category_id) && $category_id!='')
                                    { 
                                        $rep_details =  $rep_details->where('area_id',$area_id)
                                                                     ->where($prefix_representative_table.'.category_id', 'LIKE', '%'.$category_id.'%'); 
                                    }
                             
                                    
                                    $rep_details = $rep_details->where('area_id',$area_id);

                                    $rep_details = $rep_details->paginate(12);

        

                                    if(isset($rep_details))
                                    {
                                        $arr_rep_pagination   = clone $rep_details;
                                        $rep_details_arr      = $rep_details->toArray();
                                    }  



                                    /*get all state of that area*/
                                    $area_obj = $this->RepAreaModel->where('id',$area_id)->first();

                                    if(isset($area_obj))
                                    {
                                        $area_arr          = $area_obj->toArray(); 
                                        $state_id_arr      = json_decode($area_arr['state_id']);
                                        $state_details_arr = $this->StateModel->whereIn('id',$state_id_arr)->get()->toArray(); 
                                    }


                                    $this->arr_view_data['state_details_arr']           = $state_details_arr;
                                    $this->arr_view_data['rep_details_arr']             = $rep_details_arr;
                                    $this->arr_view_data['representative_pagination']   = $arr_rep_pagination;
                                    $this->arr_view_data['page_title']                  = 'Find Your Rep';


                                }
                                else
                                { 
                                    $rep_details = $rep_details->paginate(12);

                                    if(isset($rep_details))
                                    {
                                        $arr_rep_pagination   = clone $rep_details;
                                        $rep_details_arr      = $rep_details->toArray();
                                    } 
                                   
                                   
                                    $this->arr_view_data['rep_details_arr']             = $rep_details_arr;
                                    $this->arr_view_data['representative_pagination']   = $arr_rep_pagination;
                                    $this->arr_view_data['sales_manager_arr']           = [];
                                    $this->arr_view_data['page_title']                  = 'Find Your Rep';

                                }
                                 

   
        if(isset($area_id) && $area_id!="")
        {
            $sales_manager_details = DB::table($sales_manager_table)
                                ->select(DB::raw($prefix_sales_manager_table.".*,". 
                                                 $prefix_role_table.".name,". 
                                                
                                                 $prefix_user_table.".profile_image as profile_image, ".
                                                 "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                          .$prefix_user_table.".last_name) as user_name"))
                                ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_sales_manager_table.'.user_id')
                                ->leftjoin($prefix_role_users_table,$prefix_role_users_table.'.user_id','=',$prefix_sales_manager_table.'.user_id')
                                ->leftjoin($prefix_role_table,$prefix_role_table.'.id','=',$prefix_role_users_table.'.role_id')
                                ->leftjoin($prefix_area_table,$prefix_area_table.'.id','=',$prefix_sales_manager_table.'.area_id')

                                ->where($prefix_user_table.'.status',1)
                                ->where($prefix_user_table.'.is_approved',1)
                                ->where('area_id',$area_id)

                                ->where($prefix_user_table.'.status',1);

                                if (isset($category_id) && $category_id != "") 
                                {
                                    $sales_manager_details = $sales_manager_details->where($sales_manager_table.'.category_id',$category_id);
                                }
                                
                                $sales_manager_details = $sales_manager_details->where($prefix_user_table.'.is_approved',1)
                                ->where('area_id',$area_id)
                                ->first();


        $this->arr_view_data['sales_manager_arr'] = $sales_manager_details;                       


         }

        /*get category name and area name from there id*/
        if($area_id!="" || $category_id!="")
        {
            //$category_name = get_catrgory_name($category_id);

            $category_name = $this->HelperService->get_cat_division($category_id);


            $area_name     = get_area_name($area_id);

            //get type

            //$area_type = $this->RepAreaModel->where('id',$area_id)->pluck('area_type')->first();
            $area_type = get_area_type($area_id);
             

            $this->arr_view_data['category_name']   = $category_name;
            $this->arr_view_data['area_name']       = $area_name;
            $this->arr_view_data['area_type']       = $area_type;
            $this->arr_view_data['page_title']      = 'Find Your Rep';

        }

        /* get all area details*/
        $area_details = [];
        $area_details = $this->RepAreaModel->where('status',1)
                                           ->orderBy('area_name')
                                           ->get()
                                           ->toArray();        
        $this->arr_view_data['area_arr'] = $area_details;


        /*get all active retailers*/

        $retailer_arr = $country_arr = [];
  

        $user_table =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $role_table =  $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $role_user_table =  $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;


        $maker_table                 = $this->MakerModel->getTable();
        $prefix_maker_table          = DB::getTablePrefix().$maker_table;
       
        $obj_user = DB::table($user_table)
                        ->select(DB::raw($prefix_user_table.".id as id,".
                                    $prefix_user_table.".email as email, ".
                                    $prefix_user_table.".status, ".
                                    $prefix_user_table.".is_approved, ".
                                    $prefix_user_table.".wallet_address as wallet_address,".
                                    $prefix_user_table.".status_net_30,".
                                    $prefix_user_table.".contact_no as contact_no, ".
                                    $role_table.".slug as slug, ".
                                    $role_table.".name as name, ".
                                    
                                    $prefix_maker_table.".company_name,".
                                     
                                    "CONCAT(".$prefix_user_table.".first_name,' ',"
                                             .$prefix_user_table.".last_name) as user_name"
                                    ))

                        ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
                        ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')

                        ->leftJoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_user_table.'.id')
                       
                        ->where($role_table.'.slug','=','maker')
                        ->where($user_table.'.id','!=',1)
                        ->where($user_table.'.status','=',1)
                        ->where($user_table.'.is_approved','=',1)
                        ->whereNull($user_table.'.deleted_at')
                        ->orderBy($prefix_maker_table.".company_name");

        $vendor_arr = $obj_user->get()->toArray();


        $this->arr_view_data['vendor_arr'] = $vendor_arr;

    
        return view('front.find_your_rep',$this->arr_view_data);

    }

    public function rep_details($rep_id)
    {
       
        $rep_id = base64_decode($rep_id);
      
        $representative_table = $this->RepresentativeModel->getTable();

        $prefix_representative_table = DB::getTablePrefix().$representative_table;

      
        $user_table =$this->UserModel->getTable();

        $prefix_user_table = DB::getTablePrefix().$user_table;

        $role_table = $this->RoleModel->getTable();

        $prefix_role_table = DB::getTablePrefix().$role_table;

        $role_users_table = $this->RoleUsersModel->getTable();

        $prefix_role_users_table = DB::getTablePrefix().$role_users_table;

        $representative_details = DB::table($representative_table)
                                ->select(DB::raw($prefix_representative_table.".*,".  
                                                 $prefix_role_table.".name,".
                                                 $prefix_user_table.".profile_image as profile_image, ".
                                                 $prefix_user_table.".contact_no, ".
                                                 $prefix_user_table.".email, ".
                                                 "CONCAT(".$prefix_user_table.".first_name,' ',"
                                                          .$prefix_user_table.".last_name) as user_name"))
                                ->leftjoin($prefix_user_table,$prefix_user_table.'.id','=',$prefix_representative_table.'.user_id')
                                 ->leftjoin($prefix_role_users_table,$prefix_role_users_table.'.user_id','=',$prefix_representative_table.'.user_id')
                                ->leftjoin($prefix_role_table,$prefix_role_table.'.id','=',$prefix_role_users_table.'.role_id')
                                ->where($prefix_representative_table.'.user_id',$rep_id)
                                ->where($prefix_user_table.'.status',1)
                                ->first();


        $vendor_details = $this->VendorRepresentativeMappingModel->where('representative_id',$rep_id)
                                                            ->with(['get_maker_details.shop_store_images','get_user_details'])

                                                            ->whereHas('get_user_details',function($q){
                                                                    $q->where('status',1);
                                                                    $q->where('is_approved',1);
                                                            })

                                                            ->get();

 

        if(isset($vendor_details))
        {
            $vendor_details_arr = $vendor_details->toArray(); 
            
        }

        //get type

        if(isset($representative_details->area_id) && $representative_details->area_id!='')
        {
           $area_type = $this->RepAreaModel->where('id',$representative_details->area_id)->pluck('area_type')->first();
        }
           

        $this->arr_view_data['representative_details_arr'] = $representative_details;
        $this->arr_view_data['vendor_details_arr']         = $vendor_details_arr;
        $this->arr_view_data['page_title']                 = 'Find Your Rep';
        $this->arr_view_data['area_type']                  = isset($area_type)?$area_type:'';

        return view('front.rep_details',$this->arr_view_data);
    }


    public function sales_manager_details($enc_sales_manager_id)
    {
        $sales_manager_arr = $area_category_arr = []; 
        $sales_manager_id  = base64_decode($enc_sales_manager_id);

        // $sales_manager_details = $this->UserModel->where('id',$sales_manager_id)->with(['sales_manager_details.areas_details'])->get();
        $sales_manager_details = $this->SalesManagerModel->where('user_id',$sales_manager_id)->with('get_user_data','areas_details')->get()->toArray();

        // dd($sales_manager_details);
        if(isset($sales_manager_details) && count($sales_manager_details)>0)
        { 

            foreach ($sales_manager_details as $key => $areas)
            { 
                if (isset($areas['area_id'])) {

                    if(isset($areas['areas_details']) && count($areas['areas_details'])>0)
                    { 
                        $area_category_arr[$key]['area_id'] =   $areas['area_id'];
                        $area_category_arr[$key]['area_name'] = $areas['areas_details'][0]['area_name'];
                    }
                    
  
                }
                if (isset($areas['category_id'])) {
                    $division_category = $this->CategoryDivisionModel->where('id' , $areas['category_id'])->pluck('cat_division_name')->first();

                    $area_category_arr[$key]['category_name'] = isset($division_category)?$division_category:'';
                    $area_category_arr[$key]['category_id'] = $areas['category_id'];
                }

                $sales_manager_arr     = $areas['get_user_data'];
            }  
        }

        $arr_view_data['sales_manager_data'] = $sales_manager_arr;
        $arr_view_data['description']        = $sales_manager_details[0]['description'];
        $arr_view_data['area_details']       = $area_category_arr;
        $arr_view_data['page_title']         = 'Sales Manager';

        return view('front.front_sales_manager_details',$arr_view_data);
    }


    //Demo Func For IMage Zoom

    public function img_zoom_demo()
    {

        $product_arr = $this->ProductsModel->where('is_active','1')
                                            ->where('product_complete_status',4)    
                                            ->orderBy('created_at','DESC')
                                            ->with('productDetails')
                                            ->get()
                                            ->toArray();

        $arr_view_data['product_arr']   = $product_arr;                                    

        return view('front.img_zoom_demo',$arr_view_data);
    }

    /*get all vendors of that area and category*/
    public function get_all_vendors($area_id=false,$category_id=false)
    {
       $area_id     = base64_decode($area_id);
       $category_id = base64_decode($category_id);


       $state_details_arr = $state_id_arr = [];

       //get all rep from area and category
        
       $representative_table            = $this->RepresentativeModel->getTable();
       $prefix_representative_table     = DB::getTablePrefix().$representative_table;
       
       $vendor_rep_mapping_table        = $this->VendorRepresentativeMappingModel->getTable();
       $prefix_vendor_rep_mapping_table = DB::getTablePrefix().$vendor_rep_mapping_table;


       $vendor_table                    = $this->MakerModel->getTable();
       $prefix_vendor_table             = DB::getTablePrefix().$vendor_table;

       $user_table                      = $this->UserModel->getTable();
       $prefix_user_table               = DB::getTablePrefix().$user_table;

       $vendor_store_image_table        = $this->ShopImagesModel->getTable();
       $prefix_vendor_store_image_table = DB::getTablePrefix().$vendor_store_image_table;


       $vendor_rep_details_arr = DB::table($representative_table)
                                    ->select(DB::raw($prefix_representative_table.".*,".  
                                                 
                                            $prefix_vendor_table.".brand_name, ".
                                            $prefix_vendor_table.".company_name, ".
                                            $prefix_vendor_table.".user_id as uid,".

                                            $prefix_vendor_store_image_table.".maker_id,".
                                            $prefix_vendor_store_image_table.".store_profile_image,".

                                            $prefix_user_table.'.id as uid'

                                    ))

                                 
                                    ->join($prefix_vendor_rep_mapping_table,$prefix_vendor_rep_mapping_table.'.representative_id','=',$representative_table.'.user_id')

                                    ->join($prefix_vendor_table,$prefix_vendor_table.'.user_id','=',$prefix_vendor_rep_mapping_table.'.vendor_id')

                                    ->join($prefix_vendor_store_image_table,$prefix_vendor_store_image_table.'.maker_id','=',$prefix_vendor_rep_mapping_table.'.vendor_id')

                                    ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_vendor_rep_mapping_table.'.vendor_id')

                                    ->where($prefix_user_table.'.status',1)
                                    ->where($prefix_user_table.'.is_approved',1);

                                    if(isset($category_id) && $category_id!='')
                                    { 
                                        $vendor_rep_details_arr =  $vendor_rep_details_arr->where($prefix_representative_table.'.category_id', 'like', '%'.$category_id.'%');

                                                                     
                                    }

        $vendor_rep_details_arr = $vendor_rep_details_arr->groupBy('maker_id')
                                  ->where($prefix_representative_table.'.area_id',$area_id);

        $vendor_rep_details_arr = $vendor_rep_details_arr->paginate(8);
 
        if(isset($vendor_rep_details_arr))
        {
            $arr_vendor_pagination   = clone $vendor_rep_details_arr;
            $vendor_rep_details_arr  = $vendor_rep_details_arr->toArray();
        }                            

       
    
    if(isset($area_id) && $area_id!='')
    {
        $area_obj = $this->RepAreaModel->where('id',$area_id)->first();

        if(isset($area_obj))
        {
            $area_arr = $area_obj->toArray(); 
        }

        $state_id_arr      = json_decode($area_arr['state_id']);

        //$area_type = $this->RepAreaModel->where('id',$area_id)->pluck('area_type')->first();
        $area_type = get_area_type($area_id);
           
        $state_details_arr = $this->StateModel->whereIn('id',$state_id_arr)->get()->toArray(); 

        $this->arr_view_data['area_type']       = $area_type;
   
 
    }
      
    /*get category name and area name from there id*/
    if($area_id!="" && $category_id!="")
    {
        //$category_name = get_catrgory_name($category_id);
        $category_name = $this->HelperService->get_cat_division($category_id);
    
        $area_name     = get_area_name($area_id);

       
        $this->arr_view_data['category_name']    = $category_name;
        $this->arr_view_data['area_name']        = $area_name;
        
    }   


    $this->arr_view_data['vendor_rep_details_arr']       = $vendor_rep_details_arr;
    $this->arr_view_data['state_details_arr']            = $state_details_arr;
    $this->arr_view_data['arr_vendor_pagination']        = $arr_vendor_pagination;
    $this->arr_view_data['category_id']                  = $category_id;
    $this->arr_view_data['area_id']                      = $area_id;
    $this->arr_view_data['category_name']                = isset($category_name)?$category_name:'';
   
    $this->arr_view_data['page_title']                   = 'Find Your Rep';
    // dd($this->arr_view_data);
 
    return view('front.vendor_list',$this->arr_view_data);
       
    }

   
    public function search_vendor($area_id=false,$category_id=false,$category_div_id=false)
    {   
       
        $get_product_arr = $state_id_arr = $state_details_arr = [];
        $category_div_name = '';

        $category_id     = base64_decode($category_id);
        $area_id         = base64_decode($area_id);
        $category_div_id = base64_decode($category_div_id);

     
        $product_table                  = $this->ProductsModel->getTable();
        $prefix_product_table           = DB::getTablePrefix().$product_table;

        $vendor_table                   = $this->MakerModel->getTable();
        $prefix_vendor_table            = DB::getTablePrefix().$vendor_table;

        $shop_store_images_table        = $this->ShopImagesModel->getTable();
        $prefix_shop_store_images_table = DB::getTablePrefix().$shop_store_images_table;

        $user_table                     = $this->UserModel->getTable();
        $prefix_user_table              = DB::getTablePrefix().$user_table;


        $get_all_vendors   = DB::table($product_table)
                                 ->select(DB::raw($prefix_product_table.'.id as pid,'.
                                          $prefix_product_table.'.user_id as product_user_id,'.

                                          $prefix_vendor_table.'.user_id as uid,'.
                                          $prefix_vendor_table.'.company_name,'.
                                          $prefix_vendor_table.'.brand_name,'.

                                          $prefix_shop_store_images_table.'.maker_id,'.
                                          $prefix_shop_store_images_table.'.store_cover_image,'.
                                          $prefix_shop_store_images_table.'.store_profile_image,'.

                                          $prefix_user_table.'.id as usr_id'

                                ))

                                ->join($prefix_vendor_table,$prefix_vendor_table.'.user_id','=',$prefix_product_table.'.user_id')

                                ->join($prefix_shop_store_images_table,$prefix_shop_store_images_table.'.maker_id','=',$prefix_vendor_table.'.user_id')

                                ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_product_table.'.user_id')

                                ->where($prefix_user_table.'.status',1)
                                ->where($prefix_user_table.'.is_approved',1)
                                ->where($prefix_product_table.'.is_active',1)
                                ->where('category_id',$category_id)
                                ->groupBy($prefix_product_table.'.user_id')
                                ->paginate(8);



        if(isset($get_product_arr))
        {
            $arr_vendor_pagination   = clone $get_all_vendors;
            $vendor_rep_details_arr  = $get_all_vendors->toArray();
           
        }                            
                                   
          
        if(isset($area_id) && $area_id!='')
        {  
            $area_obj = $this->RepAreaModel->where('id',$area_id)->first();
         

            if(isset($area_obj))
            {
                $area_arr = $area_obj->toArray(); 
            }
            if (isset($area_arr))
            {
                $state_id_arr      = json_decode($area_arr['state_id']);

                $state_details_arr = $this->StateModel->whereIn('id',$state_id_arr)->get()->toArray();  
                
            }

            $area_type = get_area_type($area_id);
              
        }

        if(isset($category_div_id) && $category_div_id!=false)
        {
           $category_div_name = $this->HelperService->get_cat_division($category_div_id);
        }
       

        $this->arr_view_data['vendor_rep_details_arr']  = $vendor_rep_details_arr;
        $this->arr_view_data['state_details_arr']       = $state_details_arr;
        $this->arr_view_data['arr_vendor_pagination']   = $arr_vendor_pagination;
        $this->arr_view_data['category_id']             = $category_div_id;
        $this->arr_view_data['area_id']                 = $area_id;
        $this->arr_view_data['page_title']              = 'Find Your Rep';
        $this->arr_view_data['category_div_name']       = $category_div_name;
        $this->arr_view_data['area_type']               = $area_type;
        $this->arr_view_data['product_category_id']     = $category_id;


        return view('front.vendor_list',$this->arr_view_data);
   
    }

 

    public function checkVendorStatus(Request $request)
    {
          $response = [];
          $vendor_id    = base64_decode($request->input('vendor_id'));
          $user_details = $this->UserModel->where('id',$vendor_id)->select('status','is_approved')->first();

            if(isset($user_details));
            {
                if(isset($user_details) && $user_details!=null)
                {   
                    $user_details = $user_details->toArray();
                    if($user_details['status']==0 || $user_details['is_approved']==0)
                    {
                        $response['status']      = 'error';
                        $response['description'] = 'Sorry this vendor is blocked.';
                    }

                    else
                    {
                        $response['status']     = 'success';                    
                    }
                }
            }
           /* else
            {
                $response['status']      = 'error';
                $response['description'] = 'Sorry something went wrong, please try again.';
            }*/
      return json_encode($response);
    }

   
    public function searchRepresentative(Request $request)
    {   
        $form_data = $request->all();
      
        $area_id           = isset($form_data['area'])?$form_data['area']:'';
       /* $category_division = isset($form_data['category_division'])?$form_data['category_division']:'';*/
        $zip_code        = isset($form_data['zip_code'])?$form_data['zip_code']:'';
        
        $vendor_id       = isset($form_data['vendor'])?$form_data['vendor']:'';

        $rep_name        = isset($form_data['rep_name'])?trim($form_data['rep_name']):'';


        $state_details_arr = $state_id_arr = $category_details_arr = [];

        $area_table                  =  $this->RepAreaModel->getTable();
       
        $prefix_area_table           = DB::getTablePrefix().$area_table;

        $representative_table        = $this->RepresentativeModel->getTable();

        $prefix_representative_table = DB::getTablePrefix().$representative_table;

        $user_table                  = $this->UserModel->getTable();

        $prefix_user_table           = DB::getTablePrefix().$user_table;

        $state_table                 = $this->StateModel->getTable();

        $prefix_state_table          = DB::getTablePrefix().$state_table;
    
        $role_table                  = $this->RoleModel->getTable();

        $prefix_role_table           = DB::getTablePrefix().$role_table;

        $role_users_table            = $this->RoleUsersModel->getTable();

        $prefix_role_users_table     = DB::getTablePrefix().$role_users_table;

        $sales_manager_table         = $this->SalesManagerModel->getTable();
        $prefix_sales_manager_table  = DB::getTablePrefix().$sales_manager_table;

        $rep_vendor_map_table        = $this->VendorRepresentativeMappingModel->getTable();
        $prefix_rep_vendor_map_table  = DB::getTablePrefix().$rep_vendor_map_table;

        
        $rep_details = DB::table($representative_table)
                                ->select(DB::raw($prefix_representative_table.".*,".  
                                                
                                                $prefix_state_table.".name,".
                                                $prefix_role_table.".name,".
                                                $prefix_user_table.".profile_image as profile_image, ".
                                                $prefix_user_table.'.id as uid,'.

                                                "CONCAT(".$prefix_user_table.".first_name,' ',".$prefix_user_table.".last_name) as user_name"

                                            ))

                                ->join($prefix_user_table,$prefix_user_table.'.id','=',$prefix_representative_table.'.user_id')

                                ->leftjoin($prefix_role_users_table,$prefix_role_users_table.'.user_id','=',$prefix_representative_table.'.user_id')

                                ->leftjoin($prefix_role_table,$prefix_role_table.'.id','=',$prefix_role_users_table.'.role_id')

                                ->leftjoin($prefix_area_table,$prefix_area_table.'.id','=',$prefix_representative_table.'.area_id')

                                ->leftjoin($prefix_state_table,$prefix_state_table.'.id','=',$prefix_area_table.'.state_id')

                                ->leftjoin($prefix_rep_vendor_map_table,$prefix_rep_vendor_map_table.'.representative_id','=',$prefix_representative_table.'.user_id')

                                ->where($prefix_user_table.'.status',1)
                                ->where($prefix_user_table.'.is_approved',1)
                                ->groupBy($prefix_rep_vendor_map_table.'.representative_id');

                                if(isset($area_id) && $area_id!='')
                                { 
                                    $rep_details =$rep_details->where($prefix_representative_table.'.area_id',$area_id);
                                }
                                
                               /* if(isset($category_division) && $category_division!='')
                                {
                                    $rep_details =$rep_details->where($prefix_representative_table.'.category_id', 'LIKE', '%'.$category_division.'%');
                                }
*/
                                if(isset($zip_code) && $zip_code!='')
                                {
                                   $rep_details =$rep_details->where($prefix_user_table.'.post_code',$zip_code);
 
                                }

                                if(isset($vendor_id) && $vendor_id!='')
                                { 
                                    $rep_details =$rep_details->where($prefix_rep_vendor_map_table.'.vendor_id',$vendor_id);
                                }
          

                                if(isset($rep_name) && $rep_name!='')
                                {
                                    $rep_details =$rep_details->where(function($query) use($prefix_user_table,$rep_name,$prefix_rep_vendor_map_table)
                                    {
                                        return $query->orwhere($prefix_user_table.'.first_name','LIKE', '%'.$rep_name.'%')
                                                    ->orWhere($prefix_user_table.'.last_name','LIKE', '%'.$rep_name.'%')

                                                    ->orWhere(DB::raw("CONCAT(".$prefix_user_table.".first_name,' ',".$prefix_user_table.".last_name)"), 'LIKE', "%".$rep_name."%");
                                                     
                                    });         
                                }
  
                               
 
        $rep_details = $rep_details->paginate(12);

        if(isset($rep_details))
        {
            $arr_rep_pagination   = clone $rep_details;
            $rep_details_arr      = $rep_details->toArray();
        }


        /* get all area details*/
        $area_details = [];
        $area_details = $this->RepAreaModel->where('status',1)
                                           ->orderBy('area_name')
                                           ->get()
                                           ->toArray();  

        /*get all category division*/
       
        $area_details_obj = $this->RepAreaModel->where('id',$area_id)->first();

        if(isset($area_details_obj))
        {
            $area_details_arr = $area_details_obj->toArray(); 
        }

        $category_id_arr = isset($area_details_arr['category_id'])?json_decode($area_details_arr['category_id']):[];
     
        if(isset($category_id_arr) && count($category_id_arr)>0)
        {
            $category_details_arr = $this->CategoryDivisionModel->whereIn('id',$category_id_arr)->get()->toArray();
        }

        /*get all active retailers*/

        $vendor_arr = $country_arr = [];


        $user_table        =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;

        $role_table        =  $this->RoleModel->getTable();
        $prefix_role_table = DB::getTablePrefix().$role_table;

        $role_user_table        =  $this->RoleUsersModel->getTable();
        $prefix_role_user_table = DB::getTablePrefix().$role_user_table;

        $maker_table = $this->MakerModel->getTable();
        $prefix_maker_table = DB::getTablePrefix().$maker_table;
        
        $obj_user = DB::table($user_table)
                        ->select(DB::raw($prefix_user_table.".id as id,".
                                    $prefix_user_table.".email as email, ".
                                    $prefix_user_table.".status, ".
                                    $prefix_user_table.".is_approved, ".
                                    $prefix_user_table.".wallet_address as wallet_address,".
                                    $prefix_user_table.".status_net_30,".
                                    $prefix_user_table.".contact_no as contact_no, ".
                                    $role_table.".slug as slug, ".
                                    $role_table.".name as name, ".

                                    $prefix_maker_table.".company_name,".
                                     
                                    "CONCAT(".$prefix_user_table.".first_name,' ',"
                                             .$prefix_user_table.".last_name) as user_name"
                                    ))

                        ->leftJoin($role_user_table,$role_user_table.'.user_id','=',$user_table.'.id')
                        ->leftJoin($role_table,$role_user_table.'.role_id','=',$role_table.'.id')

                         ->leftJoin($prefix_maker_table,$prefix_maker_table.'.user_id','=',$prefix_user_table.'.id')
                       
                        ->where($role_table.'.slug','=','maker')
                        ->where($user_table.'.id','!=',1)
                        ->where($user_table.'.status','=',1)
                        ->where($user_table.'.is_approved','=',1)
                        ->whereNull($user_table.'.deleted_at')
                       // ->orderBy($user_table.'.created_at','DESC');
                        ->orderBy($prefix_maker_table.".company_name");


        if(isset($obj_user))
        {
           $vendor_arr = $obj_user->get()->toArray();                                   
        }

        $this->arr_view_data['vendor_arr']                  = $vendor_arr;
        $this->arr_view_data['category_division_arr']       = $category_details_arr;
        $this->arr_view_data['area_id']                     = $area_id;
        $this->arr_view_data['vendor_id']                   = $vendor_id;
        //$this->arr_view_data['category_division']           = $category_division;
        $this->arr_view_data['representative_name']         = $rep_name;

      
        $this->arr_view_data['zip_code']                    = $zip_code;
        $this->arr_view_data['area_arr']                    = $area_details; 
        $this->arr_view_data['rep_details_arr']             = $rep_details_arr;
        $this->arr_view_data['representative_pagination']   = $arr_rep_pagination;
        $this->arr_view_data['sales_manager_arr']           = [];
        $this->arr_view_data['page_title']                  = 'Find Your Rep';

        return view('front.find_your_rep',$this->arr_view_data);
    }


   /* public function getCategoryDivision(Request $request)
    { 
        $category_details_arr = $area_details_arr = [];
        $area_id = $request->get('area_id');
   
        $area_details_obj = $this->RepAreaModel->where('id',$area_id)->first();

        if(isset($area_details_obj))
        {
            $area_details_arr = $area_details_obj->toArray(); 
        }
        $category_id_arr = isset($area_details_arr['category_id'])?json_decode($area_details_arr['category_id']):[];
     
        if(isset($category_id_arr) && count($category_id_arr)>0)
        {
            $category_details_arr = $this->CategoryDivisionModel->whereIn('id',$category_id_arr)->get()->toArray();
        }

        return response()->json($category_details_arr);

    }*/

    public function is_login_update()
    { 
        $user = Sentinel::check();
        $user_id = 0;
        if($user)
        {
            $user_id = $user->id;
        }
        $result = $this->UserModel->where('id',$user_id)->update(['is_login'=>1]);

        if($result)
        {
            $response['status']        = 'success';
            $response['description']   = 'is login status has been updated.';
            $response['error_message'] = 'Please fill all the required profile fields.';
            

            return response()->json($response);
        }
        else
        { 
            $response['error']        = 'error';
            $response['description']  = 'Something went wrong,please try again.';
            return response()->json($response);

        }
    }
    public function daily_popup() {

        $json    = file_get_contents('http://ip-api.com/json/'.$_SERVER['REMOTE_ADDR']);
        $obj     = json_decode($json);
       
        $country = isset($obj->country) ? $obj->country : 0;
       
        if ($country == "United States" || $country == "India") {
        
            //setcookie("last_visit_date", "", time()-3600); //  unset Cookies 

            if(isset($_COOKIE['last_visit_date']) && $_COOKIE['last_visit_date'] != "") {

                $date1 = $_COOKIE['last_visit_date'];   // last visited date 
                $date2 = date("Ymd");                   // Current Date

                if ($date1 === $date2) {

                    if (!isset($_COOKIE['is_visited'])) {           // If Alreasy Not Visited !

                           $response['status']  = true;
                           $response['country'] = $country;
                           return response()->json($response);
                    }
                    else {
                        $response['status']  = false;
                        $response['country'] = $country;
                        return response()->json($response);
                    }
                }
                else {

                    setcookie('last_visit_date', date("Ymd"));
                    setcookie('is_visited', '1');

                    $response['status']  = true;
                    $response['country'] = $country;
                    return response()->json($response);
                }
            }
            else {

                setcookie('last_visit_date', date("Ymd"));
                setcookie('is_visited', '1');

                $response['status']  = true;
                $response['country'] = $country;
                return response()->json($response);
            }
        }
        else {

            $response['status']  = "error";
            $response['country'] = $country;
            return response()->json($response);
        }
    }
}

