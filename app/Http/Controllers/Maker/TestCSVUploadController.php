<?php

namespace App\Http\Controllers\Maker;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CategoryModel;
use App\Common\Services\GeneralService;
use App\Models\ProductsModel;
use App\Models\ProductDetailsModel;
use App\Models\ProductImagesModel;
use App\Models\CategoryTranslationModel;
use App\Models\ProductsSubCategoriesModel;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\BrandsModel;
use App\Models\RoleUsersModel;
use App\Models\SubCategoryModel;
use App\Models\ProductInventoryModel;
use App\Common\Services\ProductService;

use Validator;
use DB;
use Datatables;
use Sentinel;
use Flash;
use Helper;
use Image;
use Storage;
use Searchable;


class TestCSVUploadController extends Controller
{
    /*
    | Author : Sagar B. Jadhav
    | Date   : 27 June 2019
    */

    public function __construct(CategoryModel $CategoryModel,
                                ProductsModel $ProductsModel,
                                BrandsModel    $BrandsModel,
                                ProductDetailsModel $ProductDetailsModel,
                                ProductImagesModel $ProductImagesModel,
                                CategoryTranslationModel $CategoryTranslationModel,
                                ProductsSubCategoriesModel $ProductsSubCategoriesModel,
                                UserModel $UserModel,
                                RoleModel $RoleModel,
                                RoleUsersModel $RoleUsersModel,
                                SubCategoryModel $SubCategoryModel,
                                ProductInventoryModel $ProductInventoryModel,
                                GeneralService $GeneralService,
                                ProductService $ProductService)
    {

        $this->locale = \App::getLocale();               

        $this->UserModel          = $UserModel;
        $this->RoleModel          = $RoleModel;
        $this->BrandsModel        = $BrandsModel;
        $this->RoleUsersModel     = $RoleUsersModel;
        $this->SubCategoryModel   = $SubCategoryModel;
        $this->CategoryModel      = $CategoryModel;
        $this->BaseModel          = $ProductsModel;
        $this->ProductDetailsModel= $ProductDetailsModel;
        $this->ProductInventoryModel = $ProductInventoryModel;
        $this->ProductImagesModel = $ProductImagesModel;
        $this->CategoryTranslationModel = $CategoryTranslationModel;
        $this->ProductsSubCategoriesModel = $ProductsSubCategoriesModel;
        $this->ProductService     = $ProductService;
        $this->GeneralService = $GeneralService;

        $this->product_img        = base_path().'storage/app/';
        $this->arr_view_data      = [];
        $this->module_title       = "Products";
        $this->module_view_folder = 'maker.product';
        $this->maker_panel_slug   = config('app.project.maker_panel_slug');
        $this->module_url_path    = url($this->maker_panel_slug.'/products');
     
    }

    public function index()
    {
        /*        dd(storage_path('app/product_image/54454'))
        */        //get categories

        $user = Sentinel::check();

        if($user)
        {
            $loggedInUserId = $user->id;
        }
 
       $dest_dir      = base_path().'storage/app/product_images_import'; 
       $source_dir    = base_path('/product_csv/products.csv');
       if (($handle   = fopen($source_dir, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($data);
        $counter++;

        $row++;

        if($counter > 1 && is_array($data) && sizeof($data) > 3){

            $tmppos = strpos($data[3]."",$source_dir);

            if($tmppos === 0){
                $file = $data[3];
                $farry = explode("/",$file);
                $filename = end($farry);
                //echo $filename."<br>";
                $urls[$counter] = $file;

                //$current = file_get_contents($file);
                if(!file_exists($dest_dir.$filename)){
                    copy($file,$dest_dir.$filename);
                }
            }

          }

        }
     fclose($handle);
    }   
        

        $this->arr_view_data['module_title']             = $this->module_title;
        $this->arr_view_data['page_title']               = 'Products List';
        $this->arr_view_data['module_url_path']          = $this->module_url_path;
        return view($this->module_view_folder.'.import_csv',$this->arr_view_data);
    }

}