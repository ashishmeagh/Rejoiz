<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Traits\MultiActionTrait;
use App\Models\CatlogsModel;
use App\Models\CatalogImageModel;
use App\Models\CatalogPagesModel;
use App\Models\ProductDetailsModel;
use App\Models\CatalogPdfModel;


use App\Common\Services\GeneralService;


use Flash;
use Sentinel;
use DB;
use Datatables;
use Image;
use Validator;

class CatlogImagesController extends Controller
{
     use MultiActionTrait;
     
    public function __construct(CatalogImageModel   $CatalogImageModel,
                                CatlogsModel        $CatlogsModel,
                                GeneralService      $GeneralService,
                                CatalogPagesModel   $CatalogPagesModel,
                                ProductDetailsModel $ProductDetailsModel,
                                CatalogPdfModel     $CatalogPdfModel
                               )
    {
        $this->arr_view_data       = [];
        $this->CatalogImageModel   = $CatalogImageModel;
        $this->CatlogsModel        = $CatlogsModel;
        $this->BaseModel           = $CatalogPagesModel;
        $this->GeneralService      = $GeneralService;
        $this->CatalogPagesModel   = $CatalogPagesModel;
        $this->ProductDetailsModel = $ProductDetailsModel;
        $this->CatalogPdfModel     = $CatalogPdfModel;
        $this->module_title        = "Catalog Pages";
        $this->module_view_folder  = 'maker.catalog_pages';
        $this->maker_panel_slug    = config('app.project.maker_panel_slug');
        $this->module_url_path     = url($this->maker_panel_slug.'/catalog_pages');
        $this->catlog_image        = base_path().'/storage/app/'; 
        $this->module_catalog_url  = url($this->maker_panel_slug.'/catalogs');
        $this->default_catalog_img = url('/').config('app.project.img_path.catalog_default_image');
    }


    public function view($enc_id)
    {
        if($enc_id!="")
        {
            $catlog_id    = base64_decode($enc_id);
            $obj_catlog   = $this->CatlogsModel->where('id',$catlog_id)->first();     
            $catalog_name = isset($obj_catlog->catalog_name)?$obj_catlog->catalog_name:"";

            $this->arr_view_data['module_title']        = 'Manage Catalogs';
            $this->arr_view_data['page_title']          = 'Manage '.str_plural($this->module_title);
            $this->arr_view_data['module_url_path']     = $this->module_url_path;
            $this->arr_view_data['maker_panel_slug']    = $this->maker_panel_slug;
            $this->arr_view_data['catalog_id']          = $catlog_id;   
            $this->arr_view_data['catalog_name']        = $catalog_name;   
            $this->arr_view_data['module_catalog_url']  = $this->module_catalog_url;        

            return view($this->module_view_folder.'.index',$this->arr_view_data);
        }
        else
        {
            Flash::error('Something went wrong,Please try again.');
            return redirect()->back();
        }
    }

