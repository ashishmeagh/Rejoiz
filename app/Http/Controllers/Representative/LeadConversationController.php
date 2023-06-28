<?php

namespace App\Http\Controllers\Representative;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\LeadConversationModel;
use App\Models\RepresentativeLeadsModel;

use Sentinel,Flash;

class LeadConversationController extends Controller
{
    /*
    | Author : Sagar B. Jadhav
    | Date   : 16 July 2019
    */

    public function __construct(LeadConversationModel $LeadConversationModel,
    							RepresentativeLeadsModel $RepresentativeLeadsModel)
    {       
 		$this->LeadConversationModel   = $LeadConversationModel;
 		$this->RepresentativeLeadsModel= $RepresentativeLeadsModel;
 		$this->module_view_folder      = 'representative.conversation';
 		$this->retailer_panel_slug     = config('app.project.representative_panel_slug');
 		$this->module_url_path         = url($this->retailer_panel_slug.'/leads');
 		$this->images_public_path      = url('/storage/app/');
        $this->images_base_path        = base_path().'/storage/app/';
 		$this->arr_view_data           = [];
 		$this->module_title            = 'Conversation';
    }

    public function index($lead_id = 0)
    {
    	$lead_arr = [];

    	$loggedInUserId = $admin_id = 0;

    	$user = Sentinel::check();
    	
    	if($user)
    	{
    		$loggedInUserId = $user->id;
    	}

    	$lead_id = base64_decode($lead_id);

    	//get quote details
    	$lead_obj = $this->RepresentativeLeadsModel->with(['representative_user_details','maker_details','user_details','retailer_user_details'])->where('id',$lead_id)->first();

    	if($lead_obj)
    	{
    		$lead_arr = $lead_obj->toArray();
    	}
    	else
    	{
    		Flash::error('No lead found, please try again later.');
    		return redirect()->back();
    	}
// dd($lead_arr);
        
    	//this admin id getting logic work only if there is one admin,if multiple admin come then logic will be diffrent        
        $admin_role = Sentinel::findRoleBySlug('admin');        
        $admin_obj = \DB::table('role_users')->where('role_id',$admin_role->id)->first();

        if($admin_obj)
        {
            $admin_id = $admin_obj->user_id;            
        }

        $sender_id   = $loggedInUserId;
        $receiver_id = $lead_arr['maker_id'] or 0;

		$msg_id_arr  = [$admin_id,$sender_id,$receiver_id];
		

        $conversation_arr = $this->LeadConversationModel->with(['sender_details','receiver_details'])
					                                      ->where('lead_id',$lead_id)
					                                      ->whereIn('receiver_id',$msg_id_arr)
					                                      ->whereIn('sender_id',$msg_id_arr)
					                                      ->get()->toArray();
        
        $this->LeadConversationModel->where('lead_id',$lead_id)->update(['is_representative_viewed'=>1]);

        $this->arr_view_data['images_base_path']   = $this->images_base_path;
        $this->arr_view_data['images_public_path'] = $this->images_public_path;
    	$this->arr_view_data['module_url_path']  = $this->module_url_path;
    	$this->arr_view_data['loggedInUserId']   = $loggedInUserId;
    	$this->arr_view_data['lead_arr']        = $lead_arr;
    	$this->arr_view_data['conversation_arr'] = $conversation_arr;
    	$this->arr_view_data['page_title']       = $this->module_title;

    	return view($this->module_view_folder.'.lead_conversation',$this->arr_view_data);
    }

    public function send_message(Request $request)
    {
        $sender_id = $admin_id = $receiver_id = $lead_id = 0;
        
        $file_name= Null;
        
        if(Sentinel::check()==true)
        {
            $sender_id = Sentinel::check()->id;         
        }

        //here sender is retailer and receiver is maker        
        $receiver_id        = $request->input('receiver_id');
        $message            = $request->input('message');    
        $lead_id            = $request->input('lead_id');    


        //attachment upload        
        $attachment = $request->file('attachment');
        
        if($request->hasFile('attachment'))
        {
            $file_extension = strtolower($request->file('attachment')->getClientOriginalExtension());          

            if(in_array($file_extension,['jpg','png','jpeg','JPG','PNG','JPEG']))
            {   
                $file_name = $attachment->store('attachment');                  
            }
            else
            {
                $response['status']  = 'ERROR';
                $response['message'] = 'Please select valid image, only jpg,png and jpeg file are alowed.';
                return response()->json($response);
            }                   
        } 

        $message_data['receiver_id']    = $receiver_id or Null;
        $message_data['sender_id']      = $sender_id;
        $message_data['message']        = $message;
        $message_data['is_viewed']      = '0';        
        $message_data['attachment']     = $file_name or Null;
        $message_data['lead_id']        = $lead_id;
        
        $is_store = $this->LeadConversationModel->create($message_data);    

        if($is_store)
        {
            return response()->json(['status'=>'success']);
        }
        else
        {
            return response()->json(['status'=>'error']);
        }
    }

    public function get_message(Request $request)
    {
        $quote_arr = [];

        $last_retrieved_id = $sender_id = 0;

        $lead_id           = $request->input('lead_id');        
        $retrieved_id      = $request->input('last_retrieved_id');
        $receiver_id       = $request->input('receiver_id');

        if($retrieved_id!=null)
        {
            $last_retrieved_id = $retrieved_id;
        }

        $lead_obj = $this->RepresentativeLeadsModel->with(['maker_details'])
	        									   ->where('id',$lead_id)      
					                               ->first();

        if($lead_obj)
        {
            $lead_arr = $lead_obj->toArray();
        }     

        //this admin id getting logic work only if there is one admin,if multiple admin come then logic will be diffrent        
        $admin_role = Sentinel::findRoleBySlug('admin');        
        $admin_obj = \DB::table('role_users')->where('role_id',$admin_role->id)->first();

        if($admin_obj)
        {
            $admin_id = $admin_obj->user_id;            
        }

        if(Sentinel::check()==true)
        {
            $sender_id = Sentinel::check()->id;         
        }

        $msg_id_arr  = [$admin_id,$sender_id,$receiver_id];

        $chat_arr = $this->LeadConversationModel->with(['sender_details','receiver_details'])
			                                  ->where('id','>',$last_retrieved_id)
			                                  ->whereIn('receiver_id',$msg_id_arr)
			                                  ->whereIn('sender_id',$msg_id_arr)    
			                                  ->where('lead_id',$lead_id)
                                              ->where('sender_id','!=',$sender_id)
			                                  ->get()->toArray();
                                              
        if(count($chat_arr)>0)
        {
            $response['status']                 =   'SUCCESS';
            $response['chat_arr']               =   $chat_arr;          
            $response['images_public_path']     = $this->images_public_path;            
        }
        else
        {
            $response['status']                 =   'FAILURE';
            $response['chat_arr']               =   [];             
            $response['images_public_path']     = $this->images_public_path;
        }

        return response()->json($response);
    }
}
