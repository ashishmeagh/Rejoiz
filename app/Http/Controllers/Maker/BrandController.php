<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Traits\MultiActionTrait;
use App\Models\BrandsModel;
use App\Models\ProductsModel;
use App\Common\Services\ElasticSearchService;
use App\Common\Services\GeneralService;

use Flash;
use Validator;
use Session;

class BrandController extends Controller
{  
   use MultiActionTrait;
   public function __construct(ElasticSearchService $ElasticSearchService,GeneralService $GeneralService)
    {
        $this->BrandsModel          = new BrandsModel();
    	$this->ProducsModel         = new ProductsModel();
        $this->ElasticSearchService = $ElasticSearchService;
        $this->GeneralService       = $GeneralService;
    	$this->arr_view_data        = [];
        $this->BaseModel            = $this->BrandsModel;
    	$this->module_title         = "Brand";
    	$this->module_view_folder   = 'maker.brand';
        $this->maker_panel_slug     = config('app.project.maker_panel_slug');
        $this->module_url_path      = url($this->maker_panel_slug.'/brand');
        $this->brand_image          = base_path().'/storage/app/';
    
    }

    public function index()
    {
    	$this->arr_view_data['module_title'] = 'Manage '.str_plural($this->module_title);
    	$this->arr_view_data['page_title']   = 'Manage '.str_plural($this->module_title);
    	$this->arr_view_data['module_url_path'] = $this->module_url_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function all_brands(Request $request)
    {

        $user = \Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }

    	$obj_brands = $this->BrandsModel->orderBy('id','DESC')->where('user_id',$loggedInUserId);

    	$column_filter = $request->input('column_filter');

    	if(isset($column_filter) && $column_filter['q_brand_name'] != '')
    	{  
       		$obj_brands->where('brand_name','LIKE', '%'.$column_filter['q_brand_name'].'%');
    	}

        if(isset($column_filter) && $column_filter['q_brand_status'] != '')
        {  
            $obj_brands->where('is_active','LIKE', '%'.$column_filter['q_brand_status'].'%');
        }

        $obj_brands = $obj_brands->get();
        $current_context = $this;
    	$json_result     = \Datatables::of($obj_brands);


    	$json_result = $json_result->editColumn('enc_id',function($data) use ($current_context)
                        {
                            if(isset($data->id) && $data->id != '')
                            {
                               $id = base64_encode($data->id);
                            }
                            else
                            {
                               $id = 'N/A'; 
                            }

                            return $id;
                        })
                    ->make(true);

        $build_result = $json_result->getData();
              
        return response()->json($build_result);
    }

    public function create()
    {
    	$this->arr_view_data['module_title'] = $this->module_title;
    	$this->arr_view_data['page_title'] = 'Add '.$this->module_title;
    	$this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.add',$this->arr_view_data);
    }

    public function store(Request $request)
    {   

        $form_data = $request->all();
        $arr_rules = [  
                        'brand_name' => 'required'
                      ];

        $validator = Validator::make($request->all(),$arr_rules); 


        if($validator->fails())
        {        
            $response['status']      = 'warning';
            $response['description'] = 'Form validations failed, please check form fields.';

            return response()->json($response);
        }  

        ini_set('max_execution_time', 0);
    	
    	$user = \Sentinel::getUser();

    	if($user)
    	{
    		$user_id = $user->id;
    	}

    	$brand_id = isset($request->enc_id)?base64_decode($request->enc_id):false;

    	$brand_name = isset($form_data['brand_name'])?$form_data['brand_name']:false;

        $is_exists = $this->BrandsModel->where('brand_name',$brand_name);

        if($brand_id)
        {
            $is_exists = $is_exists->where('id','<>',$brand_id)->count();
        }
        else
        {
            $is_exists = $is_exists->count();
        }

        if($is_exists)
        {
            $response['status']      = 'error';
            $response['description'] = 'Brand already available.';
            return response()->json($response);
        }



        $brand_img_file_path = '';
            
        $brand_image = isset($form_data['brand_image'])?$form_data['brand_image']:null;
        
        if($brand_image!=null)
        {
            //Validation for product image
            $file_extension = strtolower( $brand_image->getClientOriginalExtension());

            if(!in_array($file_extension,['jpg','png','jpeg']))
            {                           
                $response['status']       = 'error';
                $response['description']  = 'Invalid profile image, please try again.';

               return response()->json($response);
            }

            $brand_img_file_path = $brand_image->store('brand_image');
            if($request->old_brand_image!="")
            {
               $old_img_path     = $this->brand_image.$request->old_brand_image;
               $this->GeneralService->unlink_old_image($old_img_path);
            }
        }

        else
        {
           $brand_img_file_path = $form_data['old_brand_image'];
        }

    	$brand = $this->BrandsModel->firstOrNew(['id' => $brand_id]);

    	$brand->user_id    = $user_id or '';
        $brand->brand_name = isset($request->brand_name)?trim($request->brand_name):'';
    	$brand->brand_image = isset($brand_img_file_path)?$brand_img_file_path:'';
    	$brand->is_active  = isset($request->status)?trim($request->status):'0';

        try
        {
            $products_arr = $this->ProducsModel->where('brand',$brand_id)->get()->toArray();
             //$this->ElasticSearchService->change_brand_name($products_arr,$brand->brand_name);
        }
        catch(\Exception $e)
        {
            $response['status']      = 'error';
            $response['description'] = 'something went wrong, please try again.';
            return response()->json($response);
        }

    	$brand_details = $brand->save();

    	if($brand_details)
    	{
    		$response['status']      = 'success';
    		$response['description'] = 'Brand has been saved.';

    		return response()->json($response);
    	}
    	else
    	{
    		$response['status']      = 'error';
    		$response['description'] = 'something went wrong, please try again.';
    		return response()->json($response);
    	}
    }

