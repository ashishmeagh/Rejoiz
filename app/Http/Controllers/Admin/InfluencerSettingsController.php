<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\InfluencerSettingModel;

use Validator;
use Flash;
use Input;
use Sentinel;


class InfluencerSettingsController extends Controller
{
	public function __construct(
                                    InfluencerSettingModel $InfluencerSettingModel
                               )
    {
        $this->InfluencerSettingModel   = $InfluencerSettingModel;
        $this->arr_view_data            = [];
        $this->BaseModel          		= $this->InfluencerSettingModel;
             
        $this->module_title       = "Influencer Settings";
        $this->module_view_folder = "admin.influencer_settings";
        $this->module_url_path    = url(config('app.project.admin_panel_slug')."/influencer_settings");
    }


    public function index()
    {
        $arr_data = array();   

        $obj_data =  $this->BaseModel->first();

        if($obj_data != FALSE)
        {
            $arr_data = $obj_data->toArray();    
        }

        $this->arr_view_data['arr_data']        = $arr_data;
        $this->arr_view_data['page_title']      = $this->module_title;
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['module_url_path'] = $this->module_url_path;
        
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function update(Request $request)
    {

        $form_data = $request->all();

        $enc_id    = $form_data['enc_id'] or 0;
        $id        = base64_decode($enc_id);


        /*Check Validations*/
        $arr_rules = [
                        'sales_target'                =>'required|numeric|min:1',
                        'reward_amount'               =>'required|numeric|min:1',
                        'discount_on_promo_code'      =>'required|numeric|min:1|max:100',
                        'promo_code_validity_in_days' =>'required|numeric|min:1'
                     ];

        $validator = Validator::make($request->all(),$arr_rules); 

        if($validator->fails())
        { 
            $response['status']      = 'warning';
            $response['description'] = $validator->errors()->first();

            return response()->json($response);
        }  

        $arr_data['sales_target']           = $form_data['sales_target'] or '';
        $arr_data['reward_amount']          = $form_data['reward_amount'] or '';
        $arr_data['discount_on_promo_code'] = $form_data['discount_on_promo_code'] or '';
        $arr_data['promo_code_validity_in_days'] = $form_data['promo_code_validity_in_days'] or '';


        $entity = InfluencerSettingModel::firstOrNew(['id' => $id]);  

        $entity->sales_target = $form_data['sales_target'] or '';
        $entity->reward_amount = $form_data['reward_amount'] or '';
        $entity->discount_on_promo_code = $form_data['discount_on_promo_code'] or '';
        $entity->promo_code_validity_in_days = $form_data['promo_code_validity_in_days'] or '';
        
        $updated = $entity->save();





        // $updated = $this->InfluencerSettingModel->where('id',$id)->update($arr_data);
 
        if($updated)
        {
            $response['status']      = 'success';
            $response['description'] = $this->module_title.' has been updated.';
        }
        else
        {
            $response['status']      = 'error';
            $response['description'] = 'Problem occurred, while updating '.$this->module_title;
        } 
      
        return response()->json($response); 
    }
    
}
