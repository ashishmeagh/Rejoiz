<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AnalyticsController extends Controller
{
    /*
    | Author : Sagar B. Jadhav
    | Date   : 27 June 2019
    */
    public function __construct()

    {   
    	$this->arr_view_data      = [];
        
        $this->module_title       = "Analytics";
    	$this->module_view_folder = 'maker.analytics'; 
    	$this->maker_panel_slug   = config('app.project.maker_panel_slug');
    	$this->module_url_path    = url($this->maker_panel_slug.'/analytics');     
    }

    public function index()
    {
    	$this->arr_view_data['module_title']    = $this->module_title;
        $this->arr_view_data['page_title']      = 'Analytics';
        $this->arr_view_data['module_url_path'] = $this->module_url_path;

    	return view($this->module_view_folder.'.index',$this->arr_view_data);
    }
}