    public function edit($enc_id)
    {
    	$brand_id = isset($enc_id)?base64_decode($enc_id):false;

    	$brand_details = $this->BrandsModel->where('id',$brand_id)->first();

    	$this->arr_view_data['module_title']    = $this->module_title;
    	$this->arr_view_data['page_title']      = 'Edit '.$this->module_title;
    	$this->arr_view_data['module_url_path'] = $this->module_url_path;
    	$this->arr_view_data['brand_details']   = $brand_details;

        return view($this->module_view_folder.'.edit',$this->arr_view_data);
    }

    public function delete($enc_id)
    {
    	$brand_id = isset($enc_id)?base64_decode($enc_id):false;

        $brand_assign_status = ProductsModel::where('brand',$brand_id)->count();

        if($brand_assign_status!=0)
        {
            flash::error('Brand have already assigned to product, currently you are not able to perform this action.');
            return redirect()->back();
        }

    	$delete_brand = $this->BrandsModel->where('id',$brand_id)->delete();

    	if($delete_brand)
    	{
    		flash::success('Brand has been deleted.');
    		return redirect()->back();
    	}
    	else
    	{
    		flash::error('Something went wrong, please try again.');
    		return redirect()->back();
    	}
    }

    public function does_brand_exists(Request $request,$enc_id = false)
    {
    	$form_data = $request->all();
    	$id = isset($enc_id)?base64_decode($enc_id):false;
    	    	
    	$brand_name = isset($form_data['brand_name'])?$form_data['brand_name']:false;

    	$is_exists = $this->BrandsModel->where('brand_name',$brand_name);


    	if($id)
    	{
    		$is_exists = $is_exists->where('id','<>',$id)->count();
    	}
    	else
    	{
    		$is_exists = $is_exists->count();
    	}

    	if($is_exists!=0)
	    {
	      return response()->json(['exists'=>'true'],404);
	    }
	    else
	    {
	      return response()->json(['exists'=>'true']);
	    }
    }

    public function change_status(Request $request)
    {
        //dd("ok");
        $id = isset($request->brand_id)?base64_decode($request->brand_id):false;
    	$status = isset($request->status)?$request->status:false;

        if($status == 'activate')
        {
            $is_active = '1';
        }
        else if($status == 'deactivate')
        {   
            $brand_assign_status = ProductsModel::where('brand',$id)->count();

            if($brand_assign_status!=0)
            {
                $response['status'] = 'error';
                $response['description'] = 'Brand have already assigned to product, currently you are not able to perform this action.';
                return response()->json($response);
            }

            $is_active = '0';
        }

        $data['is_active'] = $is_active;

        $update = $this->BrandsModel->where('id',$id)->update($data);

        if($update)
        {
            $response['status']      = 'success';
            $response['description'] = 'Status has been updated.';
            return response()->json($response);
        }
        else
        {
            $response['status']      = 'error';
            $response['description'] = 'Something went wrong, please try again.';
            return response()->json($response);
        }
    }
    public function perform_deactivate($id)
    {

        $brand_assign_status = ProductsModel::where('brand',$id)
                                            ->where('is_deleted', 0)
                                            ->where('is_active', 1)
                                            ->whereHas('userDetails',function($q){
                                                return $q->where('status',1)->where('is_approved',1);
                                            })->count();
        $active_brands =[];
        $deactive_brands =[];
        $message = '';

        if($brand_assign_status!=0)
        {
            $temp_obj = $this->BrandsModel->where('id',$id)->first();
            if(isset($temp_obj))
            {
                array_push($active_brands, $temp_obj->brand_name);
            }
        }
        else
        {
            $temp_obj = $this->BrandsModel->where('id',$id)->first();
            if(isset($temp_obj))
            {
                array_push($deactive_brands, $temp_obj->brand_name);
                $affectedRows = BrandsModel::where('id', '=', $id)->update(array('is_active' => 0));
            }
        }

        if(count($active_brands)>0)
        {    
            $active_brands_string = implode(',', $active_brands);
            $message = "Following brands can not be deactivated because they contain active products:".$active_brands_string;
      
        }
        if(count($deactive_brands)>0)
        {
            $deactive_brands_string = implode(',', $deactive_brands);
            $message .= " Following brands are successfully deactivated:".$deactive_brands_string;
        }
        Session::put('message', $message);

        
        return;
    }
}
