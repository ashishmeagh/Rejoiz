<?php

namespace App\Http\Controllers\Api\Rejoiz\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\EmailSubscriptiponModel;


use App\Common\Services\Api\Rejoiz\Front\HomeService;
use App\Common\Services\Api\Rejoiz\Common\ResponseService;

use Validator;



class HomeController extends Controller
{
    public function __construct(HomeService $HomeService,
                                ResponseService $ResponseService,
                                EmailSubscriptiponModel $EmailSubscriptiponModel) { 

        $this->HomeService             = $HomeService;
        $this->ResponseService         = $ResponseService;
        $this->EmailSubscriptiponModel = $EmailSubscriptiponModel;
    }

    public function get_menus() {
          
      $arr_data        = $this->HomeService->get_menus();

        return $this->ResponseService->send($arr_data);  
    }

   public function get_slider_images() {
      
      $arr_data        = $this->HomeService->get_slider_images();

      return $this->ResponseService->send($arr_data);  
  }

   public function get_categories(Request $request) {

     $form_data       = $request->all();
     $page            = isset($form_data['page'])?$form_data['page']:'';  
     $per_page        = isset($form_data['per_page'])?$form_data['per_page']:''; 
     $arr_data        = $this->HomeService->get_categories($page,$per_page);

    return $this->ResponseService->send($arr_data);  
  }


  public function about_us() {
          
      $arr_data        = $this->HomeService->about_us();

      return $this->ResponseService->send($arr_data);  
  }


  public function subscribe(Request $request){

    $form_data = $request->all();
    $email     = isset($form_data['email'])?$form_data['email']:'';
   
    $arr_rules = [
                   'email'=>'required|email'
                 ];
   
    $validator = Validator::make($request->all(),$arr_rules);

    if($validator->fails())
    {
       $response['status']      = "failure";
       $response['message']     = "Provided email should not be blank or invalid.";
       $response['data']        = '';

       return $this->ResponseService->send($response);      
    }
   
    /* Check for email duplication */
    $is_duplicate =  $this->EmailSubscriptiponModel
                          ->where('email',$request->input('email'))
                          ->count();  
    
    if($is_duplicate > 0)
    {
        $response['status']    = "failure";
        $response['message']   = "You have already subscribed to our newsletter.";
        $response['data']      = '';

        return $this->ResponseService->send($response);
    }  

    $arr_data   = $this->HomeService->subscribe($email);  

    return $this->ResponseService->send($arr_data); 

  }

  public function faqs()
  {
    $arr_data  = $this->HomeService->faqs();

    return $this->ResponseService->send($arr_data); 
  }

  public function get_social_links()
  {
    $arr_data  = $this->HomeService->get_social_links();

    return $this->ResponseService->send($arr_data); 
  }

  public function special_offers()
  {
    $arr_data  = $this->HomeService->special_offers();

    return $this->ResponseService->send($arr_data); 
  }

  public function rep_center()
  {
    $arr_data  = $this->HomeService->rep_center();

    return $this->ResponseService->send($arr_data); 
  }


  //Get special offer details
  public function get_promotions(Request $request)
  {
     $form_data    = $request->all();
     $area_id      = isset($form_data['area_id'])?$form_data['area_id']:'';
     $category_id  = isset($form_data['category_id'])?$form_data['category_id']:'';

     $arr_data     = $this->HomeService->get_promotions($area_id,$category_id);

     return $this->ResponseService->send($arr_data);
  }


  //Get rep_center details
  public function find_rep(Request $request)
  { 
     $form_data    = $request->all();
     $area_id      = isset($form_data['area_id'])?$form_data['area_id']:'';
     $category_id  = isset($form_data['category_id'])?$form_data['category_id']:'';
     $per_page     = isset($form_data['per_page'])?$form_data['per_page']:'';

     $arr_data     = $this->HomeService->find_rep($area_id,$category_id,$per_page);

     return $this->ResponseService->send($arr_data);
  }


  public function get_area_wise_vendors(Request $request)
  { 
     $form_data    = $request->all();
     $area_id      = isset($form_data['area_id'])?$form_data['area_id']:'';
     $category_id  = isset($form_data['category_id'])?$form_data['category_id']:'';
     $per_page     = isset($form_data['per_page'])?$form_data['per_page']:'';

     $arr_data     = $this->HomeService->get_area_wise_vendors($area_id,$category_id,$per_page);

     return $this->ResponseService->send($arr_data);
  }

  public function sales_manager_details(Request $request)
  { 
     $form_data          = $request->all();
     $sales_manager_id   = isset($form_data['user_id'])?$form_data['user_id']:'';
     $arr_data           = $this->HomeService->sales_manager_details($sales_manager_id);

     return $this->ResponseService->send($arr_data);
  }

  public function rep_details(Request $request)
  { 
     $form_data          = $request->all();
     $rep_id             = isset($form_data['user_id'])?$form_data['user_id']:'';
     $per_page           = isset($form_data['per_page'])?$form_data['per_page']:8;
     $arr_data           = $this->HomeService->rep_details($rep_id,$per_page);

     return $this->ResponseService->send($arr_data);
  }

  public function add_to_favorite(Request $request)
  {
     $form_data          = $request->all();
     $arr_data           = $this->HomeService->add_to_favorite($form_data);

     return $this->ResponseService->send($arr_data);
  }

  public function remove_from_favorite(Request $request)
  {
     $form_data          = $request->all();
     $arr_data           = $this->HomeService->remove_from_favorite($form_data);

     return $this->ResponseService->send($arr_data);
  }

  public function my_favorite(Request $request)
  {
     $form_data          = $request->all();
     $user               = isset($form_data['auth_user'])?$form_data['auth_user']:'';
     $type               = isset($form_data['type'])?$form_data['type']:'';
     $user_id            = 0;
     if($user)
     {
        $user_id         = $user->id;
     }

     $arr_data           = $this->HomeService->my_favorite($user_id,$type);

     return $this->ResponseService->send($arr_data);
  }






}
