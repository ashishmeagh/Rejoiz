<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\PromotionsTypeModel;
use App\Common\Traits\MultiActionTrait;
use App\Models\PromotionsModel;
use App\Models\PromotionsOffersModel;
use App\Models\PromoCodeModel;
use Validator;
use DB;
use Datatables;
use Sentinel;
use Flash;
use Helper;
use Image;
use Storage;
use DateTime;


/*Author : priyanka kedare
  Date : 31/10/2019
*/
class PromotionsController extends Controller
{
	use MultiActionTrait;

    public function __construct(PromotionsTypeModel   $PromotionsTypeModel,
                                PromotionsModel       $PromotionsModel,
                                PromotionsOffersModel $PromotionsOffersModel,
                                PromoCodeModel  $PromoCodeModel   
                               )
    {
       $this->arr_view_data          = [];
       $this->PromotionsTypeModel    = $PromotionsTypeModel;
       $this->PromotionsOffersModel  = $PromotionsOffersModel;
       $this->PromotionsModel        = $PromotionsModel;
       $this->BaseModel              = $this->PromotionsModel;
       $this->PromoCodeModel         = $PromoCodeModel;
       $this->module_title           = 'Promotions';
       $this->module_view_folder     = 'maker.promotions';
       $this->maker_panel_slug       = config('app.project.maker_panel_slug');
       $this->module_url_path        = url($this->maker_panel_slug.'/promotions');

    }

   
    public function index()
    {
    	$this->arr_view_data['module_title']     = 'Manage '.str_plural($this->module_title);
        $this->arr_view_data['maker_panel_slug'] = $this->maker_panel_slug;
    	$this->arr_view_data['page_title']       = 'Manage '.str_plural($this->module_title);
    	$this->arr_view_data['module_url_path']  = $this->module_url_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }



    public function get_maker_promotions(Request $request)
    {               
        $loggedInUserId = 0;

        $user = Sentinel::check();

        $arr_search_column = $request->input('column_filter');

        if($user)
        {
            $loggedInUserId = $user->id;
        }

        $promotions_tbl              = $this->BaseModel->getTable();        
        $prefixed_promotions_tbl     = DB::getTablePrefix().$this->BaseModel->getTable();

        $promotions_type_tbl         = $this->PromotionsTypeModel->getTable();
        $prefix_promotions_type_tbl  = DB::getTablePrefix().$this->PromotionsTypeModel->getTable();


        $promo_code_tbl              = $this->PromoCodeModel->getTable();        
        $prefix_promo_code_tbl       = DB::getTablePrefix().$this->PromoCodeModel->getTable();
        
        $obj_promotions = DB::table($promotions_tbl)
                              ->select(DB::raw($prefixed_promotions_tbl.".id,".  
                             $prefixed_promotions_tbl.'.maker_id,'.
                             $prefixed_promotions_tbl.".title,".
                             $prefixed_promotions_tbl.'.from_date,'.
                             $prefixed_promotions_tbl.".to_date,".   
                             $prefixed_promotions_tbl.".is_active,".
                             $prefixed_promotions_tbl.".promo_code,".

                             $prefix_promo_code_tbl.".id as promo_id,".
                             $prefix_promo_code_tbl.".promo_code_name"
                             
                         ))

                        ->leftjoin($prefix_promo_code_tbl,$prefix_promo_code_tbl.'.id','=',$prefixed_promotions_tbl.'.promo_code')      
                        
                        ->where($prefixed_promotions_tbl.'.maker_id',$loggedInUserId)
                        ->orderBy($prefixed_promotions_tbl.'.created_at','DESC');

     

        /* ---------------- Filtering Logic ----------------------------------*/                           
		 
		    if(isset($arr_search_column['q_title']) && $arr_search_column['q_title']!="")
		    {
		      $search_term    = $arr_search_column['q_title'];
		      $obj_promotions = $obj_promotions->where($prefixed_promotions_tbl.'.title','LIKE', '%'.$search_term.'%');
		    }

		    if(isset($arr_search_column['q_from_date']) && $arr_search_column['q_from_date']!="")
		    {
		       $search_term            = $arr_search_column['q_from_date'];
          
	           //$search_term_from_date  = date('Y-m-d',strtotime($search_term));

               $date                  = DateTime::createFromFormat('m-d-Y',$search_term);
               $date                  = $date->format('Y-m-d');
	        
			   $obj_promotions = $obj_promotions->where($prefixed_promotions_tbl.'.from_date','LIKE', '%'.$date.'%');

		    }

		    if(isset($arr_search_column['q_to_date']) && $arr_search_column['q_to_date']!="")
		    {
		       $search_term    = $arr_search_column['q_to_date'];

		      // $search_term_to_date  = date('Y-m-d',strtotime($search_term));
               $date           = DateTime::createFromFormat('m-d-Y',$search_term);
               $date           = $date->format('Y-m-d');
	    
		       $obj_promotions = $obj_promotions->where($prefixed_promotions_tbl.'.to_date','LIKE', '%'.$date.'%');
		    }


            if(isset($arr_search_column['q_promo_code']) && $arr_search_column['q_promo_code']!="")
            {
               $search_term    = $arr_search_column['q_promo_code'];
               $obj_promotions = $obj_promotions->where($prefix_promo_code_tbl.'.promo_code_name','LIKE', '%'.$search_term.'%');

            }

		    if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
		    {
		      $search_term    = $arr_search_column['q_status'];

		      $obj_promotions = $obj_promotions->where($prefixed_promotions_tbl.'.is_active','LIKE', '%'.$search_term.'%');
		    }

        
            $current_context = $this;

            $json_result  = Datatables::of($obj_promotions);

            /* Modifying Columns */
            $json_result =  $json_result->editColumn('id',function($data) use ($current_context)
                        {
                            if(isset($data->id) && $data->id != '')
                            {
                               return  $id = base64_encode($data->id);
                            }
                            
                        })
                        
                        ->editColumn('title',function($data)use ($current_context) 
                        {   
                           return $title = isset($data->title)?ucwords($data->title):'N/A';
                        })

                        ->editColumn('from_date',function($data)use ($current_context) 
                        {   
                            return $from_date = isset($data->from_date)?us_date_format($data->from_date):'N/A';
                        })

                        ->editColumn('to_date',function($data)use ($current_context) 
                        {   
                            return $to_date = isset($data->to_date)?us_date_format($data->to_date):'N/A';
                        })


                        ->editColumn('promo_code',function($data)use ($current_context) 
                        {   
                            return $promo_code = isset($data->promo_code_name)?$data->promo_code_name:'N/A';
                        })


                        ->editColumn('is_active',function($data)use ($current_context)
                        {
                          return $status = $data->is_active;
                        })
                       
                        ->make(true);

        $build_result = $json_result->getData();
        
        return response()->json($build_result);


        return $obj_promotions;

    }


