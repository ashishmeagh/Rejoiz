<?php 

namespace App\Common\Traits;

use Illuminate\Http\Request;
use App\Events\ActivityLogEvent;
use App\Http\Controllers\Controller;

use App\Models\ProductDetailsModel;
use App\Models\ProductImagesModel;
use App\Models\ProductInventoryModel;
use App\Models\ProductsSubCategoriesModel;

use App\Models\ProductsModel;
use App\Models\Products;
use App\Models\CategoryModel;
use App\Models\RepresentativeProductLeadsModel;
use App\Models\RetailerQuotesProductModel;
use App\Common\Services\ElasticSearchService;

use Flash;
use Validator;
use DB;
use Session;
 
trait MultiActionTrait
{
    public function multi_action(Request $request)
    { 
       
        $arr_rules = array();
        $arr_rules['multi_action'] = "required";
        $arr_rules['checked_record'] = "required";

        $validator = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
            Flash::error('Please select '.$this->module_title.' to perform multi actions.');
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $multi_action = $request->input('multi_action');
        $checked_record = $request->input('checked_record');

        /* Check if array is supplied*/
        if(is_array($checked_record) && sizeof($checked_record)<=0)
        {
            Flash::error('Problem occurred,while doing multi action.');
            return redirect()->back();
        }
        
        foreach ($checked_record as $key => $record_id) 
        {  
           
            if($multi_action=="delete")
            {   
               $this->perform_delete(base64_decode($record_id));    
               Flash::success(str_plural($this->module_title).' has been deleted.'); 
            } 
            elseif($multi_action=="activate")
            {   
               $this->perform_activate(base64_decode($record_id)); 
               Flash::success(str_plural($this->module_title).' has been activated.'); 
            }
            elseif($multi_action=="deactivate")
            {  
               $this->perform_deactivate(base64_decode($record_id));
               $message = Session::get('message');    
               if($this->module_title=="Brand")
               {
                Flash::success(str_plural($this->module_title).' has been deactivated.');  
               }
               else
               {
                 Flash::success(str_plural($this->module_title).' has been deactivated.');  
                }
            }
            elseif($multi_action=="block")
            {  
               $this->perform_deactivate(base64_decode($record_id));
               $message = Session::get('message');    
               if($this->module_title=="Brand")
               {
                Flash::success(str_plural($this->module_title).' has been blocked.');  
               }
               else
               {
                 Flash::success(str_plural($this->module_title).' has been blocked.');  
                }
            }
            elseif($multi_action=="mark_as_read")
            {  
               $this->perform_mark_as_read(base64_decode($record_id));    
               Flash::success(str_plural($this->module_title).' has been read.');  
            }
        }

        return redirect()->back();
    }

    public function activate($enc_id = FALSE)
    { 
       
        if(!$enc_id)
        {
            return redirect()->back();
        }

        if($this->perform_activate(base64_decode($enc_id)))
        {
            Flash::success($this->module_title. ' activated successfully.');
        }
        else
        {
            Flash::error('Error occurred while '.$this->module_title.' activation.');
        }

        return redirect()->back();
    }

    public function deactivate($enc_id = FALSE)
    {  
        if(!$enc_id)
        {
            return redirect()->back();
        }

        if($this->perform_deactivate(base64_decode($enc_id)))
        {
            Flash::success($this->module_title. ' deactivated successfully.');
        }
        else
        {
            Flash::error('Error occurred while '.str_plural($this->module_title).' deactivation.');
        }

        return redirect()->back();
    }

    public function delete($enc_id = FALSE)
    {
        if(!$enc_id)
        {
            return redirect()->back();
        }

      /*  if($this->perform_delete(base64_decode($enc_id)) == 'WARNING')
        {
           Flash::error("Product can't deleted because this product is already purchased.");
           return redirect()->back();
        }*/

        if($this->perform_delete(base64_decode($enc_id)))
        {   

            Flash::success(str_singular($this->module_title).' has been deleted.');
            return redirect()->back();
        }  
        else
        {
            Flash::error('Error occurred while '.str_singular( $this->module_title).' deletion.');
            return redirect()->back();
        }

        
    }