    public function get_catlogs(Request $request)
    {   
        $catalog_id = $request->input('catalog_id');

        /*get all  catlog image data*/

        $catalog_img_tbl_name     = $this->CatalogImageModel->getTable();        
        $prefixed_catalog_img_tbl = DB::getTablePrefix().$this->CatalogImageModel->getTable();

        $catalog_tbl_name        = $this->CatlogsModel->getTable();
        $prefix_catalog_tbl      = DB::getTablePrefix().$this->CatlogsModel->getTable();

        $catalog_pages_tbl        = $this->CatalogPagesModel->getTable();
        $prefix_catalog_pages_tbl      = DB::getTablePrefix().$this->CatalogPagesModel->getTable();


        $catlog_data = DB::table($catalog_pages_tbl)
                           ->select(DB::raw(
                                        $catalog_pages_tbl.'.*'
                                        /*$prefix_catalog_tbl.'.catalog_name'*/
                                        
                                                                        ))   

                                ->Join($catalog_tbl_name,$catalog_pages_tbl.'.catalog_id','=',$prefix_catalog_tbl.'.id')
                                ->where($catalog_pages_tbl.'.catalog_id',$catalog_id)
                                ->orderBy('id','ASC');
        /*dd($catlog_data->get()->toArray());*/
        $arr_search_column = $request->input('column_filter');

        if(isset($arr_search_column['q_sequence']) && $arr_search_column['q_sequence']!="")
        {
            $search_term  = $arr_search_column['q_sequence'];
            $catlog_data  = $catlog_data->where($prefix_catalog_pages_tbl.'.sequence','LIKE', '%'.$search_term.'%');
        }       

        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {   
            $search_term = $arr_search_column['q_status'];

            $catlog_data = $catlog_data->where($prefix_catalog_pages_tbl.'.is_active','LIKE', '%'.$search_term.'%');
        }  
                         
        $current_context = $this;
        $catlog_data  = $catlog_data->get();
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

                        ->editColumn('sequence',function($data) use ($current_context)
                        {  
                            if(isset($data->sequence) && $data->sequence != '')
                            {
                               $sequence = $data->sequence;
                            }
                            else
                            {
                               $sequence = 'N/A'; 
                            }

                            return $sequence;
                        })                        

                        ->editColumn('page_preview',function($data) use ($current_context)
                        { 
                            $arr_data = $arr_test = [];    
                            $id = isset($data->id)?$data->id:"";
                            $arr_data = $this->CatalogImageModel->where('catalog_page_id',$id)
                                                                /*->select('id','catalog_image','catalog_page_id')*/
                                                                ->get()->toArray();

                            $build_html = '';                                  
                            if(isset($arr_data) && $arr_data!="")
                            {
                                foreach($arr_data as $images)
                                {
                                    if(isset($images['image']) && $images['image']!="" && file_exists(base_path().'/storage/app/'.$images['image']))
                                    {
                                        $build_html.= '<div class="col-sm-6 catlog-img"><div class="imgbox"><img src="'.url('/storage/app/'.$images['image']).'" alt=""></div></div>';
                                    }
                                    else
                                    {
                                        $build_html.= '<div class="col-sm-6 catlog-img"><div class="imgbox"><img src="'.$this->default_catalog_img.'" alt=""></div></div>';
                                    } 
                                   
                                }
                            }

                            return '<div class="col-sm-8">
                                        <div class="row">
                                            '.$build_html.'
                                        </div>
                                    </div>';

                        })

                        ->make(true);

        $build_result = $json_result->getData();
        //dd($build_result);
        return response()->json($build_result);

    }

    public function view_catalog_page($encId,Request $request)
    { 
        if($encId!="")
        {
            $arrCatlogImages = [];
            $catlogPageId    = base64_decode($encId);
            $objCatlogImages = $this->CatalogPagesModel->where('id',$catlogPageId)
                                                       ->with(['getCatalogImageData'=>function($q1)
                                                       {
                                                            $q1->select('id','catalog_id','catalog_page_id','image','sku');
                                                       },'getCatalogData'=>function($q2)
                                                       {
                                                            $q2->select('id','catalog_name');
                                                       }])
                                                       ->select('id','catalog_id','sequence','page_type','is_active')
                                                       ->first();
            if($objCatlogImages)
            {
                $arrCatlogImages = $objCatlogImages->toArray();
            }                                           
            
            $catalogId = isset($arrCatlogImages['catalog_id'])?$arrCatlogImages['catalog_id']:"";
            $this->arr_view_data['module_title']     = 'Manage '.str_plural($this->module_title);
            $this->arr_view_data['page_title']       = str_plural($this->module_title).' Detail';
            $this->arr_view_data['module_url_path']  = $this->module_url_path;
            $this->arr_view_data['maker_panel_slug'] = $this->maker_panel_slug;
            $this->arr_view_data['catalog_page_id']  = $catlogPageId;   
            $this->arr_view_data['arrCatlogImages']  = $arrCatlogImages;   
            $this->arr_view_data['default_image']    = $this->default_catalog_img;
            $this->arr_view_data['back_url']  = $this->module_url_path.'/view/'.base64_encode($catalogId);
            
            return view($this->module_view_folder.'.view',$this->arr_view_data);
        }    
        else
        {
            Flash::error('Something went wrong,please try again.');
            return redirect()->back();
        }
    }