    public function create()
    {
        /*get loggedin user*/
        $user_id = 0;
        $user = Sentinel::check();

        if($user)
        {
          $user_id = $user->id;
        }
       
        $promotions_type_details =$promo_code_arr = [];
    	$promotions_type_details = $this->PromotionsTypeModel->where('is_active','1')->get()->toArray();

        //get promo code list

        $promo_code_arr = $this->PromoCodeModel->where('is_active',1)->where('status','Not Used')->where('vendor_id',$user_id)->get()->toArray();

    	$this->arr_view_data['module_title']             = $this->module_title;
    	$this->arr_view_data['promotions_type_arr']      = $promotions_type_details;
        $this->arr_view_data['promo_code_arr']           = $promo_code_arr;
    	$this->arr_view_data['page_title']               = 'Add '.$this->module_title;
    	$this->arr_view_data['module_url_path']          = $this->module_url_path;

        return view($this->module_view_folder.'.add',$this->arr_view_data);
    }


    public function edit($enc_id)
    {
        /*get loggedin user*/
        $user_id = 0;
        $user = Sentinel::check();

        if($user)
        {
          $user_id = $user->id;
        }


    	$promotion_id = base64_decode($enc_id);	

        $promotions_details = [];

    	$obj_promotions_details = $this->PromotionsModel->with(['get_promotions_offer_details.get_prmotion_type'])
                                        ->where('id',$promotion_id)->first();

       //get promo code list

        $promo_code_arr = $this->PromoCodeModel->where('is_active',1)->where('status','Not Used')->where('vendor_id',$user_id)->get()->toArray();
                                 


    	if($obj_promotions_details)
    	{
           $promotions_details = $obj_promotions_details->toArray();
    	}

        $from_date = $to_date = "";

        $from_date = isset($promotions_details['from_date'])?$promotions_details['from_date']:"";
        $to_date   = isset($promotions_details['to_date'])?$promotions_details['to_date']:"";

        if($from_date!="" && $to_date!="")
        {
            $from_date  = DateTime::createFromFormat('Y-m-d',$from_date);
            $to_date    = DateTime::createFromFormat('Y-m-d',$to_date);

            $from_date = $from_date->format('m-d-yy');
            $to_date   = $to_date->format('m-d-yy');
        }

    	$promotions_type_details = [];
    	$promotions_type_details = $this->PromotionsTypeModel->where('is_active','1')->get()->toArray();

    	$this->arr_view_data['module_title']             = $this->module_title;
    	$this->arr_view_data['promotions_arr']           = $promotions_details;
        $this->arr_view_data['promo_code_arr']           = $promo_code_arr;
    	$this->arr_view_data['promotions_type_arr']      = $promotions_type_details;
    	$this->arr_view_data['page_title']               = 'Edit '.$this->module_title;
    	$this->arr_view_data['module_url_path']          = $this->module_url_path;
        $this->arr_view_data['from_date']                = $from_date;
        $this->arr_view_data['to_date']                  = $to_date;

        return view($this->module_view_folder.'.edit',$this->arr_view_data);
    }


