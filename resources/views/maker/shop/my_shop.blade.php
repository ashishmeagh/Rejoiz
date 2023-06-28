@extends('maker.layout.master')  

@section('main_content')
<style type="text/css">
	.mainshop-box-left{
		float: left;
	}
	.mainshop-box-right{margin-left: 85px;}
	.mainshop-box{
		margin-bottom: 0px;
		color: #555;
		display: block;
	}
	.mainshop-box:hover{
		color: #444;
	}
	.txshop-t {
	    font-size: 13px;
	}
	.titleshop-t {
	    font-size: 16px;
	    font-weight: 600;
	    margin-bottom: 5px;
	}
	.mainshop-box-left i{
		font-size: 70px;
	}
</style>

<!-- Page Content -->
        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="row bg-title">
                    <div class="col-sm-12 top-bg-title">
                    	<h4 class="page-title">Company Settings</h4>
                    	<div class="right">
                        <ol class="breadcrumb">                    	
                            <li><a href="{{url(config('app.project.maker_panel_slug').'/dashboard')}}">Dashboard</a></li>
                            <li class="active">Company Settings</li>
                        </ol>
                    	</div>
                    </div>
                    <!-- /.col-lg-12 -->
                </div>
                <div class="row">
                	<div class="col-md-12">
                	<div class="row">
                	
                		<div class="col-sm-12 col-md-6 col-lg-3 company_setting_box">
                			<div class="white-box">
		                		<a href="{{url(config('app.project.maker_panel_slug').'/company_settings/images')}}" class="mainshop-box-new">
		                			<div class="mainshop-box-left-new">
		                				<i class="fa fa-image"></i>
		                			</div>
		                			<div class="mainshop-box-right-new">
		                				<div class="titleshop-t">Vendor Company Images</div>
		                				<div class="txshop-t">Upload your company profile image and cover photo</div>
		                			</div>
		                			<div class="clearfix"></div>
		                		</a>
		                	</div>
                		</div>
                		<div class="col-sm-12 col-md-6 col-lg-3 company_setting_box">
                			<div class="white-box">
		                		<a href="{{url(config('app.project.maker_panel_slug').'/company_settings/shop_settings')}}" class="mainshop-box-new">
		                			<div class="mainshop-box-left-new">
		                				<i class="fa fa-sliders"></i>
		                			</div>
		                			<div class="mainshop-box-right-new">
		                				<div class="titleshop-t">Sales Terms</div>
		                				<div class="txshop-t">View and update your company details</div>
		                			</div>
		                			<div class="clearfix"></div>
		                		</a>
		                	</div>
                		</div>
                		<div class="col-sm-12 col-md-6 col-lg-3 company_setting_box">
                			<div class="white-box">
		                		<a href="{{url(config('app.project.maker_panel_slug').'/products')}}" class="mainshop-box-new">
		                			<div class="mainshop-box-left-new">
		                				<i class="fa fa-files-o"></i>
		                			</div>
		                			<div class="mainshop-box-right-new">
		                				<div class="titleshop-t">Collection</div>
		                				<div class="txshop-t">Create and manage starter and seasonal collection</div>
		                			</div>
		                			<div class="clearfix"></div>
		                		</a>
		                	</div>
                		</div>
                		<div class="col-sm-12 col-md-6 col-lg-3 company_setting_box">
                			<div class="white-box">
		                		<a href="{{url(config('app.project.maker_panel_slug').'/company_settings/shop_story')}}" class="mainshop-box-new">
		                			<div class="mainshop-box-left-new">
		                				<i class="fa fa-heart-o"></i>
		                			</div>
		                			<div class="mainshop-box-right-new">
		                				<div class="titleshop-t">Company Story</div>
		                				<div class="txshop-t">Tells us your company story, upload images and videos</div>
		                			</div>
		                			<div class="clearfix"></div>
		                		</a>
		                	</div>
                		</div>
                	</div>
                	
                </div>
                </div>
            </div>
            <!-- /#page-wrapper -->
        </div>
   <script type="text/javascript">
       
   </script>
@stop                    