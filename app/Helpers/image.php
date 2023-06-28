<?php 


	function get_default_image_encoded(){
		return 'data:image/png;base64,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
	';
	}


if(function_exists('image_resize') == false)
{
	function image_resize($path,$width = false,$height = false,$default_image = false,$check_img_shap = false)
	{
		$cache_time = 10;
		$cache_key  = sha1($path.$width.$height);

		/* Check the image in cache */


		$cached_img = \Cache::get($cache_key);

		if($cached_img != false)
		{
			return $cached_img;
		}

		$expiresAt = now()->addMinutes($cache_time);

		try
		{
			$final_img = '';
			if($check_img_shap == false){
				$img = \Image::make($path)->resize($width, $height)->encode("data-url");
				$final_img = $img->encoded;
			}
			else
			{
				$img = \Image::make($path);

				$width  = $img->width();
				$height = $img->height();

				$dimension = 1000;

				$vertical   = (($width < $height) ? true : false);
				$horizontal = (($width > $height) ? true : false);
				$square     = (($width == $height) ? true : false);
				
				if ($vertical) {
					$top = $bottom = 0;
					$newHeight = ($dimension) - ($bottom + $top);
					$img->resize(null, $newHeight, function ($constraint) {
						$constraint->aspectRatio();
					});

				} else if ($horizontal) {
					$right = $left = 0;
					$newWidth = ($dimension) - ($right + $left);
					$img->resize($newWidth, null, function ($constraint) {
						$constraint->aspectRatio();
					});

				} else if ($square) {
					$right = $left = 0;
					$newWidth = ($dimension) - ($left + $right);
					$img->resize($newWidth, null, function ($constraint) {
						$constraint->aspectRatio();
					});

				}

				$img->resizeCanvas($dimension, $dimension, 'center', false, '#ffffff');
				$img->encode("data-url");
				$final_img = $img->encoded;

			}	

			\Cache::put($cache_key, $final_img, $expiresAt);

			return $final_img;
		}
		catch(\Exception $e)
		{
			if($default_image == false)
			{
				return get_default_image_encoded();	
			}
			else
			{
				return $default_image;
			}
			
		}

	}
}

// Show image 
function imagePath($image_name, $image_type, $is_resize)
{
	$imagePath = "";
	$URL = url('/');
	$site_name = isset($site_setting_arr['site_name']) ? $site_setting_arr['site_name'] : false;

	if($image_type == "product")
	{
		$isFileExixst = file_exists(base_path().'/storage/app/'.$image_name);
		
		if($isFileExixst && $is_resize == 0 && $image_name != "")
		{
			$imagePath = url('/storage/app/'.$image_name);
		}
		elseif($isFileExixst && $is_resize == 1 && $image_name != "")
		{
			$imagePath = image_resize(url('/storage/app/'.$image_name));
		}
		else
			$imagePath = $URL.config('app.project.img_path.product_default_images');
	}
	elseif($image_type == "category")
	{
		$isFileExixst = file_exists(base_path().'/storage/app/'.$image_name);
		
		if($isFileExixst && $is_resize == 0 && $image_name != "")
		{
			$imagePath = url('/storage/app/'.$image_name);
		}
		elseif($isFileExixst && $is_resize == 1 && $image_name != "")
		{
			$imagePath = image_resize(url('/storage/app/'.$image_name));
		}
		else
			$imagePath = $URL.config('app.project.img_path.catalog_default_image');
	}
	elseif($image_type == "user")
	{
		$isFileExixst = file_exists(base_path().'/storage/app/'.$image_name);
		
		if($isFileExixst && $is_resize == 0 && $image_name != "")
		{ 
			$imagePath = url('/storage/app/'.$image_name);
		}
		elseif($isFileExixst && $is_resize == 1 && $image_name != "")
		{ 
			$imagePath = image_resize(url('/storage/app/'.$image_name));
		}
		else
			$imagePath = $URL.config('app.project.img_path.default_images').'user-no-img.jpg';
	}
	elseif ($image_type == "site_logo") {
		
		$isFileExixst = file_exists(base_path().'/storage/app/'.$image_name);
		
		if($isFileExixst && $is_resize == 0 && $image_name != "")
		{ 
			$imagePath = url('/storage/app/'.$image_name);
		}
		elseif($isFileExixst && $is_resize == 1 && $image_name != "")
		{ 
			$imagePath = image_resize(url('/storage/app/'.$image_name));
		}
		else
			//$imagePath = $URL.config('app.project.img_path.default_images').'user-no-img.jpg';
			$imagePath = url('http://via.placeholder.com/160x53.png?text='.$site_name);
	
	}
	
	return $imagePath;
}