    public function perform_activate($id)
    { 
        
        $static_page = $this->BaseModel->where('id',$id)->first();

        if($static_page)
        {
            if($this->BaseModel->getTable()=="products")
            {

               $is_category_active =  $this->CategoryModel->select('is_active')->where('id',$static_page->category_id)->first();

               
               if(isset($is_category_active))
               {
                $is_category_active = $is_category_active->toArray();
               }
               if($is_category_active['is_active']!='1')
               {
                return FALSE;
               }

              $this->ElasticSearchService->activate_product($id,1);
              return $static_page->update(['is_active'=>'1']);

            }
            
            elseif($this->BaseModel->getTable()=="category")
            {  
                try
                {   
                    DB::beginTransaction();
                    $static_page->update(['is_active'=>'1']);
                    $category_id = isset($id)?intval($id):0;
                    $this->ElasticSearchService->activate_category_product($category_id);
                    DB::commit();
                    return $static_page->update(['is_active'=>'1']);

                }
                catch(\Exception $e)
                {
                    DB::rollback();
                    return FALSE;
                }
            }
             elseif($this->BaseModel->getTable()=="subcategory")
            {  

                $multi_action = "activate";
                $this->ElasticSearchService->activate_product($id,1);
                return $this->multi_status_update($id,$multi_action);
            
            }
            

            return $static_page->update(['is_active'=>'1']);

        }

          
            return $this->BaseModel->where('id',$id)->update(['is_active'=>'1']);
        

        return FALSE;
    }

    /* --------------------------------------------------------------------------------------------------
       Action to perform action as mark as read in notification panel of all users
       By Harshada.k
       On date 29 Sep 2020
    ----------------------------------------------------------------------------------------------------*/
    public function perform_mark_as_read($id)
    {
        $static_page = $this->BaseModel->where('id',$id)->first();

        if($static_page)
        {
            if($this->BaseModel->getTable()=="notifications")
            {
              return $static_page->update(['is_read'=>'1']);
            }   
        }
        return FALSE;
    }

    public function perform_deactivate($id)
    {   
        $static_page = $this->BaseModel->where('id',$id)->first(); 
        if($static_page)
        {   
             if($this->BaseModel->getTable()=="products")
            {
               $is_category_active =  $this->CategoryModel->select('is_active')->where('id',$static_page->category_id)->first();
               
               if(isset($is_category_active))
               {
                $is_category_active = $is_category_active->toArray();
               }
               if($is_category_active['is_active']!='1')
               {
                return FALSE;
               }
               return $static_page->update(['is_active'=>'0']);
            }
            elseif($this->BaseModel->getTable()=="category")
            {  
                try
                {   
                    DB::beginTransaction();
                    $static_page->update(['is_active'=>'0']);
                    $category_id = isset($id)?intval($id):0;
                    DB::commit();
                    return $static_page->update(['is_active'=>'0']);

                }
                catch(\Exception $e)
                {
                    DB::rollback();
                    return FALSE;
                }
            }


            elseif($this->BaseModel->getTable()=="subcategory")
            {  

                $multi_action = "deactivate";
                
                return $this->multi_status_update($id,$multi_action);
            
            }
            return $static_page->update(['is_active'=>'0']);
           
            //cmmented because of conflict on 3 jan 2020 
            // return $this->BaseModel->where('id',$id)->update(['is_active'=>'0']);

        }

        return FALSE;
    }

    public function perform_delete($id)
    {   

       
        if($this->BaseModel->getTable()=="products")
        {
          
            /*------------check the product is perchased or not---------*/
            //first check in rep_lead_details & retailer_transaction_details table
        /*    $count_in_lead_rep = $count_in_retailer_transaction = 0;

            $count_in_lead_rep = RepresentativeProductLeadsModel::where('product_id',$id)->count();

            $count_in_retailer_transaction = RetailerQuotesProductModel::where('product_id',$id)->count();  

          
            if($count_in_retailer_transaction >0 || $count_in_lead_rep >0)
            { 
                return 'WARNING';
            }*/
          /*  else
            {*/
              
               ProductDetailsModel::where('product_id',$id)->update(['is_deleted'=>1]);
               ProductImagesModel::where('product_id',$id)->update(['is_deleted'=>1]);
               ProductInventoryModel::where('product_id',$id)->update(['is_deleted'=>1]);
               ProductsSubCategoriesModel::where('product_id',$id)->update(['is_deleted'=>1]);
               $this->ElasticSearchService->delete_product($id);
               ProductsModel::where('id',$id)->update(['is_deleted'=>1]);

         /*   }*/
          /*----------------------------------------------------------*/
          
             return TRUE;
            
        }


        if($this->BaseModel->getTable()!="products")
        {    
          $delete = $this->BaseModel->where('id',$id)->delete();
         

            if($delete)
            {
                /*-------------------------------------------------------
                |   Activity log Event
                --------------------------------------------------------*/
                    /*$arr_event                 = [];
                    $arr_event['ACTION']       = 'REMOVED';
                    $arr_event['MODULE_TITLE'] = $this->module_title;

                    $this->save_activity($arr_event);*/
                /*----------------------------------------------------------------------*/
                return TRUE;
            }
       } 

        return FALSE;

    }
   
}