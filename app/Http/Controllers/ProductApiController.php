<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CategoryModel;
use App\Models\SubCategoryModel;
use App\Models\ProductsModel;
use App\Models\ProductDetailsModel;
use App\Models\ProductImagesModel;
use App\Models\CategoryTranslationModel;
use App\Models\SubCategoryTranslationModel;
use App\Models\ProductsSubCategoriesModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\RoleUsersModel;
use App\Models\MakerModel;
use App\Models\BrandsModel;
use DB;

class ProductApiController extends Controller
{
    /*
    | Author : Bhagyashri Sonawane
    | Date   : 24 Sep 2019
    */

    public function __construct()
    {
        $this->BaseModel          = New ProductsModel;
        $this->module_title       = "Products";
        $this->SubCategoryModel   = New SubCategoryModel;
        $this->CategoryModel      = New CategoryModel;
        $this->SubCategoryModel      = New SubCategoryModel;
        $this->UserModel          = New UserModel;
        $this->RoleModel          = New RoleModel;
        $this->RoleUsersModel     = New RoleUsersModel;
        $this->MakerModel         = New MakerModel;
        $this->BrandsModel        = New BrandsModel;
        $this->ProductDetailsModel= New ProductDetailsModel;
        $this->ProductImagesModel = New ProductImagesModel;
        $this->CategoryTranslationModel = New CategoryTranslationModel;
        $this->SubCategoryTranslationModel = New SubCategoryTranslationModel;
        $this->ProductsSubCategoriesModel = New ProductsSubCategoriesModel;

    }

    public function get_all_product()
    {
    	$product_tbl_name     = $this->BaseModel->getTable();        
        $prefixed_product_tbl = DB::getTablePrefix().$this->BaseModel->getTable();

        $product_sub_category_name     = $this->ProductsSubCategoriesModel->getTable();        
        $prefixed_product_sub_category_tbl = DB::getTablePrefix().$this->ProductsSubCategoriesModel->getTable();

        $category__tbl        = $this->CategoryModel->getTable();
        $prefix_category_tbl  = DB::getTablePrefix().$this->CategoryModel->getTable();

        $maker_tbl            = $this->MakerModel->getTable();
        $prefixed_maker_tbl   = DB::getTablePrefix().$this->MakerModel->getTable(); 

        $brand_tbl            = $this->BrandsModel->getTable();
        $prefixed_brand_tbl   = DB::getTablePrefix().$this->BrandsModel->getTable();

        $category_trans_tbl_name      = $this->CategoryTranslationModel->getTable();        
        $prefixed_category_trans_tbl  = DB::getTablePrefix().$this->CategoryTranslationModel->getTable();

        $sub_category_tbl_name      = $this->SubCategoryModel->getTable();        
        $prefixed_sub_category_tbl  = DB::getTablePrefix().$this->SubCategoryModel->getTable();

        $sub_category_trans_tbl_name      = $this->SubCategoryTranslationModel->getTable();        
        $prefixed_sub_category_trans_tbl  = DB::getTablePrefix().$this->SubCategoryTranslationModel->getTable();

        $user_table        =  $this->UserModel->getTable();
        $prefix_user_table = DB::getTablePrefix().$user_table;
        

        $obj_products = DB::table($product_tbl_name)
                      ->select(DB::raw($prefixed_product_tbl.".id,".  
                        $prefixed_product_tbl.'.is_active,'.
                        $prefixed_product_tbl.'.product_name,'.
                        $prefix_user_table.'.first_name,'.
                        $prefixed_product_tbl.".created_at,".
                        
                        $prefixed_category_trans_tbl.".category_name,".                       
                        $prefixed_product_tbl.".brand,". 

                        
                        $prefixed_brand_tbl.".brand_name,".
                        "CONCAT(".$prefix_user_table.".first_name,' ',"
                                              .$prefix_user_table.".last_name) as user_name"
                          ))
                               
                      ->leftjoin($user_table,$prefix_user_table.'.id','=',$prefixed_product_tbl.'.user_id')

                      ->leftjoin($brand_tbl,$prefixed_brand_tbl.'.id','=',$prefixed_product_tbl.'.brand')

                      

                      ->leftjoin($category_trans_tbl_name,$category_trans_tbl_name.'.category_id','=',$prefixed_product_tbl.'.category_id')

                      
                      ->leftjoin($maker_tbl,$prefixed_maker_tbl.'.user_id','=',$prefixed_product_tbl.'.user_id')

                      ->orderBy('created_at','DESC')->get();
    	
    	return response()->json($obj_products);
    }

    public function store(Request $request)
    {
        $data['user_id']=242;
        $data['category_id']=$request['category_id'];
        $data['product_name']=$request['product_name'];
        $data['is_active']=$request['is_active'];

        $insert=ProductsModel::insert([$data]);
        return response()->json(['success'=>true]);
    

    }
    public function update($prod_id,Request $request)
    {
       
        $product = ProductsModel::find($prod_id);
        $product->user_id=242;
        $product->category_id=$request['category_id'];
        $product->product_name=$request['product_name'];
        $product->is_active=$request['is_active'];

        $product->save();
        if($product=true){
            return response()->json(['success'=>true]);
        }        
        return response()->json(['success'=>false]);
    }

    public function delete($prod_id)
    {
        if (isset($prod_id)) {
            $product = ProductsModel::where('id',$prod_id)->delete();
           $this->ProductDetailsModel->where('product_id',$prod_id)->delete();
           $this->ProductsSubCategoriesModel->where('product_id',$prod_id)->delete();
           

           if($product=true){
            return response()->json(['success'=>true]);
            }        
            return response()->json(['success'=>false]);
        }
        else{
            return response()->json(['success'=>false]);
        }        
    }
}