    public function store(Request $request)
    {

    	$form_data = $request->all();
      
        $user      = \Sentinel::getUser();

    	if($user)
    	{
    		$user_id = $user->id;
    	}

          
        $arr_rules = [
                        'title'         => 'required',
                        'from_date'     => 'required',
                        'to_date'       => 'required',
                        'promo_code'    => 'required',
                        //'description'   => 'required',
                        'promotion_type' => 'required'

                        ];

        $validator = Validator::make($request->all(),$arr_rules);
        $response  = [];

        if($validator->fails())
        {   
            $response['status']      = 'warning';
            $response['description'] = 'Form validations fails, please check all fields.';

            return response()->json($response);
        }

        $from_date = $to_date = '';    
        //dd($form_data['from_date'],$form_data['to_date']);    
        $from_date  = DateTime::createFromFormat('Y-m-d',$form_data['from_date']);
        $to_date    = DateTime::createFromFormat('Y-m-d',$form_data['to_date']);


        $from_date = $from_date->format('Y-m-d');
        $to_date   = $to_date->format('Y-m-d');
        

        //$from_date  = date('Y-m-d', strtotime(str_replace('/', '-', '18-07-2013')));
        //$to_date    = date('Y-m-d',strtotime($form_data['to_date']));

        if($to_date  < $from_date)
        {
            $response['status']      = 'warning';
            $response['description'] = 'To date should be greater than from date.';

            return response()->json($response);   
        }

    	$promotion_id = isset($request->enc_id)?base64_decode($request->enc_id):false;

    	$title        = isset($form_data['title'])?$form_data['title']:false;

        $title        = strtolower($title);        

        $is_exists    = $this->PromotionsModel->where('title',$title)->where('maker_id',$user_id);

        if($promotion_id)
        {
            $is_exists = $is_exists->where('id','<>',$promotion_id)->count();
        }
        else
        {
            $is_exists = $is_exists->count();
        }

        if($is_exists)
        {
            $response['status']      = 'error';
            $response['description'] = 'Promotion is already exists.';
            return response()->json($response);
        }



        /*---------check promo code duplication--------*/
        if(isset($request->promo_code)){

            $promo_code_is_exists = $this->PromotionsModel->where('promo_code',$request->promo_code);



            if($promo_code_is_exists)
            {
                $promo_code_is_exists = $promo_code_is_exists->where('id','<>',$promotion_id)
                                                             ->count();
                
            }
            else
            {
                $promo_code_is_exists = $promo_code_is_exists->count();
            }
            if($promo_code_is_exists)
            {
                $response['status']      = 'error';
                $response['description'] = 'Promo code is already exists.';
                return response()->json($response);
            }

        }
        

        /*---------------------------------------------*/



      
        // $from_date  = us_date_format($form_data['from_date']);
        // $to_date    = us_date_format($form_data['to_date']);

      
    	$promotions = $this->PromotionsModel->firstOrNew(['id' =>$promotion_id]);

    	$promotions->maker_id    = $user_id or '';
        $promotions->title       = isset($title)?trim($title):'';
    	$promotions->from_date   = $from_date;
    	$promotions->to_date     = $to_date;
        $promotions->promo_code  = isset($request->promo_code)?trim($request->promo_code):'';
        $promotions->description = isset($request->description)?trim($request->description):'';
      
        
    	if(isset($form_data['status']) && $form_data['status']!='')
    	{ 
          $promotions->is_active = '1';
    	}
    	else
    	{
            $promotions->is_active = '0';
    	}

    	$promotions_details = $promotions->save();


    	if($promotions_details)
    	{

            /*insert into promotions_offer table*/
            $inserted_arr = [];

            if(isset($form_data['promotion_type']) && count($form_data['promotion_type'])>0)
            {
                foreach($form_data['promotion_type'] as $key => $promotion_type)
                {
                    $id = isset($form_data['enc_promotion_offer_id'][$key])?$form_data['enc_promotion_offer_id'][$key]:0;

                    $inserted_arr['promotion_type_id'] = $promotion_type;
                    $inserted_arr['promotion_id']      = $promotions->id;
                    $inserted_arr['minimum_ammount']   = $form_data['min_ammount'][$key];
                    $inserted_arr['discount']          = $form_data['discount'][$key];

                    $offer = $this->PromotionsOffersModel->firstOrNew(['id' =>$id]);

                    $offer->promotion_id      = $promotions->id;
                    $offer->promotion_type_id = $promotion_type;
                    $offer->minimum_ammount   = $form_data['min_ammount'][$key];
                    $offer->discount          = $form_data['discount'][$key];

                    $offer_results = $offer->save();

                }
            }

           
    		$response['status']      = 'success';
    		$response['description'] = 'Promotion has been saved.';

    		return response()->json($response);
    	}
    	else
    	{
    		$response['status']      = 'error';
    		$response['description'] = 'Something went wrong, please try again.';
    		return response()->json($response);
    	}


    }

    
    public function change_status(Request $request)
    {  
      
        $id             = isset($request->promotions_id)?base64_decode($request->promotions_id):false;
        $status         = isset($request->status)?$request->status:false;

        if($status == 'activate')
        {
            $is_active = '1';
        }
        else if($status == 'deactivate')
        {   
           $is_active = '0';
        }

        $data['is_active'] = $is_active;

      
        $update = $this->BaseModel->where('id',$id)->update($data);


        if($update)
        {
            $response['status']      = 'success';
            $response['description'] = 'Status has been changed.';
            return response()->json($response);
        }
        else
        {
            $response['status']      = 'error';
            $response['description'] = 'Something went wrong, please try again.';
            return response()->json($response);
        }

    }


