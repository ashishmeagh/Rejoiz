<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\FaqModel;
use Validator;
use Flash;
use Sentinel;
use App\Common\Traits\MultiActionTrait;
// use App\Common\Services\ElasticSearchService;
 
class FaqController extends Controller
{
     use MultiActionTrait;
    public function __construct (
                                  FaqModel $FaqModel
                    
                                )
    {
        $this->FaqModel             = $FaqModel;
        $this->BaseModel            = $this->FaqModel;
        // $this->ElasticSearchService = $this->ElasticSearchService;
        $this->arr_view_data        = [];
        $this->admin_url_path       = url(config('app.project.admin_panel_slug'));
        $this->module_url_path      = $this->admin_url_path."/faq";        
        $this->module_title         = "FAQs";
        $this->module_view_folder   = "admin.faq";
        $this->module_icon          = "fa-question-circle";
        $this->curr_panel_slug      =  config('app.project.admin_panel_slug');
    }


    public function index()
    {
        $loggedInUserId = 0;
        $user = Sentinel::check();
        if($user)
        {
            $loggedInUserId = $user->id;
        }

        $arr_data = $this->BaseModel->orderBy('id','DESC')
                                    ->get()->toArray();
                                
        $this->arr_view_data['arr_data']        = $arr_data;
        $this->arr_view_data['page_title']      = str_plural($this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function create()
    {
        $this->arr_view_data['page_title']           = 'Create '.str_singular($this->module_title);
        $this->arr_view_data['module_title']         = str_plural($this->module_title);
        $this->arr_view_data['module_url_path']      = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug']      = $this->curr_panel_slug;  

        return view($this->module_view_folder.'.create',$this->arr_view_data);
    }

    public function save(Request $request)
    {
        $is_update = false;

        $arr_rules1 = [];
        $arr_rules2 = [];

        $form_data = $request->all();
        //dd($form_data);

        $json_data = json_encode($form_data);

        $enc_id = base64_decode($request->input('enc_id',false));
        $enc_id = $enc_id == "" ? false : $enc_id;

        if($request->has('enc_id'))
        {
            $is_update = true;
        }

        if($is_update == false)
        {
           $arr_rules2 = [
                            'question' => 'required'
                          ];
           $arr_rules1 = ['type' => 'required'];
        }   
        
        $validation_arr = array_merge($arr_rules1,$arr_rules2); 

        $validator = Validator::make($request->all(),$validation_arr); 

        if($validator->fails())
        {        
           $response['status']      = 'warning';
           $response['description'] = "Form validation failed, please check form fields.";

          return response()->json($response);
        }  

        

        $entity = FaqModel::firstOrNew(['id' => $enc_id]);  

         /* Insert into  Table */
     
        if(isset($form_data['type']) && !empty($form_data['type']))
        {
           $faq_type = $form_data['type'];
        }
        else
        {
           $faq_type = $form_data['old_type'];
        }

        if(isset($form_data['question']) && !empty($form_data['question']))
        {
           $question  = $form_data['question'];
        }
        else
        {
           $question  = $form_data['old_question'];
        }

        if(isset($form_data['answer']) && !empty($form_data['answer']))
        {
           $answer  = $form_data['answer'];
        }
        else
        {
           $answer  = $form_data['old_answer'];
        }

        $entity->faq_for = $faq_type;
        $entity->question  = $question;
        $entity->answer = $answer;

        $result = $entity->save();

        if($is_update == true && $result == true){
            $response['status']     = 'success';
           $response['description'] = str_singular($this->module_title).' has been updated.';
        }

        elseif($is_update == false && $result == true)
        {
           $response['status']      = 'success';
           $response['description'] = str_singular($this->module_title).' has been added.';
        }
        else
        {
          $response['status']      = 'failure';
          $response['description'] = 'Error occurred while saving '.str_singular($this->module_title); 
        }

        $response['link'] = $this->module_url_path;  

        return response()->json($response);                                 
    }

    public function edit($enc_id)
    {
        $id       = base64_decode($enc_id);

        $obj_data = $this->FaqModel->where('id', $id)
                                    ->first();
        $arr_data = [];

        if($obj_data)
        {
            $arr_data = $obj_data->toArray();
        }
    
        $this->arr_view_data['edit_mode']       = TRUE;
        $this->arr_view_data['enc_id']          = $enc_id;
        
        $this->arr_view_data['arr_data']        = $arr_data; 
        $this->arr_view_data['page_title']      = 'Edit '.str_singular($this->module_title);
        $this->arr_view_data['module_title']    = str_plural($this->module_title);
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        $this->arr_view_data['curr_panel_slug'] = $this->curr_panel_slug;

        // dd($this->arr_view_data);  
         
        return view($this->module_view_folder.'.edit',$this->arr_view_data);
    }


    public function delete($enc_id = FALSE)
    {
        $user = Sentinel::check();

        if(!$enc_id)
        {
            return redirect()->back();
        }

        $entity = $this->BaseModel->where('id',base64_decode($enc_id))->first();

        if($entity)
        {
            
           $delete_success = $this->BaseModel->where('id',base64_decode($enc_id))->delete();
            /*-------------------------------------------------------
            |   Activity log Event
            --------------------------------------------------------*/
                $arr_event                 = [];
                $arr_event['ACTION']       = 'REMOVED';
                $arr_event['MODULE_TITLE'] = $this->module_title;
                $arr_event['USER_ID']      = $user->id;

                $this->save_activity($arr_event);
            /*----------------------------------------------------------------------*/
            Flash::success(str_singular($this->module_title).' has been deleted.');
        }

        else
        {
            Flash::error('Error occurred while '.str_singular($this->module_title).' deletion.');
        }

        return redirect()->back();
    }

    public function status_update(Request $request)
    {
        $user = Sentinel::check();

        $faq_id = base64_decode($request->input('faq_id'));
        $status = $request->input('status');
       
        if($status == 'activate')
        {
            $is_active = '1';
        }
        else if($status == 'deactivate')
        {
            $is_active = '0';
        }

        $data['is_active'] = $is_active;

        $update = $this->BaseModel->where('id',$faq_id)->update($data);

        if($update)
        {
            $response['status']  = 'success';
            $response['message'] = "Status has been changed.";

            /*-------------------activity log*------------------------------------*/
            $arr_event['ACTION']       = 'EDIT';
            $arr_event['MODULE_ID']    = $faq_id;
            $arr_event['MODULE_TITLE'] = $this->module_title;
            $arr_event['MODULE_DATA']  = json_encode(['id'=>$faq_id,'status'=>$status]);
            $arr_event['USER_ID']      = $user->id;

            $this->save_activity($arr_event);

            /*----------------------------------------------------------------------*/
        }
        else
        {
            $response['status']  = 'error';
            $response['message'] = 'Error occurred while upating status.';
        }

        return response()->json($response);
    }
    

    public function activate(Request $request)
    {
        $enc_id = $request->input('id');

        if(!$enc_id)
        {
          return redirect()->back();
        }

        if($this->perform_activate(base64_decode($enc_id)))
        {
           $arr_response['status']  = 'SUCCESS';
           $arr_response['message'] = 'FAQ has been activated.';

        }
        else
        {
           $arr_response['status']  = 'ERROR';
           $arr_response['message'] = 'Error occurred while deactivating FAQ.';
        }

        $arr_response['data'] = 'ACTIVE';
        return response()->json($arr_response);
    }

    public function deactivate(Request $request)
    {
        $status = '';
        $enc_id = $request->input('id');

        if(!$enc_id)
        {
            return redirect()->back();
        }

        $status = $this->perform_deactivate(base64_decode($enc_id));
     
        if($status == 'TRUE')
        {
            $arr_response['status']  = 'SUCCESS';
            $arr_response['data']    = 'DEACTIVE';
            $arr_response['message'] = 'FAQ has been deactivated.';
             return response()->json($arr_response);

        }
  /*      elseif($status == 'Warning') 
        {
           $arr_response['status']  = 'WARNING';
           $arr_response['message'] = "Area can't deactivated first delete from sales manager and representative";
           return response()->json($arr_response);
        }*/
        elseif($status == 'FALSE')
        {  
            $arr_response['status']   = 'ERROR';
            $arr_response['message']  = 'Error occurred while deactivating FAQ.';
             return response()->json($arr_response);
        }

        
        
       
    }

    public function perform_activate($id)
    {
        $entity = $this->BaseModel->where('id',$id)->first();
        
        if($entity)
        {   
          $this->BaseModel->where('id',$id)->update(['is_active'=>'1']);

          return TRUE;
        }

        return FALSE;
    }

    public function perform_deactivate($id)
    {  
        /*check that area is already assign or not */
          /*check if area already assign or not*/

      /*  $area_count_in_rep   = $this->RepresentativeModel->where('area_id',$id)->count();

        $area_count_in_sales = $this->SalesManagerModel->where('area_id',$id)->count(); 
        
        if($area_count_in_rep>0 || $area_count_in_sales>0)
        { 
           return 'Warning';
        }
        else
        { */
            $entity = $this->BaseModel->where('id',$id)->first();
        
            if($entity)
            {   
              //deactivate the user
              $this->BaseModel->where('id',$id)->update(['is_active'=>'0']);

              return TRUE;
            }
            else
            {
               return FALSE;
            }
       // }

        /*----------------------------------------*/

        
  }
}
