<?php

namespace App\Http\Controllers\Front;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FaqModel;

use DB;
use Flash;
use Session;
use Sentinel;

use DateTime;

class FaqController extends Controller
{
    /* 
	|  Author : Sagar B. Jadhav
	|  Date   : 11 July 2019
	*/
	public function __construct(FaqModel $FaqModel           
    						   )
    {
    	$this->arr_view_data         = [];
    	$this->module_title          = "FAQs";
    	$this->module_view_folder    = 'front.faq';     	
        $this->FaqModel              = $FaqModel;       
        $this->BaseModel             = $this->FaqModel;
        $this->module_url_path          = url('/');  
        /*$this->MakerModel            = $MakerModel;
        $this->ProductDetailsModel   = $ProductDetailsModel;*/

    }

    public function index()
    {
  
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'My '.$this->module_title;
        return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function faq_list($slug)
    {
        $getList = [];
        if (isset($slug) && $slug == 'vendor') {
            $getList=$this->BaseModel->where([['faq_for',2],['is_active',1]])->get()->toArray();
        }
        if (isset($slug) && $slug == 'retailer') {
            $slug = 'customer';
            $getList=$this->BaseModel->where([['faq_for',1],['is_active',1]])->get()->toArray();
        }

        $this->arr_view_data['slug']            = $slug;  
        $this->arr_view_data['faq_data']        = isset($getList)?$getList:[];    
        $this->arr_view_data['faq_count']        = isset($getList)?count($getList):0;    
        $this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = ucfirst($slug).' '.$this->module_title;
        $this->arr_view_data['module_url_path']      = $this->module_url_path;
        return view($this->module_view_folder.'.view',$this->arr_view_data);
        
    }

    public function question_details($slug, $enc_id)
    {
        if (isset($enc_id)) {
            $id = base64_decode($enc_id);
        }   
        
        $response['question_details'] = $this->BaseModel->where('id',$id)->first();

        return $response;
        
    }

    public function search(Request $request)
    {


        if($request['slug'] == 'vendor'){
            $slug = '2';
        }
        if($request['slug'] == 'retailer'){
            $slug = '1';
        }

        if($request->get('query'))
        {
            $query = $request->get('query');

           /* $data  = $this->BaseModel->where('question', 'LIKE', "%{$query}%")->where('faq_for',$slug)->get();*/


            $data  = $this->BaseModel->where('question', 'LIKE','%'.$query.'%')
                                     ->orwhere('answer','LIKE','%'.$query.'%')
                                     ->where('faq_for',$slug)
                                     ->get()
                                     ->toArray();


            // if(isset($data) && count($data)>0)
            // {
            //     $output = '<ul class="dropdown-menu" style="display:block; position:relative; width: 100%">';
            //     foreach($data as $key=>$row)
            //     {
            //         $output .= '
            //         <li class="tablinks '.$key.'" id="question_'.$row->id.'" onclick="get_question_details(this)" data-enc_id="'.base64_encode($row->id).'" data-slug="'.$request->get('slug').'">'.$row->question.'</a></li>';
            //     }
            //     $output .= '</ul>';
            // }
            // else
            // {
            //    $output  = '<ul class="dropdown-menu" style="display:block; position:relative; width: 100%">';
            //    $output .='No record found';
            //    $output .= '</ul>';

            // }


            return response()->json($data);
        }  
            
    } 

    

}