    public function delete_catalog_pages($encId)
    {   

        if(isset($encId) && $encId!="")
        {   
           /* try 
            {
                DB::beginTransaction();
*/
                $catalogPageId = base64_decode($encId);

                $arrData = $this->CatalogImageModel->where('catalog_page_id',$catalogPageId)
                                               ->get()
                                               ->toArray(); 
            
                $deleteCatlogPage = $this->CatalogPagesModel->where('id',$catalogPageId)->delete();

                if($deleteCatlogPage)
                {
                  
                    $deleteCatalogImage = $this->CatalogImageModel->where('catalog_page_id',$catalogPageId)->delete();

                    /*unlink image*/
                    if(isset($arrData) && count($arrData)>0)
                    {
                        foreach ($arrData as $key => $value)
                        {
                            $old_img_path    =  $this->catlog_image.$value['image'];
                            $this->GeneralService->unlink_old_image($old_img_path);
                        }
                    }


                    Flash::success('Catalog page has been deleted.');
                    return redirect()->back(); 
                }
                else
                {
                    Flash::error('Something went wrong, please try again.');
                    return redirect()->back();
                }
              
               /*DB::commit();

            } 
            catch (Exception $e)
            {
               DB::rollback();
               Flash::error($e->getMessage());
               return redirect()->back();    
            }*/
           
                  
        }
        else
        {
           Flash::error('Something went wrong, please try again.');
           return redirect()->back();
        }
    }

    public function create($enc_catlog_id=false)
    {
      
        $user_id = 0;
        $catlog_name_arr = [];

        $user = Sentinel::check();

        if($user)
        {
          $user_id = $user->id; 
        }

        /*get all catlog name of logged in user*/

        $catlog_name_arr = $this->CatlogsModel->where('maker_id',$user_id)->where('is_active',1)->get()->toArray();

        $this->arr_view_data['module_title']    =  str_singular($this->module_title);
        $this->arr_view_data['catlog_name_arr'] =  $catlog_name_arr;
        $this->arr_view_data['catalog_id']      =  $enc_catlog_id;

        $this->arr_view_data['page_title']      = 'Add Pages';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.add',$this->arr_view_data);
    }


