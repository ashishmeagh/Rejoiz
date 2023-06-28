<?php 

if(function_exists('build_listing_url') == false)
{
	function build_listing_url($arr_filter_attrib = [])
	{
		/* Sequence 
			{category?}/{second_category?}/{third_category?}/{gender?}/{size?}/{color?}/{design?}/{fit?}/{sleeve?}/{sortby?}

			Please cross check once with routes 

		*/
		
		$base_url = url('products')."/";

		if(isset($arr_filter_attrib['category']) && sizeof($arr_filter_attrib['category']) > 0)
		{
			$base_url.= implode("--",$arr_filter_attrib['category']);			
		}
		else
		{
			$base_url.="all";				
		}

		$base_url.="/";	
		if(isset($arr_filter_attrib['second_category']) && sizeof($arr_filter_attrib['second_category']) > 0)
		{

			$base_url.= implode("--",$arr_filter_attrib['second_category']);		
		}
		else
		{
			$base_url.="all";				
		}

		$base_url.="/";	
		if(isset($arr_filter_attrib['third_category']) && sizeof($arr_filter_attrib['third_category']))
		{
			$base_url.= implode("--",$arr_filter_attrib['third_category']);		
		}
		else
		{
			$base_url.="all";				
		}

		$base_url.="/";	
		if(isset($arr_filter_attrib['gender']) && sizeof($arr_filter_attrib['gender']) > 0)
		{
			$base_url.= implode("--",$arr_filter_attrib['gender']);	
		}
		else
		{
			$base_url.="all";				
		}

		$base_url.="/";	
		if(isset($arr_filter_attrib['size']) && sizeof($arr_filter_attrib['size']) > 0)
		{
			$base_url.= implode("--",$arr_filter_attrib['size']);			
		}
		else
		{
			$base_url.="all";				
		}

		$base_url.="/";	
		if(isset($arr_filter_attrib['color']) && sizeof($arr_filter_attrib['color']) > 0)
		{
			$base_url.= implode("--",$arr_filter_attrib['color']);		
		}
		else
		{
			$base_url.="all";				
		}

		$base_url.="/";	
		if(isset($arr_filter_attrib['design']) && sizeof($arr_filter_attrib['design']) > 0)
		{
			$base_url.= implode("--",$arr_filter_attrib['design']);		
		}
		else
		{
			$base_url.="all";				
		}

		$base_url.="/";	
		if(isset($arr_filter_attrib['fit']) && sizeof($arr_filter_attrib['fit']) > 0)
		{
			$base_url.= implode("--",$arr_filter_attrib['fit']);		
		}
		else
		{
			$base_url.="all";				
		}

		$base_url.="/";	
		if(isset($arr_filter_attrib['sleeve']) && sizeof($arr_filter_attrib['sleeve']) > 0)
		{
			$base_url.= implode("--",$arr_filter_attrib['sleeve']);		
		}
		else
		{
			$base_url.="all";				
		}

		$base_url.="/";	
		if(isset($arr_filter_attrib['sort_by']) && sizeof($arr_filter_attrib['sort_by']) > 0)
		{
			$base_url.= implode("--",$arr_filter_attrib['sort_by']);		
		}
		else
		{
			$base_url.="all";				
		}

		return $base_url;
	}	
}