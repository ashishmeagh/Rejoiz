<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Common\Traits\MultiActionTrait;
use App\Models\CatalogPdfModel;
use App\Common\Services\GeneralService;

use Flash;
use Sentinel;
use DB;
use Validator;

class CatalogController extends Controller
{
    use MultiActionTrait;
     
    public function __construct(
                              
                                CatalogPdfModel $CatalogPdfModel,
                                GeneralService $GeneralService
                               )
    {
        $this->arr_view_data       = [];      
        $this->BaseModel           = $CatalogPdfModel;      
        $this->CatalogPdfModel     = $CatalogPdfModel;
        $this->GeneralService      = $GeneralService;
        $this->module_title        = "Catalogs";
        $this->module_view_folder  = 'maker.catalog';
        $this->maker_panel_slug    = config('app.project.maker_panel_slug');
        $this->module_url_path     = url($this->maker_panel_slug.'/catalog_pdf');
        $this->catlog_image        = base_path().'/storage/app/'; 
        $this->module_catalog_url  = url($this->maker_panel_slug.'/catalog_pdf');
        $this->default_catalog_img = url('/').config('app.project.img_path.catalog_default_image');
    }


    public function temp()
    {
       $cat_images = DB::table('temp')->get()->toArray();

       
       view()->share('items',$cat_images);


       
        $pdf = \PDF::loadView('pdfview');
       
        return $pdf->download('pdfview.pdf');
       
        



        return view('pdfview');
    }

    public function index()
    {
        $this->arr_view_data['module_title']     = 'Manage '.str_plural($this->module_title);
        $this->arr_view_data['page_title']       = 'Hotspotted PDF';
        $this->arr_view_data['module_url_path']       = $this->module_url_path;
        $this->arr_view_data['maker_panel_slug'] = $this->maker_panel_slug;
        $this->arr_view_data['cover_image_path'] = url('/').'/storage/app/';

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_catlogs(Request $request)
    {   
        $user = \Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }

        /*get all catlog data*/

        $catalog_pdf_tbl_name     = $this->CatalogPdfModel->getTable();        
        $prefixed_catalog_pdf_tbl = DB::getTablePrefix().$this->CatalogPdfModel->getTable();


        $catlog_data = DB::table($catalog_pdf_tbl_name)
                           ->select(DB::raw(
                                        $catalog_pdf_tbl_name.'.*'    
                                    ))   
                                ->where($catalog_pdf_tbl_name.'.vendor_id',$loggedInUserId)
                                ->orderBy('id','ASC');
       
        $arr_search_column = $request->input('column_filter');

        if(isset($arr_search_column['q_catalog_name']) && $arr_search_column['q_catalog_name']!="")
        {
            $search_term  = $arr_search_column['q_catalog_name'];
            $catlog_data  = $catlog_data->where($catalog_pdf_tbl_name.'.catalog_name','LIKE', '%'.$search_term.'%');
        }       

        if(isset($arr_search_column['q_status']) && $arr_search_column['q_status']!="")
        {   
            $search_term = $arr_search_column['q_status'];

            $catlog_data = $catlog_data->where($catalog_pdf_tbl_name.'.is_active','LIKE', '%'.$search_term.'%');
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
                        ->make(true);

        $build_result = $json_result->getData();
        return response()->json($build_result);

    }
   
    public function edit($enc_id)
    {
        $user_id = 0;
        $catalog_arr = [];
        $catalog_id  = base64_decode($enc_id);
     
        $user = Sentinel::check();

        if($user)
        {
          $user_id = $user->id; 
        }
     
        $catalog_details = $this->CatalogPdfModel->where('id',$catalog_id)->first();

        if(isset($catalog_details))
        {
          $catalog_arr = $catalog_details->toArray(); 
        }
    
        $this->arr_view_data['module_title']    = str_singular($this->module_title);
        $this->arr_view_data['catalog_arr']     = $catalog_arr;
        $this->arr_view_data['page_title']      = 'Edit '.str_singular($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.edit',$this->arr_view_data);   
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

    public function delete($enc_id)
    {
        $catalog_id = isset($enc_id)?base64_decode($enc_id):false;

        $catalogData = $this->CatalogPdfModel->where('id',$catalog_id)->first();

        $delete_catalog = $this->CatalogPdfModel->where('id',$catalog_id)->delete();

        if($delete_catalog)
        {
            @unlink($this->catlog_image.$catalogData['cover_image']); // Delete cover image from folder
            @unlink($this->catlog_image.$catalogData['pdf_file']); // Delete PDF file from folder

            flash::success('Catalog has been deleted.');
            return redirect()->back();
        }
        else
        {
            flash::error('Something went wrong, please try again.');
            return redirect()->back();
        }
    }

    public function create()
    {  

        $this->arr_view_data['page_title']      = "Upload Pdf";
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.create',$this->arr_view_data);
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

        if(isset($form_data['status']) && $form_data['status']!='')
        {
            $status = '1';
        }
        else
        {
            $status = '0';
        } 


        $data['is_active']  = $status;


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
                $response['link']         = $this->module_catalog_url;
               
                return response()->json($response);
            }
            else
            {

                $response['status']       = 'error';
                $response['description']  = 'Something went wrong, please try again.';
               
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



    public function updatePdf(Request $request)
    {
       
        $data = [];
        $form_data = $request->all();
       
        $catalog_id = isset($form_data['enc_id'])?base64_decode($form_data['enc_id']):0;

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


            /*unlink old cover image*/

            $old_img = isset($form_data['old_cover_img'])?$form_data['old_cover_img']:'';

            $old_img_path =  $this->catlog_image.$old_img;

            if(isset($old_img))
            { 
                $this->GeneralService->unlink_old_image($old_img_path);
            }
         
        }
        else
        {
            $data['cover_image'] = isset($form_data['old_cover_img'])?$form_data['old_cover_img']:'';
        }

        if(isset($form_data['status']) && $form_data['status']!='')
        {
            $status = '1';
        }
        else
        {
            $status = '0';
        } 


        $data['is_active']  = $status;


        /*upload pdf*/

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
            
            $data['pdf_file']  = isset($catlog_pdf_file_path)?$catlog_pdf_file_path:'';


            /*unlink old cover image*/

            $old_img = isset($form_data['old_pdf'])?$form_data['old_pdf']:'';

            $old_img_path =  $this->catlog_image.$old_img;

            if(isset($old_img))
            { 
                $this->GeneralService->unlink_old_image($old_img_path);
            }
           
        }
        else
        {
           $data['pdf_file'] = isset($form_data['old_pdf'])?$form_data['old_pdf']:'';  
        }




        $data['catalog_name'] = isset($form_data['catalog_name'])?$form_data['catalog_name']:'';  
       
        $result = $this->CatalogPdfModel->where('id',$catalog_id)->update($data);


        if($result)
        {
            $response['status']      = 'success';
            $response['description'] = 'Catalog has been updated.';
            $response['link']         = $this->module_catalog_url;

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


