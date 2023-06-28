<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Traits\MultiActionTrait;
use App\Models\CatlogsModel;
use App\Models\CatalogImageModel;
use App\Models\CatalogPagesModel;

use Flash;
use Validator;
use DB;


class CatlogController extends Controller
{
	use MultiActionTrait;


    public function __construct( CatlogsModel $CatlogsModel,
                                CatalogImageModel $CatalogImageModel,
                                CatalogPagesModel $CatalogPagesModel
                               )
    {
        $this->arr_view_data       = [];
        $this->CatlogsModel        = $CatlogsModel;
        $this->BaseModel           = $this->CatlogsModel;
        $this->CatalogImageModel   = $CatalogImageModel;
        $this->CatalogPagesModel   = $CatalogPagesModel;
        $this->module_title        = "Catalog";
        $this->module_view_folder  = 'maker.catlogs';
        $this->maker_panel_slug    = config('app.project.maker_panel_slug');
        $this->module_url_path     = url($this->maker_panel_slug.'/catalogs');
        $this->module_url_path_images = url($this->maker_panel_slug.'/catalog_pages');
    }

    public function index()
    {
    	$this->arr_view_data['module_title']     = 'Create '.str_plural($this->module_title);
    	$this->arr_view_data['page_title']       = 'Create '.str_plural($this->module_title);
    	$this->arr_view_data['module_url_path']  = $this->module_url_path;
        $this->arr_view_data['module_url_path_images']  = $this->module_url_path_images;
        $this->arr_view_data['maker_panel_slug'] = $this->maker_panel_slug;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }


    public function get_catlogs(Request $request)
    {
        $user_id = 0;
        $catlog_data = [];

         $user = \Sentinel::getUser();

        if($user)
        {
            $user_id = $user->id;
        }

        /*get all  catlog image data*/
    	//$catlog_data  = $this->CatlogsModel->with(['maker_details'])->where('maker_id',$user_id)->get();

        $catlog_data  = $this->CatlogsModel->with(['maker_details'])->where('maker_id',$user_id);
      
        $arr_search_column = $request->input('column_filter');

       
        if(isset($arr_search_column['q_catalog_name']) && $arr_search_column['q_catalog_name']!="")
        {
            $search_term  = $arr_search_column['q_catalog_name'];
            $catlog_data  = $catlog_data->having('catalog_name','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {  
            $search_term = $arr_search_column['q_status'];
            $catlog_data = $catlog_data->where('is_active','LIKE', '%'.$search_term.'%');
        }  



        $current_context = $this;

    	$json_result  = \Datatables::of($catlog_data);

        /* Modifying Columns */
        $json_result =  $json_result->editColumn('enc_id',function($data) use ($current_context)
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

                        ->editColumn('catalog_name',function($data) use ($current_context)
                        {
                            if(isset($data->catalog_name) && $data->catalog_name != '')
                            {
                               $catalog_name = $data->catalog_name;
                            }
                            else
                            {
                               $catalog_name = 'N/A'; 
                            }
                           

                            return  $catalog_name;
                        })

    	                ->make(true);

        $build_result = $json_result->getData();

        return response()->json($build_result);

    }


    public function create()
    {
    	$this->arr_view_data['module_title']    = str_singular($this->module_title);
    	$this->arr_view_data['page_title']      = 'Add '.str_singular($this->module_title);
    	$this->arr_view_data['module_url_path'] = $this->module_url_path;
        return view($this->module_view_folder.'.add',$this->arr_view_data);
    }


    public function edit($enc_id)
    {

        /*get catlog data*/
        $catlog_arr = [];
        $catlog_id  = base64_decode($enc_id);

        $obj_catlog_data = $this->CatlogsModel->where('id',$catlog_id)->first();


        if(isset($obj_catlog_data))
        {
           $catlog_arr = $obj_catlog_data->toArray();
        }

        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['catlog_arr']      = $catlog_arr;
        $this->arr_view_data['page_title']      = 'Edit '.$this->module_title;
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.edit',$this->arr_view_data);   
    }


    public function store(Request $request)
    {
        $user_id   = 0;
        $form_data = $request->all();

        $arr_rules = [  
                        'catalog_name' => 'required'
                     ];

        $validator = Validator::make($request->all(),$arr_rules); 


       if($validator->fails())
       {        
        $response['status']      = 'warning';
        $response['description'] = 'Form validations failed, please check form fields.';
        return response()->json($response);
       }  


        $user = \Sentinel::getUser();

        if($user)
        {
            $user_id = $user->id;
        }

        $catlog_id = isset($request->enc_id)?base64_decode($request->enc_id):false;

       

        $is_exists = $this->CatlogsModel->where('catalog_name',$form_data['catalog_name']);

        if($catlog_id)
        {
            $is_exists = $is_exists->where('id','<>',$catlog_id)->count();
        }
        else
        {
            $is_exists = $is_exists->count();
        }

        if($is_exists)
        {
            $response['status']      = 'warning';
            $response['description'] = 'Catalog name already exists.';
            return response()->json($response);
        }

    
        $catlogs = $this->CatlogsModel->firstOrNew(['id' => $catlog_id]);

        $catlogs->maker_id        = $user_id or '';
        $catlogs->catalog_name    = isset($request->catalog_name)?trim($request->catalog_name):'';
          
        if(isset($form_data['status']) && $form_data['status']!='')
        {
            $status = '1';
        }
        else
        {
            $status = '0';
        }  

        $catlogs->is_active = $status; 
        
        $catlogs_details    = $catlogs->save();

        if($catlogs_details)
        {
            $response['status']      = 'success';
            $response['description'] = 'Catalog has been saved.';

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
        $id     = isset($request->catalog_id)?base64_decode($request->catalog_id):false;
        $status = isset($request->status)?$request->status:false;

        if($status == 'activate')
        {
            $is_active = '1';
        }
        else if($status == 'deactivate')
        {   
          /*  $catlog_assign_status = $this->CatlogsModel->where('id',$id)->count();

            if($catlog_assign_status!=0)
            {
                $response['status']      = 'error';
                $response['description'] = 'Brand have already assigned to product,currently you are not able to perform this action.';
                return response()->json($response);
            }*/

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


    public function delete($enc_id = FALSE)
    {
        if(!$enc_id)
        {
            return redirect()->back();
        }

      
        if($this->perform_delete(base64_decode($enc_id)))
        {   

            Flash::success(str_singular($this->module_title).' has been deleted.');
            return redirect()->back();
        }  
        else
        {
            Flash::error('Error occurred while '.str_singular( $this->module_title).' deletion.');
            return redirect()->back();
        }

        
    }

    public function perform_delete($id)
    {   
        try
        {   DB::beginTransaction();

            $delete = $this->BaseModel->where('id',$id)->delete();

            if($delete)
            {
                $resultCatalogPageDelete  = $this->CatalogPagesModel->where('catalog_id',$id)->delete();
                $resultCatalogImageDelete = $this->CatalogImageModel->where('catalog_id',$id)->delete();

            }

            DB::commit();
    
        }
        catch(Exception $e)
        {
            DB::rollback();
            Flash::error($e->getMessage());

            return response()->json($response);
        }
        

        
        if($delete)
        {
          return TRUE;
        }

        return FALSE;

    }

}
