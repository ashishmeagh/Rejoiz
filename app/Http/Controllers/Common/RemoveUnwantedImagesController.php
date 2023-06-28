<?php

namespace App\Http\Controllers\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BannerImageModel;
use App\Models\BrandsModel;
use App\Models\CategoryModel;
use App\Models\ProductDetailsModel;
use App\Models\UserModel;
use App\Models\SiteSettingModel;
use App\Models\ShopImagesModel;

use Storage;
use Flash;
class RemoveUnwantedImagesController extends Controller
{
    public function __construct(BannerImageModel $BannerImageModel,
    							BrandsModel $BrandsModel,
    							CategoryModel $CategoryModel,
    							ProductDetailsModel $ProductDetailsModel,
    							UserModel $UserModel,
    							SiteSettingModel $SiteSettingModel,
    							ShopImagesModel $ShopImagesModel)
    {
        $this->BannerImageModel    = $BannerImageModel;
        $this->BrandsModel         = $BrandsModel;
        $this->CategoryModel       = $CategoryModel;
        $this->ProductDetailsModel = $ProductDetailsModel;
        $this->UserModel           = $UserModel;
        $this->SiteSettingModel    = $SiteSettingModel;
        $this->ShopImagesModel     = $ShopImagesModel;
    }

    public function remove_banner_images()
    {
        dd('Are you sure?');
        $arrData = [];
		$allImages = Storage::allFiles('banner_image');
        if(isset($allImages) && sizeof($allImages)>0)
        {
        	foreach($allImages as $image)
        	{
        		$isExists = $this->BannerImageModel->where('banner_image',$image)->first();		
        		if($isExists==null)
        		{
        			$delete = Storage::delete($image);
        		}	
        	}

           	return 'Banner images deleted successfully.';
        }   	
        else
        {
        	//delete all images from folder...
        	$deleteAll = Storage::delete($allImages);
        	return 'Banner images deleted successfully.';
        }
    }

    public function remove_brand_images()
    {
        dd('Are You sure?');
        $arrData = [];
		$allImages = Storage::allFiles('brand_image');
        if(isset($allImages) && sizeof($allImages)>0)
        {
        	foreach($allImages as $image)
        	{
        		$isExists = $this->BrandsModel->where('brand_image',$image)->first();		
        		if($isExists==null)
        		{
        			$delete = Storage::delete($image);
        		}	
        	}

           	return 'Brand images deleted successfully.';
        }   	
        else
        {
        	//delete all images from folder...
        	$deleteAll = Storage::delete($allImages);
        	return 'Brand images deleted successfully.';
        }
    }

    public function remove_category_images()
    {
        dd('Are You sure?');
        $arrData = [];
		$allImages = Storage::allFiles('category_image');
        if(isset($allImages) && sizeof($allImages)>0)
        {
        	foreach($allImages as $image)
        	{
        		$isExists = $this->CategoryModel->where('category_image',$image)->first();		
        		if($isExists==null)
        		{
        			$delete = Storage::delete($image);
        		}	
        	}

           	return 'Category images deleted successfully.';
        }   	
        else
        {
        	//delete all images from folder...
        	$deleteAll = Storage::delete($allImages);
        	return 'Category images deleted successfully.';
        }
    }

                                                                                      

    public function remove_product_images()
    {
        dd('Are you sure?');

        $arrData   = $image_arr = [];
		$allImages = Storage::allFiles('product_image');

        if(isset($allImages) && sizeof($allImages)>0)
        {
        	foreach($allImages as $image)
        	{
        		$isExists   = $this->ProductDetailsModel->where('image',$image)->first();		
        		if($isExists==null)
        		{
        			$delete     = Storage::delete($image);
        		}	
        	}

           	return 'Product images deleted successfully.';
        }   	
        else
        {
        	//delete all images from folder...
        	$deleteAll = Storage::delete($allImages);
        	return 'Product images deleted successfully.';
        }
    }                                                                                                               

    public function remove_product_thumb_images()
    {
       // dd('Are you sure?');
        $arrData   = [];
		$allImages = Storage::allFiles('product_image/product_img_thumb');

        if(isset($allImages) && sizeof($allImages)>0)
        {
        	foreach($allImages as $image)
        	{
        		$isExists = $this->ProductDetailsModel->where('image_thumb',$image)->first();		
        		if($isExists==null)
        		{
        			$delete = Storage::delete($image);
        		}	
        	}
           	return 'Product thumb images deleted successfully.';
        }   	   
    }                                                                                                               

    public function remove_profile_images()
    {
        dd('Are you sure?');
        $arrData = [];
		$allImages = Storage::allFiles('profile_image');
		
        if(isset($allImages) && sizeof($allImages)>0)
        {
        	foreach($allImages as $image)
        	{

        		$isExists = $this->UserModel->where('profile_image',$image)->first();		
        		if($isExists==null)
        		{
        			$delete = Storage::delete($image);
        		}	
        	}
           	return 'Profile images deleted successfully.';
        }   	   
    }                                                                                                                 
    public function remove_site_logo()
    {
        dd('Are you sure?'); 
        $arrData = [];
		$allImages = Storage::allFiles('site_logo');
		
        if(isset($allImages) && sizeof($allImages)>0)
        {
        	foreach($allImages as $image)
        	{
        		$isExists   = $this->SiteSettingModel->where('site_logo',$image)->first();		
        		if($isExists==null)
        		{
        			$delete = Storage::delete($image);
        		}	
        	}
           	return 'Site logo deleted successfully.';
        }   	   
    }     

    public function remove_store_images()
    {
        dd('Are you sure?');
        $arrData = [];
		$allImages = Storage::allFiles('store_image');
		
        if(isset($allImages) && sizeof($allImages)>0)
        {
        	foreach($allImages as $image)
        	{
        		$isExistsStoreCover = $this->ShopImagesModel->where('store_cover_image',$image)
        													->orwhere('store_profile_image',$image)	
        													->first();		
        		if($isExistsStoreCover==null)
        		{												
        			$delete = Storage::delete($image);
        		}	
        	}
           	return 'Store images deleted successfully.';
        }   	   
    }


}    
