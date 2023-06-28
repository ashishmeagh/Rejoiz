<?php

namespace App\Common\Services;

use App\Models\EmailTemplateModel;
use App\Models\SiteSettingModel;
use \Session;
use \Mail;
use \Sentinel;
use \Storage;

class EmailService
{
	public function __construct(EmailTemplateModel $email,SiteSettingModel $SiteSettingModel)
	{
		$this->EmailTemplateModel = $email;
		$this->BaseModel          = $this->EmailTemplateModel;
		$this->SiteSettingModel	  = $SiteSettingModel;
		$this->site_setting_obj        = $this->SiteSettingModel->first();
        
        if(isset($this->site_setting_obj))
        {
            $this->site_setting_arr = $this->site_setting_obj->toArray();
        }
	}


	public function send_mail($arr_mail_data = [],$is_attachment_available=false,$attachment_data=[])
	{
		
		if(isset($arr_mail_data) && sizeof($arr_mail_data)>0)
		{
			
			/*----------------------------------------------------------
			|Built email Html 
			-----------------------------------------------------------*/

			$arr_mail_send_data = $this->built_mail_html($arr_mail_data);
			/*---------------------------------------------------------*/
			
			if($arr_mail_send_data==false){
				return false;
			}

			/*----------------------------------------------------------
			|Send Mail To User
			-----------------------------------------------------------*/

        	
        	$send_mail = $this->send_actual_mail($arr_mail_send_data['arr_user'],$arr_mail_send_data['arr_email_template'],$arr_mail_send_data['content'],$is_attachment_available,$attachment_data);
        	/*---------------------------------------------------------*/
        	
        	
	        if($send_mail==true)
        	{	
	        	return true;
        	}
        	else
        	{	
        		return false;
        	}

				    	  
	    }
	    return false;    
	}

	

	/*--------------------------------------------------------------------
	|built mail common html
	---------------------------------------------------------------------*/
	public function built_mail_html($arr_mail_data=[])
	{
		if(isset($arr_mail_data) && sizeof($arr_mail_data)>0)
		{
			$template_id = isset($arr_mail_data['email_template_id'])&&sizeof($arr_mail_data['email_template_id'])>0?$arr_mail_data['email_template_id']:'0';

			$arr_email_template = [];
			$obj_email_template = $this->EmailTemplateModel->where('id',$template_id)
															->first();
			
			if($obj_email_template)
		  	{
		    	$arr_email_template = $obj_email_template->toArray();
					

		    	$arr_user   = $arr_mail_data['arr_user'];
	        	$content    = $arr_email_template['template_html'];
					

	        	if(isset($arr_mail_data['arr_built_content']) && count($arr_mail_data['arr_built_content'])>0)
	        	{

	        		foreach($arr_mail_data['arr_built_content'] as $key => $data)
	        		{
	        			$content = str_replace("##".$key."##",$data,$content);
	        		}
	        	}

	        	$content = view('email.front_general',compact('content'))->render();
	        	$content = html_entity_decode($content);
						

	        	$admin_role = Sentinel::findRoleBySlug('admin');        
		        $admin_obj  = \DB::table('role_users')->where('role_id',$admin_role->id)->first();				        
		        $admin_id = $admin_obj->user_id;  


		    	$arr_return_data                       = [];
		    	$arr_return_data['arr_user']     	   = $arr_user;	
		    	$arr_return_data['arr_email_template'] = $arr_email_template;		
		    	$arr_return_data['content']            = $content;	
					
		    	return 	$arr_return_data;
		    }    
			return false;	
		}
		return false;	
	}


	public function send_actual_mail($arr_user=[],$arr_email_template=[],$content=false,$is_attachment_available=false,$attachment_data=[])
	{
		
		if(isset($arr_user) &&
		   isset($arr_email_template) &&
		   $content!= false)
		{
			/*Get site setting data from helper*/
			$arr_site_setting = get_site_settings();

			if($is_attachment_available==true && isset($attachment_data['PDF']) && $attachment_data['PDF']!=""){

				$pdf = isset($attachment_data['PDF'])?$attachment_data['PDF']:'';
				$pdfFileName = isset($attachment_data['PDF_FILE_NAME'])?$attachment_data['PDF_FILE_NAME']:'Invoice-'.date('H:i:s').'.pdf';
				
				/*Store attachment file*/
				
				Storage::put('public/pdf/'.$pdfFileName, $pdf->output());
				chmod(storage_path()."/app/public/pdf/".$pdfFileName, 0777);
				

				$send_mail = Mail::send(array(),array(), function($message) use($arr_user,$arr_email_template,$content,$arr_site_setting,$pdf,$pdfFileName)
		        {

		          $template_subject = str_replace("##project_name##",$arr_site_setting['site_name'],$arr_email_template['template_subject']);
		          $template_from_name = str_replace("##project_name##",$arr_site_setting['site_name'],$arr_email_template['template_from']);


		          $arr_email_template['template_subject'] = isset($template_subject)?$template_subject:$arr_email_template['template_subject'];

		          $message->from($arr_email_template['template_from_mail'], $template_from_name);
		          $first_name = isset($arr_user['first_name']) ? $arr_user['first_name'] : "";
		          $message->to($arr_user['email'],$first_name)
				          ->subject($arr_email_template['template_subject'])
				          ->setBody($content, 'text/html');
				  		$message->attachData($pdf->output(), $pdfFileName, [
                        'mime' => 'application/pdf',
                    ]);        
		        });

			}
			else{

				$send_mail = Mail::send(array(),array(), function($message) use($arr_user,$arr_email_template,$content,$arr_site_setting)
		        {

		          $template_subject = str_replace("##project_name##",$arr_site_setting['site_name'],$arr_email_template['template_subject']);
		          $template_from_name = str_replace("##project_name##",$arr_site_setting['site_name'],$arr_email_template['template_from']);


		          $arr_email_template['template_subject'] = isset($template_subject)?$template_subject:$arr_email_template['template_subject'];

		          $message->from($arr_email_template['template_from_mail'], $template_from_name);
		          $first_name = isset($arr_user['first_name']) ? $arr_user['first_name'] : "";
		          $message->to($arr_user['email'], $first_name)
				          ->subject($arr_email_template['template_subject'])
				          ->setBody($content, 'text/html');
		        });

			}		
			return true;
		}
		return false;
	}

	/*function for build pdf email arr*/
	public function build_pdf_email_arr($arr_built_content=[],$pdf_arr=[],$template_id,$arr_user=[])
	{
		
		$arr_mail_data = [];

		if(isset($arr_built_content)&&
			isset($pdf_arr) &&
			isset($arr_user) &&
			isset($template_id) && !empty($template_id))
		{                
	        $arr_mail_data['email_template_id']   = $template_id;
	        $arr_mail_data['arr_built_content']   = $arr_built_content;
	        $arr_mail_data['arr_user']            = $arr_user;
		}

		return $arr_mail_data;
	}
}

?>