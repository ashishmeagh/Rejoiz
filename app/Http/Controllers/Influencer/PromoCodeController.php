<?php

namespace App\Http\Controllers\Influencer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\UserModel;
use App\Models\MakerModel;
use App\Models\InfluencerPromoCodeModel;
use App\Models\PromoCodeInfluencerMappingModel;
use App\Models\CustomerQuotesModel;

use DB;
use Validator;
use Sentinel;
use Flash;
use Datatables;

class PromoCodeController extends Controller
{
    public function __construct(UserModel $UserModel,
                                MakerModel $MakerModel,
    							InfluencerPromoCodeModel $InfluencerPromoCodeModel,
    							PromoCodeInfluencerMappingModel $PromoCodeInfluencerMappingModel,
                                CustomerQuotesModel $CustomerQuotesModel
    						)
    {
    	$this->UserModel       				   = $UserModel; 
    	$this->InfluencerPromoCodeModel 	   = $InfluencerPromoCodeModel;
    	$this->PromoCodeInfluencerMappingModel = $PromoCodeInfluencerMappingModel; 
        $this->MakerModel                      = $MakerModel;
        $this->CustomerQuotesModel             = $CustomerQuotesModel;

    	$this->arr_view_data    = [];

    	$this->module_title           = 'Promo Code';
       	$this->module_view_folder     = 'influencer.promo_code';
       	$this->influencer_panel_slug  = config('app.project.influencer_panel_slug');
        $this->module_url_path        = url($this->influencer_panel_slug.'/promo_code');

    }

    public function index()
    {
    	$this->arr_view_data['module_title']          = $this->module_title;
      	$this->arr_view_data['page_title']            = 'Promo Code';
      	$this->arr_view_data['module_url_path']       = $this->module_url_path;

    	return view($this->module_view_folder.'.index',$this->arr_view_data);
    }

    public function get_promo_code_listing(Request $request)
    {
    	$loggedInUserId = 0;
        $user = Sentinel::check();

        $arr_search_column = $request->input('column_filter');

        if($user)
        {
            $loggedInUserId = $user->id;
        }

        $influencer_promo_code_tbl              = $this->InfluencerPromoCodeModel->getTable();        
        $prefix_influencer_promo_code_tbl       = DB::getTablePrefix().$this->InfluencerPromoCodeModel->getTable();

        $promo_code_influencer_mapping_tbl        = $this->PromoCodeInfluencerMappingModel->getTable();
        $prefix_promo_code_influencer_mapping_tbl = DB::getTablePrefix().$this->PromoCodeInfluencerMappingModel->getTable();

        $user_tbl        = $this->UserModel->getTable();
        $prefix_user_tbl = DB::getTablePrefix().$this->UserModel->getTable();

        $maker_tbl        = $this->MakerModel->getTable();
        $prefix_maker_tbl = DB::getTablePrefix().$this->MakerModel->getTable();

        $customer_quotes_tbl        = $this->CustomerQuotesModel->getTable();
        $prefix_customer_quotes_tbl = DB::getTablePrefix().$this->CustomerQuotesModel->getTable();

        $obj_promo_code = DB::table($influencer_promo_code_tbl)
                            ->select(DB::raw($prefix_influencer_promo_code_tbl.'.id,'.
                                    $prefix_influencer_promo_code_tbl.'.promo_code_name,'.

                                    $prefix_promo_code_influencer_mapping_tbl.'.assigned_date,'.
                                    $prefix_promo_code_influencer_mapping_tbl.'.expiry_date,'.

                                    'COUNT(DISTINCT '.$prefix_customer_quotes_tbl.'.order_no) as promo_code_used_cnt'
                                    ))
                            ->join($promo_code_influencer_mapping_tbl,$promo_code_influencer_mapping_tbl.'.influencer_promo_code_id','=',$influencer_promo_code_tbl.'.id')
                            ->leftjoin($customer_quotes_tbl,function($join) use($customer_quotes_tbl,$promo_code_influencer_mapping_tbl){

                                    $join->on($customer_quotes_tbl.'.influencer_id','=',$promo_code_influencer_mapping_tbl.'.influencer_id')
                                    ->on($customer_quotes_tbl.'.promo_code_id','=',$promo_code_influencer_mapping_tbl.'.influencer_promo_code_id');
                                })
                            ->where($prefix_promo_code_influencer_mapping_tbl.'.influencer_id',$loggedInUserId)
                            ->where($influencer_promo_code_tbl.'.is_active',1)
                            ->groupBy($influencer_promo_code_tbl.'.promo_code_name')
                            ->orderBy($promo_code_influencer_mapping_tbl.'.created_at','DESC');

        if(isset($arr_search_column['q_promo_code']) && $arr_search_column['q_promo_code']!="")
        {
            $search_term    = $arr_search_column['q_promo_code'];
            $obj_promo_code = $obj_promo_code->where($prefix_influencer_promo_code_tbl.'.promo_code_name','LIKE', '%'.$search_term.'%');
        }

       /* if(isset($arr_search_column['q_vendor_name']) && $arr_search_column['q_vendor_name']!="")
        {
            $search_term    = $arr_search_column['q_vendor_name'];
            $obj_promo_code = $obj_promo_code->having('vendor_name','LIKE', '%'.$search_term.'%')
                                            ->Orhaving('company_name','LIKE', '%'.$search_term.'%');
        }*/

        $current_context = $this;

        $json_result     = Datatables::of($obj_promo_code);

        /* Modifying Columns */
        $json_result =  $json_result->editColumn('id',function($data) use ($current_context)
                        {
                            if(isset($data->id) && $data->id != '')
                            {
                               return  $id = base64_encode($data->id);
                            }
                            
                        })
                        ->editColumn('promo_code_name',function($data)use ($current_context) 
                        {   
                           return isset($data->promo_code_name)?$data->promo_code_name:'N/A';
                        })
                        ->editColumn('assigned_date',function($data)use ($current_context) 
                        {   
                           return isset($data->assigned_date)?$data->assigned_date:'N/A';
                        })
                        ->editColumn('expiry_date',function($data)use ($current_context) 
                        {   
                           return isset($data->expiry_date)?$data->expiry_date:'N/A';
                        })

                        /*->editColumn('vendor_name',function($data)use($current_context)
                        {
                            return $vendor_name = isset($data->vendor_name)?$data->vendor_name:'N/A';
                        })*/

                        ->make(true);

        $build_result = $json_result->getData();
        
        return response()->json($build_result);
    }
}
