<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CatalogPdfImagesModel;
use App\Common\Traits\MultiActionTrait;
use App\Models\CatlogsModel;

use App\Common\Services\GeneralService;

use Flash;
use Sentinel;
use DB;
use Datatables;
use Image;
use Validator;


class CatalogPdfImageController extends Controller
{
    use MultiActionTrait;

    public function __construct(
                                  CatlogsModel            $CatlogsModel,
                                  GeneralService          $GeneralService,
                                  CatalogPdfImagesModel   $CatalogPdfImagesModel
                               )
    {
        $this->arr_view_data       = [];
        $this->CatlogsModel        = $CatlogsModel;
        $this->BaseModel           = $CatalogPdfImagesModel;
        $this->GeneralService      = $GeneralService;
        $this->CatalogPdfImagesModel = $CatalogPdfImagesModel;
       
        $this->module_title        = "Catalog Images";
        $this->module_view_folder  = 'maker.catalog_pdf_images';
        $this->maker_panel_slug    = config('app.project.maker_panel_slug');
        $this->module_url_path     = url($this->maker_panel_slug.'/catalog_images');
        $this->catlog_image        = base_path().'/storage/app/'; 
        $this->default_catalog_img = url('/').config('app.project.img_path.catalog_default_image');
    }

    
    public function index()
    { 
    	$this->arr_view_data['module_title']      = 'Manage '.str_plural($this->module_title);
    	$this->arr_view_data['page_title']        = 'Manage '.str_plural($this->module_title);
    	$this->arr_view_data['module_url_path']   = $this->module_url_path;
        $this->arr_view_data['catalog_img_path']  = $this->catlog_image;
        $this->arr_view_data['maker_panel_slug']  = $this->maker_panel_slug;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }


    public function get_images(Request $request)
    {  
        $user_id = 0;
      
        $user = \Sentinel::getUser();

        if($user)
        {
            $user_id = $user->id;
        }
     
       /*get all  catlog image data*/

        $catalog_img_tbl         = $this->CatalogPdfImagesModel->getTable();        
        $prefixed_catalog_img_tbl = DB::getTablePrefix().$this->CatalogPdfImagesModel->getTable();

        $catalog_tbl_name        = $this->CatlogsModel->getTable();
        $prefix_catalog_tbl      = DB::getTablePrefix().$this->CatlogsModel->getTable();


        $catlog_data = DB::table($catalog_img_tbl)
                                    ->select(DB::raw(
                                        $catalog_img_tbl.'.*,'.
                                        $prefix_catalog_tbl.'.id as cid,'.
                                        $prefix_catalog_tbl.'.catalog_name,'.
                                        $prefix_catalog_tbl.'.maker_id'

                                    ))   

                                    ->Join($catalog_tbl_name,$prefixed_catalog_img_tbl.'.catalog_id','=',$prefix_catalog_tbl.'.id')

                                    ->where($prefix_catalog_tbl.'.maker_id',$user_id)
                                    ->orderBy('id','DESC');
      
        $arr_search_column = $request->input('column_filter');
    
        if(isset($arr_search_column['q_catalog_name']) && $arr_search_column['q_catalog_name']!="")
        {
            $search_term  = $arr_search_column['q_catalog_name'];
            $catlog_data  = $catlog_data->having($prefix_catalog_tbl.'.catalog_name','LIKE', '%'.$search_term.'%');
        }

         
        if(isset($arr_search_column['q_sequence']) && $arr_search_column['q_sequence']!="")
        {
            $search_term  = $arr_search_column['q_sequence'];
            $catlog_data  = $catlog_data->where($prefixed_catalog_img_tbl.'.page_sequence','LIKE', '%'.$search_term.'%');
        }

        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {  
            $search_term = $arr_search_column['q_status'];
            $catlog_data = $catlog_data->where($prefixed_catalog_img_tbl.'.is_active','LIKE', '%'.$search_term.'%');
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

                        ->editColumn('sequence',function($data) use ($current_context)
                        {
                            if(isset($data->page_sequence) && $data->page_sequence != '')
                            {
                               $page_sequence = $data->page_sequence;
                            }
                            else
                            {
                               $page_sequence = 'N/A'; 
                            }
                           

                            return  $page_sequence;
                        })

    	                ->make(true);

        $build_result = $json_result->getData();

        return response()->json($build_result);

    	                

    }


    public function create()
    {
    	$user_id = 0;
    	$catalogData = [];

    	$user = Sentinel::check();

    	if(isset($user))
    	{
           $user_id = $user->id; 
    	}
        /*get catalog details*/

        $catalogData = $this->CatlogsModel->where('is_active',1)->where('maker_id',$user_id)->get()->toArray();

        
    	$this->arr_view_data['module_title']    = str_singular($this->module_title);
    	$this->arr_view_data['catalog_data']    = $catalogData;
    	$this->arr_view_data['page_title']      = 'Add '.str_singular($this->module_title);
    	$this->arr_view_data['module_url_path'] = $this->module_url_path;
        return view($this->module_view_folder.'.create',$this->arr_view_data);
    }


    public function store(Request $request)
    {  
        try
        {
            DB::beginTransaction();

            $user_id   = 0;
            $data      = [];
            $status    ='';
            $form_data = $request->all();
           
            $user = \Sentinel::getUser();

            if($user)
            {
                $user_id = $user->id;
            }

            $duplicate_arr = array_unique(array_diff_assoc($form_data['sequence'], array_unique( $form_data['sequence'] ) ) );

            
            if(isset($duplicate_arr) && count($duplicate_arr)>0)
            {
               /*Flash::error("Entered sku's".implode(',',$duplicate_arr)." are duplicate.");*/
               $response['status']      = 'error';
               $response['description'] = "Following page sequence(s) are duplicate: ".implode(',',$duplicate_arr);
               return response()->json($response);

            }

            /*--------Check  sequence is present or duplication-------------------*/
           
            if(isset($form_data['sequence']) && sizeof($form_data['sequence'])>0)
            {   
                foreach($form_data['sequence'] as $key=>$sequence)
                {                
                    $getCount = $this->CatalogPdfImagesModel->where('page_sequence',$sequence)
                                                            ->where('catalog_id',$form_data['catalog_id'])
                                                            ->count();

                    if($getCount>0)
                    {
                        $response['status']       = 'error';
                        $response['description']  = 'Page sequence is already exists.';

                        return response()->json($response);
                    }

                }

            }
           
            /*---------------------------------------------------------------------*/

            if(isset($form_data['sequence']) && sizeof($form_data['sequence'])>0)
            {    
                foreach($form_data['sequence'] as $key => $sequence)
                {  

                    $data['catalog_id']    = isset($form_data['catalog_id'])?$form_data['catalog_id']:'';
                    $data['page_sequence'] =  isset($sequence)?$sequence:'';
                    
                    $data['is_active']     = '1';

                  
                    $catlog_image = isset($form_data['catalog_image'][$key])?$form_data['catalog_image'][$key]:null;
                
                    if($catlog_image!=null)
                    { 
                        //Validation for product image
                        $file_extension = strtolower($catlog_image->getClientOriginalExtension());

                        if(!in_array($file_extension,['jpg','png','jpeg']))
                        {                           
                            $response['status']       = 'error';
                            $response['description']  = 'Invalid catalog image, please try again.';

                           return response()->json($response);
                        }

                        $catlog_img_file_path = $catlog_image->store('catalog_image');
   
                    }
                    else
                    {  
                        $response['status']       = 'error';
                        $response['description']  = 'Catalog image is empty.';
                        return response()->json($response);
                    }
                  
                      
                    $data['image']   = isset($catlog_img_file_path)?$catlog_img_file_path:'';
                    
                    $catalogs_img = $this->CatalogPdfImagesModel->create($data);

                }
            }      

            DB::commit();

            if($catalogs_img)
            {
                $response['status']      = 'success';
                $response['description'] = 'Catalog page has been saved.';

                return response()->json($response);
            }
            else
            {
                $response['status']      = 'error';
                $response['description'] = 'Something went wrong, please try again.';
                return response()->json($response);
            }

        }
        catch(Exception $e)
        {
            DB::rollback();
            $response['status']        = 'error';
            $response['description']   = $e->getMessage();
            return response()->json($response);
        }
    }



    public function edit($enc_id)
    {
        /*get catlog data*/
        $catalogData = $catalogPageArr = [];
        $user_id = 0;
        $catalog_page_id  = base64_decode($enc_id);

        $user = Sentinel::check();

        if($user)
        {
          $user_id = $user->id; 
        }

        //get all catalog name
        $catalogData = $this->CatlogsModel->where('is_active',1)->where('maker_id',$user_id)->get()->toArray();

        //get catalog pages

        $catalogPageDataObj = $this->CatalogPdfImagesModel->where('id',$catalog_page_id)
                                                          ->with(['getCatalogData'])
                                                          ->first();

        if(isset($catalogPageDataObj))
        {
            $catalogPageArr = $catalogPageDataObj->toArray();
        }
                                                   
     
        $this->arr_view_data['module_title']        = str_singular($this->module_title);
        $this->arr_view_data['catlog_arr']          = $catalogData;
        $this->arr_view_data['catalogPageArr']      = $catalogPageArr;
       
        $this->arr_view_data['page_title']          = 'Edit '.str_singular($this->module_title);
        $this->arr_view_data['module_url_path']     = $this->module_url_path;

        return view($this->module_view_folder.'.edit',$this->arr_view_data);   
    }


    public function update(Request $request)
    {
        $updated_data = [];  
        $form_data    = $request->all();
        $status       = '';
        
        $id         = isset($form_data['enc_id'])?base64_decode($form_data['enc_id']):false;
        $catalog_id = isset($form_data['enc_catlog_id'])?base64_decode($form_data['enc_catlog_id']):false;

        /*---------check page sequence--------------*/
        $getCount = $this->CatalogPdfImagesModel->where('page_sequence',$form_data['sequence'])
                                                ->where('catalog_id',$catalog_id)
                                                ->count();

        if($getCount>0)
        {
            $response['status']       = 'error';
            $response['description']  = 'Page sequence is already exists.';
            return response()->json($response);
        }

        /*-----------------------------------------------------*/


        $updated_data['page_sequence'] = isset($form_data['sequence'])?$form_data['sequence']:'';

        if(isset($form_data['status']) && $form_data['status']!='')
        {
          $status = 1;
        }
        else
        {
          $status = 0;
        }

        $updated_data['is_active'] = isset($status)?$status:'';

        $Image = isset($form_data['catalog_image'])?$form_data['catalog_image']:''; 

        if($Image !=null && $Image!="")
        {
            $file_extension = strtolower($Image->getClientOriginalExtension());

            if(!in_array($file_extension,['jpg','png','jpeg']))
            {                           
                $response['status']       = 'error';
                $response['description']  = 'Invalid catalog image, please try again.';
                return response()->json($response);
            }

            $catlog_img_file_path = $Image->store('catalog_image');

            /*unlink image*/

            $old_img = isset($form_data['old_catlog_image'])?$form_data['old_catlog_image']:'';

            $old_img_path =  $this->catlog_image.$old_img;

            if(isset($old_img))
            { 
               $this->GeneralService->unlink_old_image($old_img_path);
            }
        }
        else
        {
           $catlog_img_file_path = isset($form_data['old_catlog_image'])?$form_data['old_catlog_image']:'';
        } 

     
        $updated_data['image'] = $catlog_img_file_path;   
         
        $result = $this->CatalogPdfImagesModel->where('id',$id)->where('catalog_id',$catalog_id)->update($updated_data);  

        if($result)
        {
           $response['status']      = 'success';
           $response['description'] = 'Page has been updated.';

           return response()->json($response);
        }
        else
        {
           $response['status']      = 'error';
           $response['description'] = 'Something went wrong, please try again.';

           return response()->json($response);   
        }  
              
    }

}

    