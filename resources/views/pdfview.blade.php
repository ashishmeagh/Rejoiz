<!DOCTYPE html>
<html>
<head>
	<title></title>
	{{-- <style type="text/css">
	
		.img_style{
			height: 250px;

		}
	</style> --}}
</head>
<body>	
	{{-- {{dD($items)}} --}}


		@foreach($items as $key=>$img)
			
				<img src="{{url('/')}}/storage/app/{{$img->image}}" alt="Workplace" usemap="#workmap{{$key}}" width="400" height="379">

				<map name="workmap{{$key}}">
			 	 	<area shape="rect" coords="{{$img->coord}}" alt="Computer" href="{{$img->link}}" title="Click here to purchase product">
				</map>
		
			
		@endforeach




</body>
</html>


{{-- <!DOCTYPE html>
<html>
<body>

<h2>Image Maps</h2>
<p>Click on the computer, the phone, or the cup of coffee to go to a new page and read more about the topic:</p>

<img src="{{url('/')}}/storage/app/product_image/workplace.jpg" alt="Workplace" usemap="#workmap" width="400" height="379">

<map name="workmap">
  <area shape="rect" coords="34,44,270,350" alt="Computer" href="http://www.howtocreate.co.uk/tutorials/html/imagemaps">

</map>

</body>
</html> --}}