    public function edit($enc_id)
    {

        /*get catlog data*/
        $catlog_arr = [];
        $catalogPageDataArr = [];
        //$catlog_id  = base64_decode($enc_id);
        $catalog_page_id  = base64_decode($enc_id);

        $user_id = 0;
        $catlog_name_arr = [];

        $user = Sentinel::check();

        if($user)
        {
          $user_id = $user->id; 
        }

        //get all catalog name
        $catlog_name_arr = $this->CatlogsModel->where('maker_id',$user_id)->where('is_active',1)->get()->toArray();

        //get catalog pages

        $catalogPageDataObj = $this->CatalogPagesModel->where('id',$catalog_page_id)
                                                     ->with(['getCatalogImageData','getCatalogData'])
                                                     ->first();

        if(isset($catalogPageDataObj))
        {
           $catalogPageDataArr = $catalogPageDataObj->toArray();
        }                                            

 
        $this->arr_view_data['module_title']    = str_singular($this->module_title);
        //$this->arr_view_data['catlog_arr']      = $catlog_arr;
        $this->arr_view_data['catlog_arr']      = $catalogPageDataArr;
        $this->arr_view_data['catlog_name_arr'] = $catlog_name_arr;
        $this->arr_view_data['page_title']      = 'Edit '.str_singular($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.edit',$this->arr_view_data);   
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

            $page_id    = isset($form_data['enc_id'])?base64_decode($form_data['enc_id']):false;
            $catalog_id = isset($form_data['enc_catlog_id'])?base64_decode($form_data['enc_catlog_id']):$form_data['catalog_id'];

            $isUpdate = false;

            if($page_id && $catalog_id)
            {
                $isUpdate = true;
            }


            $duplicate_arr = array_unique(array_diff_assoc($form_data['sku'], array_unique( $form_data['sku'] ) ) );

            
            if(isset($duplicate_arr) && count($duplicate_arr)>0)
            {
               /*Flash::error("Entered sku's".implode(',',$duplicate_arr)." are duplicate.");*/
               $response['status']      = 'error';
               $response['description'] = "Following SKU(s) are duplicate: ".implode(',',$duplicate_arr);
               return response()->json($response);

            }

            /*Check SKU is present or duplication*/
            if(isset($form_data['page_type_radio']) && $form_data['page_type_radio'] == 'product_images')
            {
                if(isset($form_data['sku']) && sizeof($form_data['sku'])>0)
                {   
                    $exstingArray = [];

                    foreach($form_data['sku'] as $key=>$sku)
                    {                

                        if($isUpdate == true)
                        {   
                            $imageId = isset($form_data['image_pk_id'][$key])?$form_data['image_pk_id'][$key]:0;
                            //dump($imageId,$sku,$form_data);
                            $getSkuRecordCount = $this->CatalogImageModel->where('id','<>',$imageId)
                                                                         ->where('sku',$sku)
                                                                         ->where('catalog_id',$catalog_id)
                                                                         ->count();

                        }
                        else
                        {
                            $getSkuRecordCount = $this->CatalogImageModel->where('sku',$sku)
                                                                         ->where('catalog_id',$catalog_id)
                                                                         ->count();

                        }

                   
                        if($getSkuRecordCount>0)
                        {
                            $response['status']       = 'error';
                            $response['description']  = $sku.' SKU is already exists.';

                            return response()->json($response);
                        }

                        // Check added sku is valid vender or not
                        $checkVenderSku = $this->is_vendor_acceesible($sku);
                        $checkProductActiveStatus = $this->is_product_active($sku);

         

                        if($checkVenderSku == false)
                        {
                            $response['status']       = 'error';
                            $response['description']  = 'This '.$sku.' sku is not match with your product sku.';

                            return response()->json($response);   
                        }


                        if($checkProductActiveStatus == false)
                        {
                            $response['status']       = 'error';
                            $response['description']  = 'This '.$sku.' sku is not currently activate.';

                            return response()->json($response);   
                        }


                    }
                }
            }



            /*check page sequence duplication*/

            if(isset($form_data['sequence']) && $form_data['sequence']!='')
            {
                $sequence_count = $this->CatalogPagesModel->where('sequence',$form_data['sequence'])->where('catalog_id',$catalog_id);
           
                    if($isUpdate == true)
                    {
                        $sequence_count = $sequence_count->where('id','<>',$page_id)->count();
                    }
                     else
                    {
                       $sequence_count =  $sequence_count->count();
                    }

                if($sequence_count >0)
                {
                    $response['status']       = 'error';
                    $response['description']  = 'Sequence is already assigned.';

                    return response()->json($response);
                }  
            }
            

            /*add entry into catalog pages table*/
            $page_data               = [];
            $page_data['catalog_id'] = $form_data['catalog_id'];
            $page_data['sequence']   = $form_data['sequence'];
            $page_data['page_type']  = $form_data['page_type_radio'];

            if(isset($form_data['status']) && $form_data['status']!='')
            {
                $status = '1';
            }
            else
            {
                $status = '0';
            } 


            $page_data['is_active']  = $status;

            $catalog_page = $this->CatalogPagesModel->firstOrNew(['id'=>$page_id]);

            $catalog_page->catalog_id = $page_data['catalog_id'];
            $catalog_page->sequence   = $page_data['sequence'];
            $catalog_page->page_type  = $page_data['page_type'];
            $catalog_page->is_active     = $page_data['is_active'];


            $result = $catalog_page->save();

        if(isset($page_data['page_type']) && $page_data['page_type']!='' && $page_data['page_type'] =='product_images')
        {
                /*for update page first of all all image data from  page_id*/

                if(isset($page_id) && $page_id!='')
                {
                    $arrData = $this->CatalogImageModel->where('catalog_page_id',$page_id)
                                                       ->get()
                                                       ->toArray(); 

                    $delete_res = $this->CatalogImageModel->where('catalog_page_id',$page_id)->delete();      
                }

                if(isset($form_data['sku']) && sizeof($form_data['sku'])>0)
                {    
                    foreach($form_data['sku'] as $key => $sku)
                    {  


                        $data['catalog_id']    = $form_data['catalog_id'];
                        //$data['page_sequence'] = $form_data['sequence'];
                        $data['sku']           = $sku;
                       
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

                            $data['image']        = $catlog_img_file_path;

                            $old_img = isset($form_data['old_product_img'][$key])?$form_data['old_product_img'][$key]:'';

                            $old_img_path =  $this->catlog_image.$old_img;

                            if(isset($old_img))
                            { 
                               $this->GeneralService->unlink_old_image($old_img_path);
                            }

                        }
                        else
                        {  
                           $catlog_img_file_path = isset($form_data['old_product_img'][$key])?$form_data['old_product_img'][$key]:'';

                       

                        }
                      
                        $catalog_img_data                    = [];
                        $catalog_img_data['catalog_id']      = $form_data['catalog_id'] or 0;
                        
                        $catalog_img_data['image']           = isset($catlog_img_file_path)?$catlog_img_file_path:'';
                        $catalog_img_data['sku']             = isset($sku)?$sku:null;
                        $catalog_img_data['is_active']       = '1';
                        $catalog_img_data['catalog_page_id'] = $catalog_page->id;
                    
                        $catalogs_img = $this->CatalogImageModel->create($catalog_img_data);

                    }
                }    

        }
        else
        {  

                if(isset($page_id) && $page_id!='')
                {
                    $delete_res = $this->CatalogImageModel->where('catalog_page_id',$page_id)->delete();
                }

                $cover_image = isset($form_data['cover_image'])?$form_data['cover_image']:null;
                
                if($cover_image!=null)
                { 
                    //Validation for product image
                    $file_extension = strtolower($cover_image->getClientOriginalExtension());

                    if(!in_array($file_extension,['jpg','png','jpeg']))
                    {                           
                        $response['status']       = 'error';
                        $response['description']  = 'Invalid catalog image, please try again.';

                       return response()->json($response);
                    }

                    $catlog_img_file_path = $cover_image->store('catalog_image');

                    $data['image']        = $catlog_img_file_path;

                }
                else
                {
                    $catlog_img_file_path = $form_data['old_cover_page_img'];
                }    
                  
                $catalog_img_data                  = [];
                $catalog_img_data['catalog_id']    = $form_data['catalog_id'] or 0;
           
                $catalog_img_data['image']         = isset($catlog_img_file_path)?$catlog_img_file_path:'';
                $catalog_img_data['sku']           = isset($sku)?$sku:null;
                $catalog_img_data['is_active']     = '1';
                $catalog_img_data['catalog_page_id'] = $catalog_page->id;
             
                $catalogs_img = $this->CatalogImageModel->create($catalog_img_data);

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

    

    /* check the current login user can access this sku?  */
    public function is_vendor_acceesible($sku='')
    {
        $isAccessible       = false;
        $currentLoginUserId = 0;
        $loginUser = Sentinel::check();
        if($loginUser == true)
        {
            $currentLoginUserId = $loginUser->id;
        }

        if($currentLoginUserId != 0 && $sku != '')
        {
            $isAccessibleData = $this->ProductDetailsModel->where('sku',$sku)
                                                      ->whereHas('productDetails',function($q) use ($currentLoginUserId)
                                                      {
                                                        return $q->where('user_id',$currentLoginUserId);
                                                      })
                                                      ->count();
            if($isAccessibleData > 0)
            {
                return $isAccessible = true;
            }
        }
        return $isAccessible;
    }
    /*Check product status*/
    public function is_product_active($sku='')
    {
        $isAccessible       = false;
        $currentLoginUserId = 0;
        $loginUser = Sentinel::check();
        if($loginUser == true)
        {
            $currentLoginUserId = $loginUser->id;
        }

        if($currentLoginUserId != 0 && $sku != '')
        {
            $isAccessibleData = $this->ProductDetailsModel->where('sku',$sku)
                                                      ->whereHas('productDetails',function($q) use ($currentLoginUserId)
                                                      {
                                                        return $q->where('user_id',$currentLoginUserId)->where('is_active',1);
                                                        
                                                      })
                                                      ->count();
            if($isAccessibleData > 0)
            {
                return $isAccessible = true;
            }
        }
        return $isAccessible;
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

        // try
        // {
        //     DB::beginTransaction();

            $catalog_image_id = base64_decode($request->input('id'));

            $pageImgDetails   =  $this->CatalogImageModel->where('id',$catalog_image_id)->first();

            if($pageImgDetails)
           {     

            $result = $this->CatalogImageModel->where('id',$catalog_image_id)->delete();
           
            if($result)
            {
               /*image unlink*/
                $image = isset($pageImgDetails['image'])?$pageImgDetails['image']:'';

                $old_img_path =  $this->catlog_image.$image;

                $this->GeneralService->unlink_old_image($old_img_path);
               
                $response['status']      = 'success';
                $response['description'] = 'product has been deleted from catalog.';
                return response()->json($response);
            }
           } 

           else if($pageImgDetails==null)
          {
                $response['status']      = 'success_not_already_in_catalog';
                return response()->json($response);
          }

            else
            {
                
              $response['status']      = 'error';
              $response['description'] = 'Error occurred while deleting product from catalog.';
              return response()->json($response);

            }

        //     DB::commit();

        // }
        // catch (Exception $e)
        // {
        //     DB::rollback();

        //     Flash::error($e->getMessage());

        //     return redirect()->back();

        // }


    }



    public function uploadPdf()
    {  

        $this->arr_view_data['page_title']      = "Upload Pdf";
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.upload_pdf',$this->arr_view_data);
    }

    public function savePdf(Request $request)
    {   $user_id = 0;
        $user = Sentinel::check();

        if(isset($user))
        {
          $user_id = isset($user->id)?$user->id:0;
        }

        $form_data = $request->all();
        
        $arr_rules = [  
                        'upload_pdf'   => 'required',
                        'catalog_name' =>'required',
                        'cover_image'  =>'required'
                     ];

        $validator = Validator::make($request->all(),$arr_rules); 


       if($validator->fails())
       {        
        $response['status']      = 'warning';
        $response['description'] = 'Form validations failed, please check form fields.';
        return response()->json($response);
       }  



        /*cover image upload*/

        $coverImage = isset($form_data['cover_image'])?$form_data['cover_image']:null;


        if($coverImage!=null)
        { 
            //Validation for product image
            $file_extension = strtolower($coverImage->getClientOriginalExtension());

            if(!in_array($file_extension,['jpeg','jpg','png']))
            {                           
                $response['status']       = 'error';
                $response['description']  = 'Invalid cover image, please try again.';

               return response()->json($response);
            }

            $cover_img_file_path = $coverImage->store('catalog_pdf');
            
            $data['cover_image'] = isset($cover_img_file_path)?$cover_img_file_path:'';
         
        }


        $catlogPdf = isset($form_data['upload_pdf'])?$form_data['upload_pdf']:null;

    
        if($catlogPdf!=null)
        { 
            //Validation for product image
            $file_extension = strtolower($catlogPdf->getClientOriginalExtension());

            if(!in_array($file_extension,['pdf']))
            {                           
                $response['status']       = 'error';
                $response['description']  = 'Invalid file type, please try again.';

               return response()->json($response);
            }

            $catlog_pdf_file_path = $catlogPdf->store('catalog_pdf');
            
            $data['pdf_file']      = isset($catlog_pdf_file_path)?$catlog_pdf_file_path:'';
            $data['vendor_id']     = isset($user_id)?$user_id:0;
            $data['is_active']     = 1;
            $data['catalog_name']  = isset($form_data['catalog_name'])?$form_data['catalog_name']:'';

                    
            $result = $this->CatalogPdfModel->create($data);

            if($result)
            {
                $response['status']       = 'success';
                $response['description']  = 'Pdf has been uploaded.';

                return response()->json($response);
            }

        }
        else
        {
            $response['status']       = 'error';
            $response['description']  = 'File is empty.';

            return response()->json($response);
        }
    
    }

}