    public function delete_row(Request $request)
    {  
        $promotion_offer_id = base64_decode($request->input('id'));

        $result = $this->PromotionsOffersModel->where('id',$promotion_offer_id)->delete();

        if($result)
        {
          $response['status']      = 'success';
          $response['description'] = 'promotion offer has been deleted.';
          return response()->json($response);
        }
        else
        {
            
          $response['status']      = 'error';
          $response['description'] = 'Error occurred while deleting promotion offer.';
          return response()->json($response);

        }

    }


    public function view($enc_id)
    {
       $promotions_details = [];
       $id = base64_decode($enc_id);
       

       $obj_promotions_details = $this->PromotionsModel->with(['get_promotions_offer_details.get_prmotion_type','get_promo_code_details'])
                                        ->where('id',$id)->first();

        if($obj_promotions_details)
        {
           $promotions_details = $obj_promotions_details->toArray();
        }
     
        $this->arr_view_data['promotion_arr']      = $promotions_details; 
        $this->arr_view_data['page_title']         = 'View '.$this->module_title;
        $this->arr_view_data['module_url_path']    = $this->module_url_path;
        $this->arr_view_data['module_title']       = $this->module_title;

        //dd($promotions_details);
        return view($this->module_view_folder.'.view',$this->arr_view_data);

    }


    public function delete($enc_id = FALSE)
    {
        if(!$enc_id)
        {
            return redirect()->back();
        }
        if($this->perform_delete(base64_decode($enc_id)))
        {   
            Flash::success($this->module_title.' has been deleted.');
        }
        else
        {
            Flash::error('Problem occurred while '.str_plural($this->module_title).' deletion.');
        }

        return redirect()->back();

    }

    public function perform_delete($id)
    {   
 
        if($this->BaseModel->getTable()=="products")
        {
          $is_elastic_exist = $this->BaseModel::search()->match('_id',$id)->get()->hits()->count();
          if($is_elastic_exist!=0)
          {
            $elastic_delete = $this->BaseModel::where('id',$id)->first()->document()->delete();
          }
        }
        
        $delete = $this->BaseModel->where('id',$id)->delete();

            
        if($delete)
        {  $result = $this->PromotionsOffersModel->where('promotion_id',$id)->delete();
           return TRUE;
        }

        return FALSE;

    }

}
